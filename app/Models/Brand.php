<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon_font', 'icon_image', 'color', 'status'])]
class Brand extends Model
{
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'operator', 'name');
    }
    public function iconClasses(): string
    {
        return $this->icon_font ? 'bi bi-'.$this->icon_font : 'bi bi-tag';
    }

    public function iconUrl(): ?string
    {
        if ($this->icon_image) {
            // Icon_image disimpan relatif ke storage publik
            return asset('storage/'.ltrim($this->icon_image, '/'));
        }

        return null;
    }

    public function hasImage(): bool
    {
        return (bool) $this->icon_image;
    }

    /**
     * Cari brand berdasarkan nama operator produk (case-insensitive).
     * Dipakai untuk menampilkan icon brand yang cocok dgn operator string.
     */
    public static function resolve(?string $operator): ?self
    {
        if (! $operator || trim($operator) === '') {
            return null;
        }

        return static::where('name', $operator)->first()
            ?? static::whereRaw('LOWER(name) = ?', [strtolower($operator)])->first()
            ?? null;
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}