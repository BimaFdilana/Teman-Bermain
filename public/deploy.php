<?php
/**
 * Webhook auto-deploy untuk shared hosting (cPanel).
 *
 * Setup:
 * 1. Tambahkan baris ini ke file .env (JANGAN commit .env):
 *      DEPLOY_SECRET=token-rahasia-panjang
 *    Buat token via: php -r "echo bin2hex(random_bytes(16)).PHP_EOL;"
 * 2. Di GitHub: Settings > Webhooks > Add webhook
 *    - Payload URL : https://domain-anda.com/deploy.php
 *    - Content type: application/json
 *    - Secret      : token yang sama
 *    - Event       : Just the push event
 * 3. Setiap push ke branch main → deploy.php menarik kode + migrate otomatis.
 *
 * Keamanan: request tanpa signature GitHub yang valid langsung ditolak (403).
 * File .env tidak pernah disentuh skrip ini.
 */

// ===== KONFIGURASI =====
define('DEPLOY_BRANCH', 'main');
define('PHP_BIN', 'php'); // bila deploy gagal, isi path lengkap, mis. /opt/alt/php83/usr/bin/php
// =======================

$repoRoot = dirname(__DIR__); // folder project (sejajar dengan public/)
$logFile  = $repoRoot . '/storage/logs/deploy.log';

function dlog(string $msg): void {
    global $logFile;
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

// Baca DEPLOY_SECRET dari .env (tidak ikut di-commit)
function envSecret(string $envFile): ?string {
    if (! is_readable($envFile)) return null;
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'DEPLOY_SECRET=')) {
            return trim(trim(substr($line, strlen('DEPLOY_SECRET='))), "\"'");
        }
    }
    return null;
}

// Cari binary composer yang bisa dipakai user web
function findComposer(): string {
    $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';
    $candidates = ['composer', '/usr/local/bin/composer'];
    if ($home !== '') $candidates[] = rtrim($home, '/') . '/bin/composer';
    $candidates[] = '/opt/cpanel/composer/bin/composer';
    foreach (array_unique($candidates) as $c) {
        $out = []; $code = 1;
        @exec(escapeshellarg($c) . ' --version 2>&1', $out, $code);
        if ($code === 0) return $c;
    }
    return 'composer';
}

function run(string $cmd): array {
    $out = [];
    $code = 0;
    @exec($cmd . ' 2>&1', $out, $code);
    return [$code, implode("\n", $out)];
}

$secret = envSecret($repoRoot . '/.env');
if (! $secret) {
    http_response_code(500);
    dlog('GAGAL: DEPLOY_SECRET belum diisi di .env');
    exit('Server misconfigured');
}

// 1. Validasi signature GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');
$expected  = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (! hash_equals($expected, $signature)) {
    http_response_code(403);
    dlog('DITOLAK: signature tidak valid dari ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    exit('Forbidden');
}

// 2. Hanya proses push ke branch yang ditentukan
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . DEPLOY_BRANCH) {
    dlog('SKIP: push ke ' . $ref . ' (bukan ' . DEPLOY_BRANCH . ')');
    exit('Skipped: not ' . DEPLOY_BRANCH);
}

if (! is_dir($repoRoot . '/.git')) {
    http_response_code(500);
    dlog('GAGAL: ' . $repoRoot . ' bukan repository git');
    exit('Not a git repository');
}

dlog('MULAI deploy, commit: ' . substr($data['after'] ?? '?', 0, 7));

// 3. Tarik kode terbaru
[$c1, $o1] = run('cd ' . escapeshellarg($repoRoot) . " && git -c safe.directory='*' pull origin " . DEPLOY_BRANCH);
dlog("git pull (exit $c1): " . substr($o1, 0, 500));
if ($c1 !== 0) { http_response_code(500); exit('git pull failed'); }

// 4. Install dependency PHP
// (user web tidak punya HOME → arahkan COMPOSER_HOME ke folder writable milik app)
$composerHome = $repoRoot . '/storage/app/.composer';
@mkdir($composerHome, 0755, true);
$composer = findComposer();
dlog("composer: $composer");
$composerEnv = 'COMPOSER_HOME=' . escapeshellarg($composerHome) . ' COMPOSER_ALLOW_SUPERUSER=1 ';
[$c2, $o2] = run('cd ' . escapeshellarg($repoRoot) . ' && ' . $composerEnv . escapeshellarg($composer) . ' install --no-dev --optimize-autoloader --no-interaction');
dlog("composer install (exit $c2): " . substr($o2, -500));

// 5. Migrasi database
[$c3, $o3] = run('cd ' . escapeshellarg($repoRoot) . ' && ' . PHP_BIN . ' artisan migrate --force');
dlog("migrate (exit $c3): " . substr($o3, -500));

// 6. Bersihkan & cache ulang
[$c4, $o4] = run('cd ' . escapeshellarg($repoRoot) . ' && ' . PHP_BIN . ' artisan optimize');
dlog("optimize (exit $c4): " . substr($o4, -300));

dlog('SELESAI deploy');
echo 'OK - deployed';
