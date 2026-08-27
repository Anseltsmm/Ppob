<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index()
    {
        // Hanya kategori yang punya produk aktif — hindari tile mengarah ke halaman kosong
        $categories = Category::withCount('activeProducts')
            ->where('status', true)
            ->has('activeProducts')
            ->orderBy('sort')
            ->get();

        return view('customer.dashboard', compact('categories'));
    }
}
