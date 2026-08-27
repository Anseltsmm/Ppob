<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Katalog produk OkeConnect (daftar harga JSON).
 *
 * Endpoint contoh: https://okeconnect.com/harga/json?id=...&produk=pulsa
 *
 * Menyediakan daftar produk untuk di-preview admin (halaman Import Produk)
 * dan import terpilih ke tabel products (upsert berdasarkan kode).
 */
class OkeConnectCatalogService
{
    private const SETTING_URL = 'okeconnect_harga_url';

    /**
     * Batas nominal default untuk produk open denom (E-Wallet Nominal Bebas).
     */
    private const OPEN_DENOM_MIN = 10_000;

    private const OPEN_DENOM_MAX = 1_000_000;

    /**
     * Map operator dari JSON ke nilai field `operator` di aplikasi
     * (harus sesuai dengan OPERATOR_PREFIXES di PulsaController agar
     * produk muncul saat deteksi operator).
     */
    private const OPERATOR_MAP = [
        'axis' => 'AXIS',
        'xl - axis' => 'XL',
        'by u' => 'Telkomsel',
    ];

    /**
     * Map kategori dari JSON ke nama kategori di aplikasi.
     */
    private const CATEGORY_MAP = [
        'pulsa' => 'Pulsa',
        'pulsa transfer' => 'Pulsa Transfer',
        'sms telepon' => 'Pulsa',
        'data' => 'Paket Data',
        'kuota telkomsel' => 'Paket Data',
        'kuota indosat' => 'Paket Data',
        'kuota xl' => 'Paket Data',
        'kuota tri' => 'Paket Data',
        'kuota axis' => 'Paket Data',
        'kuota byu' => 'Paket Data',
        'kuota nasional' => 'Paket Data',
        'kuota smartfren' => 'Paket Data',
        'token pln' => 'Token PLN',
        'pascabayar' => 'Pascabayar',
        'tagihan' => 'Pascabayar', // PLN pascabayar, Indihome, BPJS, gas, internet
        'air pdam' => 'Pascabayar',
        'tagihan pbb' => 'Pascabayar',
        'finance' => 'Pascabayar', // cicilan, kartu kredit, KPR, leasing
        'ewallet' => 'E-Wallet',
        'e-wallet' => 'E-Wallet',
        'dompet digital' => 'E-Wallet',
        'nominal bebas' => 'E-Wallet',
        'game' => 'Game',
        'digital' => 'Game', // voucher game (Mobile Legends, Free Fire, Google Play, dll)
        'cetak voucher' => 'Cetak Voucher', // voucher untuk dicetak & dijual ulang agen
    ];

    public function getUrl(): ?string
    {
        $url = Setting::get(self::SETTING_URL);

        return is_string($url) && trim($url) !== '' ? trim($url) : null;
    }

    public function setUrl(string $url): void
    {
        Setting::set(self::SETTING_URL, trim($url));
    }

    /**
     * Ambil daftar produk dari endpoint JSON, dinormalisasi.
     *
     * @return array<int, array{
     *     code: string,
     *     name: string,
     *     operator: string,
     *     category: string,
     *     modal_price: float,
     *     active: bool,
     *     open_denom: bool,
     *     inquiry_code: string|null,
     *     skip_import?: bool,
     * }>
     */
    public function fetch(?string $url = null): array
    {
        $url = trim((string) ($url ?: $this->getUrl()));

        if ($url === '') {
            throw new RuntimeException('URL daftar harga OkeConnect belum diatur.');
        }

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Throwable $e) {
            throw new RuntimeException('Tidak bisa mengakses URL harga: '.$e->getMessage());
        }

        if ($response->failed()) {
            throw new RuntimeException('URL harga mengembalikan HTTP '.$response->status().'.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Respons URL harga bukan JSON array.');
        }

        $items = [];

        foreach ($data as $raw) {
            if (! is_array($raw) || empty($raw['kode'])) {
                continue;
            }

            $code = (string) $raw['kode'];
            $category = $this->mapCategory((string) ($raw['kategori'] ?? ''));
            $harga = (float) ($raw['harga'] ?? 0);

            // Produk bayar pascabayar: kode B + harga negatif (biaya admin).
            // Pasangan inquiry-nya (kode C) diturunkan dari kode B.
            $inquiryCode = null;
            if ($category === 'Pascabayar' && str_starts_with($code, 'B') && $harga < 0) {
                $inquiryCode = 'C'.substr($code, 1);
            }

            $items[] = [
                'code' => $code,
                'name' => (string) ($raw['keterangan'] ?? $code),
                'operator' => $this->mapOperator((string) ($raw['produk'] ?? '')),
                'category' => $category,
                'modal_price' => $harga,
                'active' => ((string) ($raw['status'] ?? '1')) === '1',
                'open_denom' => strtolower((string) ($raw['kategori'] ?? '')) === 'nominal bebas',
                'inquiry_code' => $inquiryCode,
            ];

            // Produk inquiry (kode C, harga 0) bukan produk yang dijual — dilewati saat import.
            if ($category === 'Pascabayar' && str_starts_with($code, 'C') && $harga == 0) {
                $items[array_key_last($items)]['skip_import'] = true;
            }

            // Cetak Voucher: produk "Cek Status Voucher" (kode CEK*, harga 0)
            // adalah inquiry status, bukan voucher yang dijual — dilewati.
            if ($category === 'Cetak Voucher' && str_starts_with(strtoupper($code), 'CEK') && $harga == 0) {
                $items[array_key_last($items)]['skip_import'] = true;
            }

            // Produk inquiry H2H di kategori non-pascabayar: nama diawali
            // "Cek ..."/"Bayar ..." (mis. "Cek Harga Tri Cuan Max", "Bayar Paket Axis Cuanku") —
            // itu inquiry dengan harga dummy (0/1/999), bukan produk jual.
            // (Kategori Pascabayar punya produk "Bayar Tagihan" yang VALID dan ditangani
            // lewat inquiry_code — jangan disentuh di sini.)
            $lowerName = strtolower(trim((string) ($raw['keterangan'] ?? '')));
            if ($category !== 'Pascabayar' && (str_starts_with($lowerName, 'cek ') || str_starts_with($lowerName, 'bayar '))) {
                $items[array_key_last($items)]['skip_import'] = true;
            }
        }

