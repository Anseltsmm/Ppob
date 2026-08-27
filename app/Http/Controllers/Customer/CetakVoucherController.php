<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\VoucherBrands;
use Illuminate\Http\Request;

/**
 * Menu Cetak Voucher (voucher fisik untuk diaktifkan sendiri).
 *
 * Alur: pilih operator → pilih voucher → konfirmasi → SN/kode voucher
 * ditampilkan di halaman order setelah pembayaran sukses.
 */
class CetakVoucherController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'Cetak Voucher')->first();

        // Brand yang memiliki produk voucher aktif, urut dari jumlah terbanyak
        $brands = collect();

        if ($category) {
            $brands = Product::where('category_id', $category->id)
                ->where('status', true)
                ->where('modal_price', '>', 0)
                ->get(['name', 'operator'])
                ->map(fn ($p) => VoucherBrands::brandOf(($p->operator ?? '').' '.$p->name))
                ->filter()
                ->countBy()
                ->sortDesc();
        }

        return view('customer.cetak-voucher.index', compact('category', 'brands'));
    }

    /**
     * AJAX: daftar voucher sesuai brand.
     */
    public function products(Request $request)
    {
        $brand = (string) $request->input('brand', '');

        $query = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('name', 'Cetak Voucher'))
            ->where('status', true)
            ->where('modal_price', '>', 0);

        if ($brand !== '' && $brand !== 'all') {
            $keywords = VoucherBrands::keywords($brand);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', '%'.$keyword.'%')
                        ->orWhere('operator', 'like', '%'.$keyword.'%');
                }
            });
        }

        $products = $query->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'brand' => VoucherBrands::brandOf(($p->operator ?? '').' '.$p->name),
                'region' => VoucherBrands::regionOf($p->operator),
                'operator' => $p->operator,
                'denom' => $this->extractDenom($p->name, $p->code),
                'sell_price' => (int) $p->sell_price,
                'description' => $p->description,
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * Ekstrak nominal dari nama/code, contoh \"Act Voucher 10GB...\" → \"10 GB\".
     */
    private function extractDenom(?string $name, ?string $code): string
    {
        if ($name && preg_match('/(\d+(?:[.,]\d+)?)\s*(gb|mb|tb)/i', $name, $m)) {
            return $m[1].' '.strtoupper($m[2]);
        }

        if ($name && preg_match('/([\d.]+)/', $name, $m)) {
            $val = (int) str_replace('.', '', $m[1]);

            if ($val > 0) {
                return number_format($val, 0, ',', '.');
            }
        }

        return $code ?: ($name ?? '');
    }
}
