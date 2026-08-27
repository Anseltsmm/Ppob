<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== USER ====================
        $admin = User::firstOrCreate(
            ['email' => 'admin@ppob.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'status' => true,
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@ppob.test'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '081298765432',
                'saldo' => 100000,
                'status' => true,
            ]
        );

        $this->command?->info("Admin: admin@ppob.test / password");
        $this->command?->info("Customer: customer@ppob.test / password (saldo Rp 100.000)");

        // ==================== SETTING DEFAULT ====================
        Setting::firstOrCreate(['key' => 'okeconnect_base_url'], ['value' => 'https://h2h.okeconnect.com']);
        Setting::firstOrCreate(['key' => 'tripay_mode'], ['value' => 'sandbox']);

        // ==================== KATEGORI ====================
        $categories = [
            ['name' => 'Pulsa', 'icon' => 'phone', 'description' => 'Pulsa semua operator', 'sort' => 1],
            ['name' => 'Paket Data', 'icon' => 'wifi', 'description' => 'Paket internet semua operator', 'sort' => 2],
            ['name' => 'Token PLN', 'icon' => 'lightning-charge', 'description' => 'Token listrik PLN prabayar', 'sort' => 3],
            ['name' => 'Pascabayar', 'icon' => 'receipt', 'description' => 'Tagihan bulanan: PLN, BPJS, Telkom, PDAM', 'sort' => 4],
            ['name' => 'E-Wallet', 'icon' => 'wallet2', 'description' => 'Topup dompet digital', 'sort' => 5],
            ['name' => 'Game', 'icon' => 'joystick', 'description' => 'Voucher game online', 'sort' => 6],
        ];

        foreach ($categories as $cat) {
            $category = Category::where('name', $cat['name'])->first();

            if ($category) {
                // Sudah ada — perbarui ikon/deskripsi bila berubah
                $category->update(collect($cat)->except('name')->all());
                continue;
            }

            // Belum ada — buat baru dgn slug acak unik
            Category::create($cat + ['slug' => Str::slug($cat['name']).'-'.Str::random(4)]);
        }

        // ==================== PRODUK CONTOH ====================
        // Catatan: kode produk di bawah adalah contoh.
        // Ganti dengan kode produk sesuai daftar produk OkeConnect Anda.
        $pulsa = Category::where('name', 'Pulsa')->first();
        $data = Category::where('name', 'Paket Data')->first();
        $pln = Category::where('name', 'Token PLN')->first();
        $ewallet = Category::where('name', 'E-Wallet')->first();
        $game = Category::where('name', 'Game')->first();

        $sampleProducts = [
            // [nama, kode, kategori, modal, jual, operator]
            ['Telkomsel 5.000', 'T5', $pulsa, 5900, 6500, 'Telkomsel'],
            ['Telkomsel 10.000', 'T10', $pulsa, 10300, 11000, 'Telkomsel'],
            ['Telkomsel 25.000', 'T25', $pulsa, 24400, 25000, 'Telkomsel'],
            ['Indosat 5.000', 'S5', $pulsa, 5950, 6500, 'Indosat'],
            ['Indosat 10.000', 'S10', $pulsa, 10200, 11000, 'Indosat'],
            ['XL 5.000', 'X5', $pulsa, 6000, 6500, 'XL'],
            ['XL 10.000', 'X10', $pulsa, 10300, 11000, 'XL'],
            ['AXIS 5.000', 'AX5', $pulsa, 5950, 6500, 'AXIS'],
            ['AXIS 10.000', 'AX10', $pulsa, 10300, 11000, 'AXIS'],
            ['Three 5.000', 'TRI5', $pulsa, 6200, 6800, 'Three'],
            ['Three 10.000', 'TRI10', $pulsa, 10500, 11200, 'Three'],
            ['Smartfren 5.000', 'SF5', $pulsa, 6250, 6800, 'Smartfren'],
            ['Telkomsel 1 GB', 'D1', $data, 15100, 16000, 'Telkomsel'],
            ['Telkomsel 2 GB', 'D2', $data, 25100, 26500, 'Telkomsel'],
            ['Indosat 1 GB', 'SD1', $data, 14600, 15500, 'Indosat'],
            ['XL 1 GB', 'XD1', $data, 14800, 15700, 'XL'],
            ['AXIS 1 GB', 'AXD1', $data, 15200, 16200, 'AXIS'],
            ['Three 1 GB', 'TRID1', $data, 15700, 16500, 'Three'],
            ['Smartfren 1 GB', 'SFD1', $data, 15300, 16200, 'Smartfren'],
            // === Token PLN — Prabayar ===
            ['Token PLN 5.000', 'PLN5', $pln, 6655, 7500, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 5.000.'],
            ['Token PLN 10.000', 'PLN10', $pln, 11655, 12500, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 10.000.'],
            ['Token PLN 20.000', 'PLN20', $pln, 20400, 21000, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 20.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.'],
            ['Token PLN 50.000', 'PLN50', $pln, 50400, 51500, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 50.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.'],
            ['Token PLN 100.000', 'PLN100', $pln, 100400, 102000, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 100.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.'],
            ['Token PLN 200.000', 'PLN200', $pln, 200400, 203000, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 200.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.'],
            ['Token PLN 500.000', 'PLN500', $pln, 500500, 512000, 'Token PLN Prabayar', 'Token listrik prabayar PLN senilai Rp 500.000.'],
            // === Token PLN — Terbaik ===
            ['Token PLN Terbaik 20.000', 'PLNB20', $pln, 21765, 22500, 'H2H Terbaik', 'Token PLN 20.000 via H2H Terbaik — proses cepat dan stabil.'],
            ['Token PLN Terbaik 50.000', 'PLNB50', $pln, 51765, 53000, 'H2H Terbaik', 'Token PLN 50.000 via H2H Terbaik — proses cepat dan stabil.'],
            ['Token PLN Terbaik 100.000', 'PLNB100', $pln, 101765, 103500, 'H2H Terbaik', 'Token PLN 100.000 via H2H Terbaik — proses cepat dan stabil.'],
            ['Token PLN Terbaik 200.000', 'PLNB200', $pln, 201765, 204000, 'H2H Terbaik', 'Token PLN 200.000 via H2H Terbaik — proses cepat dan stabil.'],
            ['Token PLN Terbaik 500.000', 'PLNB500', $pln, 501765, 510000, 'H2H Terbaik', 'Token PLN 500.000 via H2H Terbaik — proses cepat dan stabil.'],
            // === Token PLN — Promo ===
            ['Promo Token PLN 20 Ribu', 'PLNP20', $pln, 21760, 22500, 'H2H Promo', 'Promo Token PLN 20.000 — harga spesial.'],
            ['Promo Token PLN 50 Ribu', 'PLNP50', $pln, 51760, 53000, 'H2H Promo', 'Promo Token PLN 50.000 — harga spesial.'],
            ['Promo Token PLN 100 Ribu', 'PLNP100', $pln, 101760, 103500, 'H2H Promo', 'Promo Token PLN 100.000 — harga spesial.'],
            ['Promo Token PLN 200 Ribu', 'PLNP200', $pln, 201760, 204000, 'H2H Promo', 'Promo Token PLN 200.000 — harga spesial.'],
            ['Promo Token PLN 500 Ribu', 'PLNP500', $pln, 501760, 510000, 'H2H Promo', 'Promo Token PLN 500.000 — harga spesial.'],
            // === Token PLN — Full Reply ===
            ['Token PLN Full Reply 20.000', 'PLNF20', $pln, 21765, 22500, 'H2H Full Reply', 'Token PLN Full Reply 20.000 — detail lengkap.'],
            ['Token PLN Full Reply 50.000', 'PLNF50', $pln, 51765, 53000, 'H2H Full Reply', 'Token PLN Full Reply 50.000 — detail lengkap.'],
            ['Token PLN Full Reply 100.000', 'PLNF100', $pln, 101765, 103500, 'H2H Full Reply', 'Token PLN Full Reply 100.000 — detail lengkap.'],
            ['Token PLN Full Reply 200.000', 'PLNF200', $pln, 201765, 204000, 'H2H Full Reply', 'Token PLN Full Reply 200.000 — detail lengkap.'],
            ['Token PLN Full Reply 500.000', 'PLNF500', $pln, 501765, 510000, 'H2H Full Reply', 'Token PLN Full Reply 500.000 — detail lengkap.'],
            // === Token PLN — Racikan ===
            ['Token PLN Racikan 20.000', 'PLNZ20', $pln, 21748, 22500, 'H2H Racikan', 'Token PLN Racikan 20.000 — harga hemat.'],
            ['Token PLN Racikan 50.000', 'PLNZ50', $pln, 51748, 53000, 'H2H Racikan', 'Token PLN Racikan 50.000 — harga hemat.'],
            ['Token PLN Racikan 100.000', 'PLNZ100', $pln, 101748, 103500, 'H2H Racikan', 'Token PLN Racikan 100.000 — harga hemat.'],
            ['Token PLN Racikan 200.000', 'PLNZ200', $pln, 201748, 204000, 'H2H Racikan', 'Token PLN Racikan 200.000 — harga hemat.'],
            ['Token PLN Racikan 500.000', 'PLNZ500', $pln, 501748, 510000, 'H2H Racikan', 'Token PLN Racikan 500.000 — harga hemat.'],
        ];

        foreach ($sampleProducts as $item) {
            [$name, $code, $category, $modal, $sell, $operator] = $item;
            $description = $item[6] ?? null;
            Product::firstOrCreate(
                ['code' => $code],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'type' => 'prepaid',
                    'modal_price' => $modal,
                    'sell_price' => $sell,
                    'operator' => $operator,
                    'description' => $description ?? null,
                    'status' => true,
                ]
            );
        }

        // Contoh produk open denom (e-wallet)
        Product::firstOrCreate(
            ['code' => 'BBSDN'],
            [
                'category_id' => $ewallet->id,
                'name' => 'Topup GoPay / OVO / DANA (Nominal Bebas)',
                'type' => 'opendenom',
                'modal_price' => 0,
                'admin_fee' => 2000,
                'min_nominal' => 10000,
                'max_nominal' => 1000000,
                'operator' => 'E-Wallet',
                'status' => true,
            ]
        );

        // ==================== PRODUK GAME CONTOH ====================
        $gameProducts = [
            // [nama, kode, operator, harga modal menjadi admin_fee, harga jual]
            ['Mobile Legends 86 Diamonds', 'ML86', 'Mobile Legends', 8200, 9000],
            ['Mobile Legends 172 Diamonds', 'ML172', 'Mobile Legends', 15800, 16800],
            ['Mobile Legends 344 Diamonds', 'ML344', 'Mobile Legends', 31500, 33000],
            ['Free Fire 140 Diamonds', 'FF140', 'Free Fire', 15400, 16500],
            ['Free Fire 355 Diamonds', 'FF355', 'Free Fire', 37500, 39500],
            ['PUBG Mobile 60 UC', 'PUBG60', 'PUBG Mobile', 12000, 13000],
            ['PUBG Mobile 325 UC', 'PUBG325', 'PUBG Mobile', 62000, 64500],
            ['COD Mobile 80 CP', 'COD80', 'COD Mobile', 16000, 17200],
            ['Higgs Domino 100M Chip', 'HG100', 'Higgs Domino', 21000, 22500],
        ];

        foreach ($gameProducts as [$gName, $gCode, $gOperator, $gModal, $gSell]) {
            Product::firstOrCreate(
                ['code' => $gCode],
                [
                    'category_id' => $game->id,
                    'name' => $gName,
                    'type' => 'prepaid',
                    'modal_price' => $gModal,
                    'sell_price' => $gSell,
                    'operator' => $gOperator,
                    'status' => true,
                ]
            );
        }

        // ==================== BRAND ====================
        $brands = [
            ['name' => 'Telkomsel', 'icon_font' => 'sim', 'color' => '#ef4444'],
            ['name' => 'Indosat', 'icon_font' => 'sim', 'color' => '#8b5cf6'],
            ['name' => 'XL', 'icon_font' => 'sim', 'color' => '#ec4899'],
            ['name' => 'AXIS', 'icon_font' => 'sim', 'color' => '#22c55e'],
            ['name' => 'Three', 'icon_font' => 'sim', 'color' => '#6366f1'],
            ['name' => 'Smartfren', 'icon_font' => 'sim', 'color' => '#06b6d4'],
            ['name' => 'E-Wallet', 'icon_font' => 'wallet2', 'color' => '#3b82f6'],
            // ===== E-Wallet =====
            ['name' => 'DANA', 'icon_font' => 'wallet2', 'color' => '#108ee9'],
            ['name' => 'GoPay', 'icon_font' => 'wallet2', 'color' => '#00aed6'],
            ['name' => 'OVO', 'icon_font' => 'wallet2', 'color' => '#4c2f93'],
            ['name' => 'ShopeePay', 'icon_font' => 'wallet2', 'color' => '#ee4d2d'],
            ['name' => 'LinkAja', 'icon_font' => 'wallet2', 'color' => '#ed1c24'],
            // ===== Game =====
            ['name' => 'PUBG Mobile', 'icon_font' => 'controller', 'color' => '#f8ab28'],
            ['name' => 'Mobile Legends', 'icon_font' => 'controller', 'color' => '#3246a8'],
            ['name' => 'Free Fire', 'icon_font' => 'fire', 'color' => '#ff5d2a'],
            ['name' => 'COD Mobile', 'icon_font' => 'crosshair', 'color' => '#1b1f23'],
            ['name' => 'Google Play', 'icon_font' => 'play-circle', 'color' => '#3b7efe'],
            ['name' => 'Steam Wallet', 'icon_font' => 'controller', 'color' => '#1b2838'],
            ['name' => 'Higgs Domino', 'icon_font' => 'controller', 'color' => '#e0202c'],
            ['name' => 'Point Blank', 'icon_font' => 'crosshair', 'color' => '#2b6cb0'],
            // ===== Lainnya =====
            ['name' => 'Token PLN Prabayar', 'icon_font' => 'lightning-charge', 'color' => '#f59e0b'],
            ['name' => 'H2H Terbaik', 'icon_font' => 'lightning-charge', 'color' => '#16a34a'],
            ['name' => 'H2H Promo', 'icon_font' => 'gift', 'color' => '#d97706'],
            ['name' => 'H2H Full Reply', 'icon_font' => 'chat-square-text', 'color' => '#0891b2'],
            ['name' => 'H2H Racikan', 'icon_font' => 'arrow-left-right', 'color' => '#7c3aed'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['name' => $brand['name']],
                ['slug' => Str::slug($brand['name']), 'status' => true]
                    + collect($brand)->except('name')->all()
            );
        }
        $this->command?->info('Seeder selesai: '.count($brands).' brand contoh dibuat.');
    }
}
