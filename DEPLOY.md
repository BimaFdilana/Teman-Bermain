# Deploy ke Shared Hosting (cPanel)

Panduan deploy website Rumah Cahaya-bks ke shared hosting. Estimasi waktu: 30–60 menit.

## 1. Syarat hosting

- PHP **8.2+** (disarankan 8.3) + ekstensi: `mbstring`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `sqlite3`/`pdo_mysql`, `zip`
- MySQL / MariaDB (1 database)
- Cron job (untuk scheduler Laravel)
- HTTPS aktif (wajib untuk PWA MiniApp & login Google)

## 2. Upload file

**Opsi A — via Git (bila hosting menyediakan SSH/terminal):**
```bash
cd ~/public_html   # atau folder domain
git clone https://github.com/BimaFdilana/teman-bermain.git .
```

**Opsi B — via File Manager / FTP:** upload semua file project ke `public_html`,
lalu arahkan document root domain ke folder `public/` (di cPanel: *Domains → Document Root*).

> Struktur aman: hanya isi folder `public/` yang boleh diakses web.
> Bila document root tidak bisa diubah, pindahkan isi `public/` ke `public_html`
> dan sesuaikan path di `index.php` (`__DIR__.'/../teman-bermain/vendor/autoload.php'`).

## 3. Install dependency (via terminal hosting)

```bash
composer install --no-dev --optimize-autoloader
```

> Folder `public/build/` sudah berisi hasil `npm run build` dari repo — tidak perlu `npm` di hosting.

## 4. Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Isi yang wajib diubah:

| Key | Isi |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://domain-anda.id` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | sesuai database hosting |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | email & password admin awal |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | dari Google Cloud Console (`https://domain-anda.id/auth/google/callback`) |

## 5. Database & storage

```bash
php artisan migrate --force
php artisan db:seed --force      # data contoh + akun admin
php artisan storage:link
```

Atur permission folder (via File Manager): `storage/` dan `bootstrap/cache/` → **775**.

## 6. Cron job (WAJIB)

Di cPanel → *Cron Jobs*, tambah (tiap menit):

```
* * * * * /usr/bin/php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

Fungsinya: order kadaluarsa otomatis (tiap 5 menit) + backup database harian jam 02:00.

## 7. Optimasi produksi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Setiap ubah `.env` setelah ini, ulangi `php artisan config:cache`.

## 8. Cek akhir

1. Buka `https://domain-anda.id` — landing tampil.
2. Daftar akun baru → No WA dinormalisasi ke `62...`.
3. Login admin di `https://domain-anda.id/admin/login`.
4. Upload QRIS di Pengaturan → coba alur beli sampai redeem.
5. Pasang satu MiniApp di HP Android/iPhone → terbuka tanpa login ulang.

## 9. Backup

- **Otomatis:** `php artisan backup:database` jalan tiap jam 02:00 via cron → `storage/app/backups/` (7 hari terakhir).
- **Manual:** unduh folder `storage/app/backups/` + export database via phpMyAdmin secara berkala.
- Kode sumber sudah di GitHub — push tiap ada perubahan.

## Update versi baru

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
