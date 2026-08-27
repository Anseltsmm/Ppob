<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\OkeConnectCatalogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        // onEachSide(1): tampilkan halaman ringkas (prev, [1] … [5] [6] [7] … [20], next)
        // agar tidak meluber/hal terpotong di layar mobile.
        $products = $query->latest()->paginate(20, ['*'], 'page', $request->input('page', 1))
            ->withQueryString()
            ->onEachSide(1);
        $categories = Category::orderBy('sort')->get();

        // Ringkasan untuk header halaman
        $summary = [
            'total' => Product::count(),
            'active' => Product::where('status', true)->count(),
            'categories' => Category::count(),
        ];

        // Map icon brand per operator (untuk tampil di kolom produk)
        $brandMap = collect();
        foreach ($products->getCollection()->pluck('operator')->unique() as $op) {
            if ($op) {
                $brandMap[$op] = Brand::resolve($op);
            }
        }

        return view('admin.products.index', compact('products', 'categories', 'summary', 'brandMap'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('sort')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['status' => ! $product->status]);

        return back()->with('success', 'Status produk diperbarui.');
    }

    /**
     * Halaman import produk dari daftar harga OkeConnect.
     * URL harga disimpan ke settings saat dimuat lewat query param ?url=.
     */
    public function import(Request $request, OkeConnectCatalogService $catalog)
    {
        $url = (string) $request->query('url', '');

        if ($url !== '') {
            $catalog->setUrl($url);
        } else {
            $url = (string) ($catalog->getUrl() ?? '');
        }

        $error = null;
        $paginator = null;
        $stats = [];
        $existingProducts = collect();
        $operatorOptions = collect();
        $categoryOptions = collect();

        if ($url !== '') {
            try {
                $allItems = collect($catalog->fetch($url));
                $operatorOptions = $allItems->pluck('operator')->unique()->sort()->values();
                $categoryOptions = $allItems->pluck('category')->unique()->sort()->values();

                $allCodes = $allItems->pluck('code');
                $existingAll = $allCodes->isNotEmpty()
                    ? Product::whereIn('code', $allCodes)->pluck('code')->flip()
                    : collect();

                $stats = [
                    'total' => $allItems->count(),
                    'active' => $allItems->where('active', true)->count(),
                    'inactive' => $allItems->where('active', false)->count(),
                    'new' => $allItems->count() - $existingAll->count(),
                    'existing' => $existingAll->count(),
                ];

                $items = $allItems;

                if ($request->filled('q')) {
                    $q = strtolower((string) $request->input('q'));
                    $items = $items->filter(fn (array $item) => str_contains(strtolower($item['name']), $q) || str_contains(strtolower($item['code']), $q));
                }

                if ($request->filled('operator')) {
                    $items = $items->where('operator', (string) $request->input('operator'));
                }

                if ($request->filled('category')) {
                    $items = $items->where('category', (string) $request->input('category'));
                }

                if (in_array((string) $request->input('status'), ['1', '0'], true)) {
                    $items = $items->where('active', $request->input('status') === '1');
                }

                if ($items->isEmpty()) {
                    $paginator = null;
                } else {
                    $existingProducts = Product::whereIn('code', $items->pluck('code'))->get(['code', 'modal_price'])->keyBy('code');

                    $paginator = new LengthAwarePaginator(
                        $items->forPage((int) $request->query('page', 1), 50)->values(),
                        $items->count(),
                        50,
                        (int) $request->query('page', 1),
                        ['path' => $request->url(), 'query' => $request->query()]
                    );
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.products.import', compact('url', 'error', 'paginator', 'stats', 'existingProducts', 'operatorOptions', 'categoryOptions'));
    }

    /**
     * Import produk terpilih ke database (upsert berdasarkan kode).
     */
    public function importStore(Request $request, OkeConnectCatalogService $catalog)
    {
        $validated = $request->validate([
            'url' => ['nullable', 'url', 'max:500'],
            'codes' => ['required', 'array', 'min:1'],
            'codes.*' => ['required', 'string', 'max:50'],
            'markup_type' => ['required', 'in:none,nominal,percent'],
            'markup_value' => ['required_if:markup_type,nominal,percent', 'numeric', 'min:0', 'max:1000000'],
        ]);

        if (! empty($validated['url'])) {
            $catalog->setUrl($validated['url']);
        }

        $result = $catalog->import(
            $validated['codes'],
            $validated['markup_type'],
            (float) ($validated['markup_value'] ?? 0)
        );

        return redirect()->route('admin.products.import')
            ->with('success', 'Import selesai: '.$result['created'].' produk baru, '.$result['updated'].' diperbarui, '.$result['skipped'].' dilewati.');
    }

    /**
     * Perbarui harga semua produk yang sudah ada di database
     * berdasarkan daftar harga terbaru dari OkeConnect.
     */
    public function syncPrices(Request $request, OkeConnectCatalogService $catalog)
    {
        $validated = $request->validate([
            'url' => ['nullable', 'url', 'max:500'],
            'markup_type' => ['required', 'in:none,nominal,percent'],
            'markup_value' => ['required_if:markup_type,nominal,percent', 'numeric', 'min:0', 'max:1000000'],
        ]);

        if (! empty($validated['url'])) {
            $catalog->setUrl($validated['url']);
        }

        $result = $catalog->updatePrices(
            $validated['markup_type'],
            (float) ($validated['markup_value'] ?? 0)
        );

        return redirect()->route('admin.products.import')
            ->with('success', 'Harga diperbarui: '.$result['updated'].' produk disinkronkan dengan daftar harga terbaru.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:products,code'.($product ? ','.$product->id : '')],
            'type' => ['required', 'in:prepaid,opendenom'],
            'description' => ['nullable', 'string'],
            'modal_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'min_nominal' => ['nullable', 'numeric', 'min:0'],
            'max_nominal' => ['nullable', 'numeric', 'min:0'],
            'operator' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        return [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'modal_price' => $validated['modal_price'],
            'sell_price' => $validated['sell_price'] ?? 0,
            'admin_fee' => $validated['admin_fee'] ?? 0,
            'min_nominal' => $validated['min_nominal'] ?? null,
            'max_nominal' => $validated['max_nominal'] ?? null,
            'operator' => $validated['operator'] ?? null,
            'status' => $request->boolean('status'),
        ];
    }
}
