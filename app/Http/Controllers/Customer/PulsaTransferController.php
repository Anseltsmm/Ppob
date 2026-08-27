<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Menu Pulsa Transfer (transfer pulsa antar nomor, mis. By.U Direct).
 *
 * Alur sama seperti Pulsa: masukkan nomor tujuan → pilih nominal.
 * Operator transfer (By.U) tidak punya prefix di OPERATOR_PREFIXES
 * (produk By.U aktif muncul lewat keyword \"by u\" / \"byu\").
 */
class PulsaTransferController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'Pulsa Transfer')->first();

        return view('customer.pulsa-transfer.index', compact('category'));
    }

    /**
     * AJAX: daftar produk pulsa transfer (nominal nominal).
     */
    public function products(Request $request)
    {
        $number = preg_replace('/\D/', '', (string) $request->input('number', ''));

        if (strlen($number) < 8) {
            return response()->json(['error' => 'Nomor tidak valid.'], 422);
        }

        $products = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('name', 'Pulsa Transfer'))
            ->where('status', true)
            ->where('modal_price', '>', 0)
            ->orderBy('sell_price')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'denom' => $this->extractDenom($p->name, $p->code),
                'operator' => $p->operator,
                'sell_price' => (int) $p->sell_price,
                'description' => $p->description,
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * Ekstrak nominal dari nama, contoh \"BYU 15.000\" → \"15.000\".
     */
    private function extractDenom(?string $name, ?string $code): string
    {
        if ($name && preg_match('/([\d.]+)/', $name, $m)) {
            $val = (int) str_replace('.', '', $m[1]);

            if ($val > 0) {
                return number_format($val, 0, ',', '.');
            }
        }

        return $code ?: ($name ?? '');
    }
}
