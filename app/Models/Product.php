<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id', 'name', 'code', 'type', 'description',
    'modal_price', 'sell_price', 'admin_fee', 'min_nominal', 'max_nominal',
    'operator', 'status', 'inquiry_code',
])]
class Product extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Apakah produk ini pembayaran pascabayar (butuh nominal tagihan).
     */
    public function isBill(): bool
    {
        return $this->inquiry_code !== null;
    }

    /**
     * Hitung harga jual: untuk prepaid pakai sell_price,
     * untuk opendenom & pascabayar = nominal (qty) + admin_fee.
     */
    public function priceFor(?float $qty = null): float
    {
        if ($this->type === 'opendenom' || $this->isBill()) {
            return round(($qty ?? 0) + $this->admin_fee);
        }

        return (float) $this->sell_price;
    }

    protected function casts(): array
    {
        return [
            'modal_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'min_nominal' => 'decimal:2',
            'max_nominal' => 'decimal:2',
            'status' => 'boolean',
        ];
    }
}
