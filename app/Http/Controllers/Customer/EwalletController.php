<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\EwalletBrands;
use Illuminate\Http\Request;

/**
 * Menu Top Up E-Wallet.
 *
 * Alur: pilih brand (DANA/OVO/GoPay/dll) → masukkan nomor tujuan → pilih
 * nominal (produk fixed denom atau input nominal bebas untuk open denom).
 */
class EwalletController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'E-Wallet')->first();

        // Brand yang memiliki produk aktif, urut dari jumlah produk terbanyak
        $brands = collect();

        if ($category) {
            $brands = Product::where('category_id', $category->id)
                ->where('status', true)
                ->get(['name', 'operator'])
                ->map(fn ($p) => EwalletBrands::brandOf(($p->operator ?? '').' '.$p->name))
                ->filter()
                ->countBy()
                ->sortDesc();
        }

        return view('customer.ewallet.index', compact('category', 'brands'));
    }

    /**
     * AJAX: daftar produk E-Wallet sesuai brand & nomor tujuan.
     */
    public function products(Request $request)
    {
        $number = preg_replace('/\D/', '', (string) $request->input('number', ''));
        $brand = (string) $request->input('brand', '');

        if (strlen($number) < 8) {
            return response()->json(['error' => 'Nomor tujuan tidak valid.'], 422);
        }

        $query = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('name', 'E-Wallet'))
            ->where('status', true)
            // Kecualikan produk inquiry dummy (Cek/Bayar, harga 0) yang bukan barang jual
            ->where('name', 'not like', 'Cek %')
            ->where('name', 'not like', 'Bayar %');

        if ($brand !== '' && $brand !== 'all') {
            $keywords = EwalletBrands::keywords($brand);

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
                'brand' => EwalletBrands::brandOf(($p->operator ?? '').' '.$p->name),
                'type' => $p->type,
                'denom' => $this->extractDenom($p->name, $p->code),
                'sell_price' => (int) $p->sell_price,
                'admin_fee' => (int) $p->admin_fee,
                'min_nominal' => $p->min_nominal !== null ? (int) $p->min_nominal : null,
                'max_nominal' => $p->max_nominal !== null ? (int) $p->max_nominal : null,
                'description' => $p->description,
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * Ekstrak nominal dari nama/code produk, contoh "DANA 25.000" → "25.000".
     */
    private function extractDenom(?string $name, ?string $code): string
    {
        if ($name && preg_match('/([\d.]+)\s*(GB|MB)?/i', $name, $m)) {
            $val = (int) str_replace('.', '', $m[1]);

            if ($val > 0) {
                return number_format($val, 0, ',', '.');
            }
        }

        return $code ?: ($name ?? '');
    }
}