        return $items;
    }

    /**
     * Import produk terpilih (upsert berdasarkan kode).
     *
     * @param  array<int, string>  $codes
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(array $codes, string $markupType = 'nominal', float $markupValue = 0): array
    {
        $byCode = collect($this->fetch())->keyBy('code');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($codes as $code) {
            $item = $byCode->get((string) $code);

            if (! $item || ($item['skip_import'] ?? false)) {
                $skipped++;

                continue;
            }

            $category = $this->findOrCreateCategory($item['category']);
            $exists = Product::where('code', $item['code'])->exists();

            // Deskripsi informatif (bukan duplikat nama)
            $description = \App\Support\ProductDescriptions::forItem($item);

            if ($item['inquiry_code'] !== null) {
                // Produk bayar pascabayar: harga dinamis = nominal tagihan + biaya admin.
                $fee = abs($item['modal_price']);
                $data = [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $description,
                    'inquiry_code' => $item['inquiry_code'],
                    'type' => 'prepaid',
                    'modal_price' => $fee,
                    'admin_fee' => $this->sellPrice($fee, $markupType, $markupValue),
                    'sell_price' => 0, // dihitung saat bayar: nominal + admin_fee
                    'operator' => $item['operator'],
                    'status' => $item['active'],
                ];
            } elseif ($item['open_denom']) {
                // Produk Nominal Bebas → open denom: harga = nominal + admin_fee
                $data = [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $description,
                    'type' => 'opendenom',
                    'modal_price' => $item['modal_price'], // biaya admin dari OkeConnect
                    'admin_fee' => $this->sellPrice($item['modal_price'], $markupType, $markupValue),
                    'sell_price' => 0,
                    'min_nominal' => self::OPEN_DENOM_MIN,
                    'max_nominal' => self::OPEN_DENOM_MAX,
                    'operator' => $item['operator'],
                    'status' => $item['active'],
                ];
            } else {
                $data = [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $description,
                    'type' => 'prepaid',
                    'modal_price' => $item['modal_price'],
                    'sell_price' => $this->sellPrice($item['modal_price'], $markupType, $markupValue),
                    'operator' => $item['operator'],
                    'status' => $item['active'],
                ];
            }

            // Jangan timpa deskripsi yang sudah diedit manual (bukan duplikat nama)
            if ($exists) {
                $current = Product::where('code', $item['code'])->value('description');
                if ($current !== null && trim((string) $current) !== '' && trim((string) $current) !== trim((string) $item['name'])) {
                    unset($data['description']);
                }
            }

            Product::updateOrCreate(['code' => $item['code']], $data);

            $exists ? $updated++ : $created++;
        }

        return compact('created', 'updated', 'skipped');
    }

    /**
     * Perbarui harga (modal & jual) untuk semua produk yang SUDAH ADA di database
     * berdasarkan daftar harga terbaru. Produk baru tidak dibuat.
     *
     * @return array{updated: int, skipped: int}
     */
    public function updatePrices(string $markupType = 'nominal', float $markupValue = 0): array
    {
        $updated = 0;
        $skipped = 0;

        foreach ($this->fetch() as $item) {
            $product = Product::where('code', $item['code'])->first();

            if (! $product) {
                $skipped++;

                continue;
            }

            if ($product->inquiry_code !== null) {
                // Pascabayar: perbarui biaya admin saja
                $product->update([
                    'modal_price' => abs($item['modal_price']),
                    'admin_fee' => $this->sellPrice(abs($item['modal_price']), $markupType, $markupValue),
                ]);
            } elseif ($product->type === 'opendenom') {
                $product->update([
                    'modal_price' => $item['modal_price'],
                    'admin_fee' => $this->sellPrice($item['modal_price'], $markupType, $markupValue),
                ]);
            } else {
                $product->update([
                    'modal_price' => $item['modal_price'],
                    'sell_price' => $this->sellPrice($item['modal_price'], $markupType, $markupValue),
                ]);
            }

            $updated++;
        }

        return compact('updated', 'skipped');
    }

    /**
     * Hitung harga jual dari harga modal + markup.
     */
    public function sellPrice(float $modal, string $markupType, float $markupValue): float
    {
        return match ($markupType) {
            'percent' => round($modal * (1 + $markupValue / 100)),
            'nominal' => round($modal + $markupValue),
            default => round($modal),
        };
    }

    private function mapOperator(string $raw): string
    {
        return self::OPERATOR_MAP[strtolower(trim($raw))] ?? trim($raw);
    }

    private function mapCategory(string $raw): string
    {
        $key = strtolower(trim($raw));

        return self::CATEGORY_MAP[$key] ?? ucwords($key);
    }

    private function findOrCreateCategory(string $name): Category
    {
        $category = Category::where('name', $name)->first();

        if ($category) {
            return $category;
        }

        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sort' => (int) Category::max('sort') + 1,
        ]);
    }
}
