<?php
/**
 * Webhook auto-deploy untuk shared hosting (cPanel).
 *
 * Cara pakai:
 * 1. Isi DEPLOY_SECRET di bawah dengan token rahasia (samakan dengan Secret di GitHub webhook).
 * 2. Di GitHub: Settings > Webhooks > Add webhook
 *    - Payload URL : https://domain-anda.com/deploy.php
 *    - Content type: application/json
 *    - Secret      : token yang sama
 *    - Event       : Just the push event
 * 3. Setiap push ke branch main, file ini menarik kode terbaru + migrate otomatis.
 *
 * Keamanan: request tanpa signature GitHub yang valid langsung ditolak (403).
 */

// ===== KONFIGURASI — UBAH BAGIAN INI =====
define('DEPLOY_SECRET', 'GANTI-DENGAN-TOKEN-RAHASIA-PANJANG'); // samakan dengan Secret webhook GitHub
define('DEPLOY_BRANCH', 'main');
define('PHP_BIN', 'php'); // di cPanel bisa perlu '/usr/local/bin/ea-php83'
// =========================================

$repoRoot = dirname(__DIR__); // folder project (sejajar dengan public/)
$logFile  = $repoRoot . '/storage/logs/deploy.log';

function dlog(string $msg): void {
    global $logFile;
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

function run(string $cmd): array {
    $out = [];
    $code = 0;
    // git butuh safe.directory karena dijalankan user web yang beda dengan pemilik file
    exec($cmd . ' 2>&1', $out, $code);
    return [$code, implode("\n", $out)];
}

// 1. Validasi signature GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');
$expected  = 'sha256=' . hash_hmac('sha256', $payload, DEPLOY_SECRET);

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

// 4. Install dependency PHP (tidak menyentuh .env)
[$c2, $o2] = run('cd ' . escapeshellarg($repoRoot) . ' && composer install --no-dev --optimize-autoloader --no-interaction');
dlog("composer install (exit $c2): " . substr($o2, -500));

// 5. Migrasi database
[$c3, $o3] = run('cd ' . escapeshellarg($repoRoot) . ' && ' . PHP_BIN . ' artisan migrate --force');
dlog("migrate (exit $c3): " . substr($o3, -500));

// 6. Bersihkan & cache ulang
[$c4, $o4] = run('cd ' . escapeshellarg($repoRoot) . ' && ' . PHP_BIN . ' artisan optimize');
dlog("optimize (exit $c4): " . substr($o4, -300));

dlog('SELESAI deploy');
echo 'OK - deployed';
