<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id', 'invoice', 'reference', 'amount', 'fee_customer', 'total_amount',
    'payment_method', 'payment_name', 'pay_code', 'pay_url', 'checkout_url',
    'status', 'expired_at', 'paid_at',
])]
class Deposit extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tandai deposit lunas dan kredit saldo customer.
     *
     * Baris deposit di-lock (SELECT ... FOR UPDATE) dan status dicek ulang di
     * dalam transaksi agar callback / scheduler / polling yang bersamaan tidak
     * mengkredit saldo dua kali (double-credit).
     *
     * @param  array  $data  Data dari Tripay (reference, payment_method, payment_name, pay_code)
     */
    public function markPaid(array $data = []): void
    {
        DB::transaction(function () use ($data) {
            $deposit = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $deposit || $deposit->status === 'PAID') {
                return;
            }

            $deposit->update([
                'status' => 'PAID',
                'reference' => $data['reference'] ?? $deposit->reference,
                'payment_method' => $data['payment_method'] ?? $deposit->payment_method,
                'payment_name' => $data['payment_name'] ?? $deposit->payment_name,
                'pay_code' => isset($data['pay_code']) ? (string) $data['pay_code'] : $deposit->pay_code,
                'paid_at' => now(),
            ]);

            $user = User::find($deposit->user_id);
            if ($user) {
                $user->credit(
                    (float) $deposit->amount,
                    'Topup saldo invoice '.$deposit->invoice,
                    'deposit',
                    $deposit->id
                );
            }
        });
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'PAID' => 'Lunas',
            'EXPIRED' => 'Kadaluarsa',
            'FAILED' => 'Gagal',
            default => 'Belum Dibayar',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'PAID' => 'success',
            'EXPIRED', 'FAILED' => 'danger',
            default => 'warning',
        };
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_customer' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
