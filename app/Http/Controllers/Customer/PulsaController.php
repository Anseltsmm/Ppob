<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\DataBrands;
use App\Support\OperatorKeywords;
use Illuminate\Http\Request;

class PulsaController extends Controller
{
    /**
     * Daftar awalan nomor untuk tiap operator di Indonesia.
     */
    public const OPERATOR_PREFIXES = [
        'Telkomsel' => ['0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853'],
        'Indosat'   => ['0814', '0815', '0816', '0855', '0856', '0857', '0858'],
        'XL'        => ['0817', '0818', '0819', '0859', '0877', '0878'],
        'AXIS'      => ['0831', '0832', '0833', '0837', '0838'],
        'Three'     => ['0895', '0896', '0897', '0898', '0899'],
        'Smartfren' => ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'],
    ];

    public function index()
    {
        $pulsa = Category::where('slug', 'like', 'pulsa%')->orWhere('name', 'Pulsa')->first()
            ?? Category::where('name', 'like', '%pulsa%')->first();

        $operators = array_keys(self::OPERATOR_PREFIXES);

        return view('customer.layanan.index', [
            'scope' => 'pulsa',
            'category' => $pulsa,
            'operators' => $operators,
        ]);
    }

    /**
     * AJAX: deteksi operator dari nomor.
     */
    public function detect(Request $request)
    {
        $number = preg_replace('/\D/', '', (string) $request->input('number', ''));

        if (strlen($number) < 8) {
            return response()->json(['operator' => null]);
        }

        // Normalisasi: 62xx / +62xx / 0xx → e.g. "0812"
        $prefix = $number;
        if (str_starts_with($prefix, '62')) {
            $prefix = '0'.substr($prefix, 2);
        }
        // Ambil 4 digit awal
        $prefix4 = substr($prefix, 0, 4);

        foreach (self::OPERATOR_PREFIXES as $operator => $prefixes) {
            if (in_array($prefix4, $prefixes, true)) {
                return response()->json(['operator' => $operator]);
            }
        }

        return response()->json(['operator' => null]);
    }

    /**
     * Ekstrak nominal () dari nama/code produk.
     * Khusus Masa Aktif / SMS / Telepon: tampilkan satuan yang bermakna
     * ("30 Hari", "300 SMS", "60 Mnt") alih-alih angka telanjang.
     */
    private function extractDenom(?string $name, ?string $code): string
    {
        if ($name) {
            $lower = strtolower($name);

            // Masa aktif: "+ 30 Hari" / "+ 4 Bulan" → "30 Hari"
            if (str_contains($lower, 'masa aktif') && preg_match('/\+\s*(\d+)\s*(hari|bulan|tahun)/i', $name, $m)) {
                return $m[1].' '.ucfirst(strtolower($m[2]));
            }

            // SMS: "300 SMS"
            if (str_contains($lower, 'sms') && preg_match('/(\d+(?:\.\d{3})*)\s*sms/i', $name, $m)) {
                return number_format((int) str_replace('.', '', $m[1]), 0, ',', '.').' SMS';
            }

            // Telepon: "60 Mnt" / "185 Menit"
            if ((str_contains($lower, 'mnt') || str_contains($lower, 'menit')) && preg_match('/(\d+(?:\.\d{3})*)\s*(mnt|menit)/i', $name, $m)) {
                $unit = strtolower($m[2]) === 'mnt' ? 'Mnt' : 'Menit';

                return number_format((int) str_replace('.', '', $m[1]), 0, ',', '.').' '.$unit;
            }
        }

        // Cari pola nominal dalam nama, contoh "Telkomsel 25.000" -> 25000
        if ($name && preg_match('/([\d.]+)\s*(GB|MB|GB)?/i', $name, $m)) {
            $val = (int) str_replace('.', '', $m[1]);
            $unit = strtoupper($m[2] ?? '');
            if ($unit === 'GB' || $unit === 'MB') {
                return $m[1].' '.$unit;
            }
            if ($val > 0) {
                return number_format($val, 0, ',', '.');
            }
        }

        return $code ?: ($name ?? '');
    }

    /**
     * Jenis layanan produk untuk tab di halaman Pulsa:
     * Pulsa / SMS & Telepon / Masa Aktif / Paket Data.
     */
    private function productKind(Product $p): string
    {
        $category = $p->category?->name;

        if ($category !== 'Pulsa') {
            return $category ?: 'Pulsa';
        }

        $text = strtolower(($p->operator ?? '').' '.$p->name);

        if (str_contains($text, 'masa aktif')) {
            return 'Masa Aktif';
        }

        if (str_contains($text, 'sms') || str_contains($text, 'telepon') || str_contains($text, 'telp')) {
            return 'SMS & Telepon';
        }

        return 'Pulsa';
    }

    /**
     * Warna branded tiap operator.
     */
    private function operatorColor(?string $operator): string
    {
        return [
            'Telkomsel' => '#ef4444',
            'Indosat' => '#8b5cf6',
            'XL' => '#ec4899',
            'AXIS' => '#22c55e',
            'Three' => '#6366f1',
            'Smartfren' => '#06b6d4',
        ][$operator] ?? '#3b82f6';
    }

