<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'product_id', 'category_id', 'order_code', 'product_name',
    'destination', 'qty', 'buy_price', 'sell_price', 'status',
    'trx_id', 'sn', 'message', 'checked_at',
])]
class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'Sukses',
            self::STATUS_FAILED => 'Gagal',
            default => 'Pending',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'warning',
        };
    }

    /**
     * Pesan response OkeConnect untuk ditampilkan ke customer: raw response
     * tanpa info saldo deposit (privasi toko), mis. "Saldo: 500.000" /
     * "SALDO ANDA: 10.000.000" dibuang sampai akhir baris.
     */
    public function customerMessage(): ?string
    {
        if ($this->message === null || trim((string) $this->message) === '') {
            return null;
        }

        $message = preg_replace('/\s*\bSaldo\b[^\n]*$/mi', '', (string) $this->message)
            ?? (string) $this->message;
        $message = preg_replace('/\n{2,}/', "\n", $message) ?? $message;

        return trim($message) !== '' ? trim($message) : null;
    }

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'checked_at' => 'datetime',
        ];
    }
}
