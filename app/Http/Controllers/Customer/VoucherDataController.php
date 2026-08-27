<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;

/**
 * Halaman Voucher Data — sistemnya hampir sama dengan menu Pulsa,
 * tapi khusus menampilkan produk dari kategori "Paket Data"
 * (di dashboard berlabel "Voucher Data").
 */
class VoucherDataController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'Paket Data')->first();

        return view('customer.layanan.index', [
            'scope' => 'voucher',
            'category' => $category,
            'operators' => array_keys(PulsaController::OPERATOR_PREFIXES),
        ]);
    }
}
