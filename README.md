# Aplikasi PPOB (Payment Point Online Bank)

Aplikasi fullstack PPOB berbasis **Laravel** yang terintegrasi dengan:

## Domain

| Domain | Fungsi |
|--------|--------|
| `https://app.azkia.cloud` | Aplikasi utama (customer) |
| `https://admin.azkia.cloud` | Panel admin (otomatis redirect ke `/admin`) |

- **OkeConnect (H2H)** — eksekusi produk digital (pulsa, paket data, token PLN, pascabayar, e-wallet, game)
- **TriPay** — payment gateway untuk topup saldo customer

## Fitur

### Customer
- Registrasi & login
- Topup saldo via TriPay (Virtual Account, QRIS, e-wallet, Indomaret/Alfamart)
- Beli produk: pulsa, paket data, token PLN, pascabayar, e-wallet (open denom), game
- Riwayat order + mutasi saldo
- Cek status pembayaran topup manual

### Admin
- Dashboard statistik (customer, order, profit, topup)
- Kelola produk (CRUD, tipe prepaid/open denom, harga modal & jual)
- Kelola kategori produk
- Kelola order & cek status ke OkeConnect
- Kelola topup/deposit customer
- Kelola customer (lihat saldo, adjust saldo, aktif/nonaktif)
- Pengaturan kredensial API (OkeConnect & TriPay) + test koneksi + cek saldo

### Otomatisasi
- Order diproses via queue (worker) ke OkeConnect
- Callback OkeConnect (GET) mengupdate status order
- Callback TriPay (POST) memvalidasi signature & menambah saldo
- Scheduler: cek ulang order pending (tiap menit) & deposit unpaid (tiap 5 menit)

## Instalasi

```bash
# 1. Konfigurasi .env (database, APP_URL)
cp .env.example .env
# edit: DB_CONNECTION=mysql, DB_DATABASE=ppob, DB_USERNAME=ppob, DB_PASSWORD=...

# 2. Install dependency & key
composer install
php artisan key:generate

# 3. Migrasi & seeder
php artisan migrate --seed

# 4. Jalankan queue worker & scheduler
php artisan queue:work --sleep=3 --tries=3
# scheduler via cron:
# * * * * * cd /path/project && php artisan schedule:run >> /dev/null 2>&1
```

## Akun Demo (dari seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@ppob.test` | `password` |
| Customer | `customer@ppob.test` | `password` (saldo Rp 100.000) |

## Konfigurasi API

Setelah login sebagai admin, buka **Pengaturan** (`/admin/settings`):

### OkeConnect
- **Base URL**: `https://h2h.okeconnect.com`
- **Member ID**: kode user (contoh `OK00123`)
- **PIN**: PIN transaksi dari pendaftaran
- **Password**: password transaksi via API
- **Callback URL** (isi di dashboard OkeConnect): `https://app.azkia.cloud/webhook/okeconnect?token=TOKEN` — token autentikasi ditampilkan di halaman **Pengaturan** (dan bisa di-regenerate di sana). Callback tanpa token yang valid akan ditolak (401).

### TriPay
- **Mode**: `sandbox` (testing) / `production` (live)
- **API Key**, **Private Key**, **Merchant Code**: dari dashboard member TriPay
  - Sandbox: menu API & Integrasi > Simulator > Merchant > Detail
  - Production: menu Merchant > Opsi > Edit
- **Callback URL** (isi di dashboard TriPay): `https://app.azkia.cloud/webhook/tripay`

## Alur Transaksi

### Pembelian Produk
1. Customer memilih produk & memasukkan nomor tujuan
2. Saldo dipotong, order dibuat (status `pending`)
3. Queue worker memanggil OkeConnect `/trx`
4. Response `SUKSES` → order `success` + SN tersimpan
5. Response `GAGAL` → order `failed` + saldo di-refund otomatis
6. Response pending → dicek ulang oleh scheduler

### Topup Saldo
1. Customer pilih nominal & metode pembayaran
2. Aplikasi membuat transaksi closed payment ke TriPay
3. Customer membayar (VA/QRIS/e-wallet/retail)
4. Callback TriPay → validasi signature → saldo ditambah

## Struktur Penting

