<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')
            ->orderByRaw('COALESCE(color, name)')
            ->orderBy('name')
            ->get();

        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Brand::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'icon_font' => $data['icon_font'] ?? null,
            'icon_image' => $this->handleIconUpload($request),
            'color' => $data['color'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        return back()->with('success', 'Brand berhasil ditambahkan.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $this->validateData($request);

        // Hapus gambar lama jika user meng-upload yang baru
        if ($request->file('icon_image') && $brand->icon_image) {
            Storage::disk('public')->delete($brand->icon_image);
            $brand->icon_image = null;
        }

        $brand->update([
            'name' => $data['name'],
            'icon_font' => $data['icon_font'] ?? null,
            'color' => $data['color'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        if ($request->file('icon_image')) {
            $brand->icon_image = $this->handleIconUpload($request);
            $brand->save();
        }

        return back()->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->icon_image) {
            Storage::disk('public')->delete($brand->icon_image);
        }

        $brand->delete();

        return back()->with('success', 'Brand berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon_font' => ['nullable', 'string', 'max:50'],
            'icon_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['nullable', 'boolean'],
        ]);
    }

    private function handleIconUpload(Request $request): ?string
    {
        if (! $request->hasFile('icon_image')) {
            return null;
        }

        return $request->file('icon_image')->store('brands', 'public');
    }
}