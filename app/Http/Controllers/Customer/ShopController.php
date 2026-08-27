<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('status', true)->orderBy('sort')->get();

        $query = Product::with('category')->where('status', true);

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        $products = $query->orderBy('category_id')->orderBy('name')->paginate(24)->withQueryString();

        return view('customer.shop.index', compact('categories', 'products'));
    }

    public function show(Product $product)
    {
        if (! $product->status) {
            abort(404);
        }

        return view('customer.shop.show', compact('product'));
    }

    public function byCategory(Category $category)
    {
        $categories = Category::where('status', true)->orderBy('sort')->get();
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->where('status', true)
            ->orderBy('name')
            ->paginate(24);

        return view('customer.shop.index', compact('categories', 'products', 'category'));
    }
}