    /**
     * AJAX: ambil produk sesuai operator (dan validasi nomor).
     */
    public function products(Request $request)
    {
        $number = preg_replace('/\D/', '', (string) $request->input('number', ''));
        $operator = $request->input('operator');
        $scope = $request->input('scope', 'pulsa');
        $subBrand = (string) $request->input('sub_brand', '');

        if (strlen($number) < 8) {
            return response()->json(['error' => 'Nomor tidak valid.'], 422);
        }

        if ($scope !== 'pln' && ! in_array($operator, array_keys(self::OPERATOR_PREFIXES), true)) {
            return response()->json(['error' => 'Operator tidak dikenali.'], 422);
        }

        // 'voucher' → Paket Data, 'pln' → Token PLN, default 'pulsa' → Pulsa + Paket Data
        $categoryNames = match ($scope) {
            'voucher' => ['Paket Data'],
            'pln' => ['Token PLN'],
            default => ['Pulsa', 'Paket Data'],
        };
        $categories = Category::whereIn('name', $categoryNames)->pluck('id');

        $products = Product::with('category')
            ->whereIn('category_id', $categories)
            ->where('status', true);

        if ($scope === 'pln') {
            // Kategori Token PLN di katalog juga memuat produk inquiry (Cek/Bayar)
            // dan produk H2H yang bukan barang jual — kecualikan berdasarkan nama.
            // Operator (sub-brand) tetap ditampilkan meskipun mengandung 'H2H'.
            $products->where('name', 'not like', 'Cek %')
                ->where('name', 'not like', 'Bayar %')
                ->where('name', 'not like', '%H2H%');
        }

        // Produk dengan modal_price <= 0 adalah pola Cek/Bayar (harga dinamis,
        // mis. "Tsel Telepon Digipos") — tidak layak dijual sebagai harga tetap.
        $products->where('modal_price', '>', 0);

        // Produk inquiry H2H (nama diawali "Cek ..."/"Bayar ...", harga dummy)
        // bukan barang jual — kecualikan di semua menu.
        $products->where('name', 'not like', 'Cek %')
            ->where('name', 'not like', 'Bayar %');

        if ($scope !== 'pln' && $operator) {
            $keywords = OperatorKeywords::keywords($operator);

            if ($keywords === []) {
                // Operator tidak dikenal — pastikan tidak ada produk yang cocok
                $products->whereRaw('0 = 1');
            } else {
                $products->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('operator', 'like', '%'.$keyword.'%')
                            ->orWhere('name', 'like', '%'.$keyword.'%');
                    }
                });
            }
        }

        // Filter sub-brand paket data (scope 'voucher'): "Axis Cuanku", "XL Bebas Puas", dll.
        if ($scope === 'voucher' && $subBrand !== '' && $subBrand !== 'all') {
            $keywords = DataBrands::keywords($operator, $subBrand);

            if ($keywords !== []) {
                $products->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('operator', 'like', '%'.$keyword.'%')
                            ->orWhere('name', 'like', '%'.$keyword.'%');
                    }
                });
            }
        }

        $collection = $products->orderBy('category_id')
            ->orderBy('sell_price')
            ->get();

        // Daftar sub-brand/sub-kategori yang tersedia untuk operator ini (chips di halaman).
        // - scope 'voucher' & 'pulsa': sub-brand paket data (Cuanku, Freedom, dll.)
        // - scope 'pln': sub-kategori produk Token PLN berdasarkan operator/brand
        $subBrands = collect();
        if (in_array($scope, ['voucher', 'pulsa'], true)) {
            $subBrands = $collection
                ->filter(fn ($p) => $this->productKind($p) === 'Paket Data')
                ->map(fn ($p) => DataBrands::brandOf(($p->operator ?? '').' '.$p->name, $operator))
                ->filter()
                ->countBy()
                ->sortDesc();
        } elseif ($scope === 'pln') {
            $subBrands = $collection
                ->map(fn ($p) => $p->operator)
                ->filter()
                ->countBy()
                ->sortDesc();
        }

        // Map icon brand (font/gambar/warna) per operator — dicari sekaligus utk efisiensi
        $brandByOperator = collect();
        foreach ($collection->pluck('operator')->unique()->flatten() as $op) {
            if ($op) {
                $brand = Brand::resolve($op);
                if ($brand) {
                    $brandByOperator[$op] = [
                        'icon' => $brand->icon_font,
                        'image' => $brand->iconUrl(),
                        'color' => $brand->color,
                    ];
                }
            }
        }

        $products = $collection->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category?->name,
            // Jenis layanan utk tab/grouping: Pulsa | SMS & Telepon | Masa Aktif | Paket Data
            'kind' => $this->productKind($p),
            // Sub-brand paket data / sub-kategori Token PLN (berdasarkan operator)
            'sub_brand' => in_array($scope, ['voucher', 'pulsa'], true) && $this->productKind($p) === 'Paket Data'
                ? DataBrands::brandOf(($p->operator ?? '').' '.$p->name, $operator)
                : ($scope === 'pln' ? $p->operator : null),
            // Ambil nominal dari nama, contoh: "Telkomsel 25.000" -> "25.000" (atau dari code)
            'denom' => $this->extractDenom($p->name, $p->code),
            'operator' => $p->operator,
            'operator_color' => $this->operatorColor($p->operator),
            'sell_price' => (int) $p->sell_price,
            'description' => $p->description,
            // Icon brand dari tabel brands, fallback ke generik
            'brand_icon' => $brandByOperator[$p->operator]['icon'] ?? null,
            'brand_image' => $brandByOperator[$p->operator]['image'] ?? null,
            'brand_color' => $brandByOperator[$p->operator]['color'] ?? null,
        ]);

        return response()->json([
            'products' => $products,
            'sub_brands' => $subBrands->keys(),
        ]);
    }
}