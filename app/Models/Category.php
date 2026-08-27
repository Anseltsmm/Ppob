<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon', 'image', 'description', 'sort', 'status'])]
class Category extends Model
{
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('status', true);
    }

    /**
     * Apakah kategori memakai gambar logo (mengalahkan ikon font bila ada).
     */
    public function hasImage(): bool
    {
        return (bool) $this->image;
    }

    /**
     * URL gambar logo kategori (relatif ke storage publik).
     */
    public function imageUrl(): ?string
    {
        if ($this->image) {
            return asset('storage/'.ltrim($this->image, '/'));
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
