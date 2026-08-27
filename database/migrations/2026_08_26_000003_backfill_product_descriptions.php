<?php

use App\Models\Product;
use App\Support\ProductDescriptions;
use Illuminate\Database\Migrations\Migration;

/**
 * Data migration: isi deskripsi informatif untuk semua produk.
 *
 * Deskripsi hanya diubah jika masih kosong atau sama persis dengan nama
 * (artinya belum pernah diedit manual). Editan manual tidak disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Product::with('category')->cursor() as $product) {
            $current = $product->description;

            if ($current !== null && trim((string) $current) !== '' && trim((string) $current) !== trim($product->name)) {
                continue; // sudah ada deskripsi manual / berbeda dari nama
            }

            $item = [
                'name' => $product->name,
                'operator' => $product->operator ?? '',
                'category' => $product->category?->name ?? '',
                'open_denom' => $product->type === 'opendenom',
                'inquiry_code' => $product->inquiry_code,
            ];

            $product->update(['description' => ProductDescriptions::forItem($item)]);
        }
    }

    public function down(): void
    {
        Product::query()->update(['description' => \Illuminate\Support\Facades\DB::raw('name')]);
    }
};
