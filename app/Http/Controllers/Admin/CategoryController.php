<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'icon' => $data['icon'] ?? null,
            'image' => $this->handleIconUpload($request),
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 0,
            'status' => true,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request);

        // Hapus gambar lama jika upload yang baru ATAU user memilih hapus
        if ($request->boolean('remove_image')) {
            $this->deleteIconImage($category);
            $category->image = null;
        } elseif ($request->file('image') && $category->image) {
            Storage::disk('public')->delete($category->image);
            $category->image = null;
        }

        $category->update([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 0,
            'status' => $request->boolean('status'),
        ]);

        if ($request->file('image')) {
            $category->image = $this->handleIconUpload($request);
            $category->save();
        }

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $this->deleteIconImage($category);
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'integer'],
            'remove_image' => ['nullable', 'boolean'],
        ]);
    }

    private function handleIconUpload(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('categories', 'public');
    }

    private function deleteIconImage(Category $category): void
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
    }
}
