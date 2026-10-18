# Deploy Otomatis: Muse → GitHub → Hosting

Alur yang disarankan: setiap ada perbaikan dari Muse, saya **commit + push ke GitHub**,
lalu GitHub otomatis memanggil `deploy.php` di hosting — hosting menarik kode terbaru,
install dependency, dan migrate **tanpa disentuh manual**.

## Syarat hosting

- PHP `shell_exec()` diizinkan (cek di cPanel → *Select PHP Version* → tidak dicentang `disable_functions`)
- Ada `git` dan `composer` (sebagian besar shared hosting Indonesia menyediakannya)
- Jika salah satunya tidak ada → pakai **Cara B** di bawah

## Setup sekali saja (Cara A — webhook, disarankan)

### 1. Di hosting (cPanel)

1. **Git Version Control** → *Create* → Clone URL: `https://github.com/BimaFdilana/teman-bermain.git`,
   Repository Path: mis. `rumahcahaya`, Branch: `main`. Catat **Clone URL** & path-nya.
2. Buka **Terminal** cPanel (atau SSH), masuk ke folder repo:
   ```sh
   cd ~/rumahcahaya
   cp .env.example .env   # lalu isi DB_*, APP_URL, MAIL_*, GOOGLE_* via File Manager
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```
3. Arahkan **document root** domain ke folder `public/` (cPanel → *Domains* → *Document Root*).
   Jika tidak bisa diubah, pindahkan isi `public/` ke `public_html` (lihat DEPLOY.md).
4. Edit `public/deploy.php`: ganti `DEPLOY_SECRET` dengan token rahasia panjang
   (buat mis. 32 karakter acak), dan `PHP_BIN` bila perlu (mis. `/usr/local/bin/ea-php83`).
5. Tambahkan cron scheduler Laravel (cPanel → *Cron Jobs*):
   ```
   * * * * * /usr/local/bin/ea-php83 /home/username/rumahcahaya/artisan schedule:run >> /dev/null 2>&1
   ```
   (sesuaikan path PHP & username)

### 2. Di GitHub

Repo → **Settings → Webhooks → Add webhook**:

- Payload URL: `https://domain-anda.com/deploy.php`
- Content type: `application/json`
- Secret: token yang sama dengan `DEPLOY_SECRET`
- Event: *Just the push event* → Add webhook

### 3. Uji

Ubah sesuatu kecil → push → buka `https://domain-anda.com` — perubahan tampil.
Log tiap deploy tercatat di `storage/logs/deploy.log`.

> Keamanan: `deploy.php` menolak request tanpa signature GitHub yang valid (403).
> File `.env` tidak pernah disentuh skrip deploy.

## Cara B — kalau hosting tidak mengizinkan shell_exec/git

1. Tetap clone via **cPanel Git Version Control** (tanpa webhook).
2. Buat **cron tiap 5 menit** yang menjalankan `git pull`:
   ```
   */5 * * * * cd /home/username/rumahcahaya && git pull origin main >> /home/username/pull.log 2>&1
   ```
3. Setelah pull, jalankan manual via Terminal cPanel (atau tambahkan ke skrip):
   `composer install --no-dev && php artisan migrate --force && php artisan optimize`.
4. Kekurangan: ada jeda maks 5 menit, dan migrate/composer tetap semi-manual.

## Cara C — GitHub Actions + FTP (opsi terakhir)

Cocok bila hosting hanya menyediakan FTP. Workflow meng-upload file yang berubah
tiap push. Kelemahan untuk Laravel: folder `vendor/` ribuan file → upload lambat;
perlu strategi (upload `vendor.zip` sekali, extract via cron). Hanya dipakai bila
Cara A & B tidak memungkinkan — minta saya buatkan workflow-nya bila perlu.