```
app/
├── Services/
│   ├── OkeConnectService.php   # Integrasi API H2H OkeConnect
│   └── TripayService.php       # Integrasi payment gateway TriPay
├── Jobs/
│   ├── ProcessOrder.php        # Eksekusi order ke OkeConnect
│   ├── CheckPendingOrders.php  # Cek ulang order pending
│   └── CheckPendingDeposits.php# Cek ulang deposit unpaid
└── Http/Controllers/
    ├── Customer/               # Shop, Order, Topup, Dashboard
    ├── Admin/                  # Dashboard, Produk, Kategori, Order, Deposit, Customer, Setting
    └── Webhook/                # Callback Tripay & OkeConnect
```

## Deployment

### Prasyarat (VPS & Shared Hosting sama)
- **PHP 8.3+** dengan ekstensi: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `openssl`, `fileinfo`
- **Composer 2**
- **MySQL / MariaDB** (disarankan) atau SQLite
- **Node.js + npm** (hanya untuk build asset)

### Deploy ke VPS (Ubuntu/Nginx)

```bash
# 1. Clone repository
cd /var/www
git clone https://github.com/username/ppob.git
cd ppob

# 2. Install dependency & env
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# 3. Build asset
npm install && npm run build

# 4. Konfigurasi .env (edit sesuai server)
#    APP_URL=https://domain-anda.com
#    DB_CONNECTION=mysql, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Migrasi & seeder (seeder opsional, hanya data demo)
php artisan migrate --seed
php artisan storage:link

# 6. Optimasi (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Izin direktori
sudo chown -R www-data:www-data storage bootstrap/cache

# 8. Queue worker (wajib untuk proses order) — via supervisor:
#    [program:ppob-worker]
#    command=php /var/www/ppob/artisan queue:work --sleep=3 --tries=3 --timeout=120
#    autostart=true
#    autorestart=true

# 9. Scheduler via cron:
#    * * * * * cd /var/www/ppob && php artisan schedule:run >> /dev/null 2>&1

# 10. Nginx: arahkan root ke /var/www/ppob/public
#     server {
#         root /var/www/ppob/public;
#         index index.php;
#         location / { try_files $uri $uri/ /index.php?$query_string; }
#         location ~ \.php$ { include snippets/fastcgi-php.conf; fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; }
#     }
```

### Deploy ke Shared Hosting (cPanel)

Karena shared hosting mengarahkan domain ke folder `public_html`:

```bash
# 1. Build asset di lokal, lalu upload
composer install --no-dev --optimize-autoloader   # (atau jalankan di hosting lewat terminal)
npm install && npm run build

# 2. Upload seluruh folder project (kecuali .env, vendor, node_modules)
#    ke folder utama akun hosting, misal: /home/user/ppob

# 3. Isi folder public_html dengan symlink ke /public
#    Di cPanel: buat symlink lewat terminal
ln -s /home/user/ppob/public /home/user/public_html
#    ATAU jika tidak bisa symlink: upload isi folder public/ langsung ke public_html/
#    lalu sesuaikan path di public/index.php:
#        require __DIR__.'/../vendor/autoload.php'  →  require __DIR__.'/../ppob/vendor/autoload.php'
#        $app = require __DIR__.'/../bootstrap/app.php' → __DIR__.'/../ppob/bootstrap/app.php'

# 4. Konfigurasi .env
cp .env.example .env
php artisan key:generate   # via terminal hosting / SSH

# 5. Migrasi
php artisan migrate --seed
php artisan storage:link

# 6. Queue worker di shared hosting:
#    Gunakan cron job di cPanel (setiap menit):
#    php /home/user/ppob/artisan queue:work --stop-when-empty --tries=3
#    Dan scheduler:
#    php /home/user/ppob/artisan schedule:run

# 7. Pastikan PHP versi 8.3 (pilih di cPanel > Select PHP Version) & aktifkan ekstensi.
```

> **Catatan shared hosting:** beberapa hosting membatasi proses background (queue).
> Jika order tidak terproses, gunakan cron per menit untuk menjalankan worker (`--stop-when-empty`)
> agar tidak ada proses yang berjalan terus-menerus.

## Catatan

- Kode produk di seeder adalah **contoh**. Ganti dengan kode produk sesuai daftar produk OkeConnect Anda.
- Mode sandbox TriPay dapat diuji dengan fitur "simulasi pembayaran" di dashboard TriPay.
- Jangan pernah commit file `.env` (berisi kredensial).
