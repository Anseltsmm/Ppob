<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\GameBrands;
use Illuminate\Http\Request;

/**
 * Menu Top Up Game (voucher game).
 *
 * Alur: pilih game → masukkan user ID / ID pemain → pilih nominal voucher.
 */
class GameController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'Game')->first();

        // Brand game yang memiliki produk aktif, urut dari jumlah produk terbanyak
        $brands = collect();

        if ($category) {
            $brands = Product::where('category_id', $category->id)
                ->where('status', true)
                ->get(['name', 'operator'])
                ->map(fn ($p) => GameBrands::brandOf(($p->operator ?? '').' '.$p->name))
                ->filter()
                ->countBy()
                ->sortDesc();
        }

        return view('customer.game.index', compact('category', 'brands'));
    }

    /**
     * AJAX: daftar produk game sesuai brand & user ID.
     */
    public function products(Request $request)
    {
        $userId = trim((string) $request->input('user_id', ''));
        $brand = (string) $request->input('brand', '');

        // User ID game: alfanumerik, 4–30 karakter (mis. ID Mobile Legends "81234567")
        if (! preg_match('/^[a-zA-Z0-9-]{4,30}$/', $userId)) {
            return response()->json(['error' => 'User ID tidak valid (min 4 karakter alfanumerik).'], 422);
        }

        $query = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('name', 'Game'))
            ->where('status', true)
            // Kecualikan produk inquiry dummy (Cek/Bayar, harga 0) yang bukan barang jual
            ->where('name', 'not like', 'Cek %')
            ->where('name', 'not like', 'Bayar %');

        if ($brand !== '' && $brand !== 'all') {
            $keywords = GameBrands::keywords($brand);

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
                'brand' => GameBrands::brandOf(($p->operator ?? '').' '.$p->name),
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
     * Ekstrak nominal dari nama/code produk, contoh "Gemscool 1000 G-Cash" → "1.000".
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
