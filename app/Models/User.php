<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'saldo', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function balanceHistories(): HasMany
    {
        return $this->hasMany(BalanceHistory::class);
    }

    /**
     * Kurangi saldo user (dengan cek saldo cukup).
     *
     * Baris user di-lock (SELECT ... FOR UPDATE) di dalam transaksi agar dua
     * request bersamaan tidak bisa sama-sama lolos cek saldo (double-spend).
     *
     * @return bool false jika saldo tidak cukup
     */
    public function debit(float $amount, string $description, ?string $refType = null, ?int $refId = null): bool
    {
        return DB::transaction(function () use ($amount, $description, $refType, $refId) {
            $user = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $user || $user->saldo < $amount) {
                return false;
            }

            $user->saldo -= $amount;
            $user->save();

            $user->balanceHistories()->create([
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);

            return true;
        });
    }

    /**
     * Tambah saldo user.
     *
     * Baris user di-lock agar kredit bersamaan (mis. callback + scheduler)
     * tidak saling menimpa nilai saldo (lost update).
     */
    public function credit(float $amount, string $description, ?string $refType = null, ?int $refId = null): void
    {
        DB::transaction(function () use ($amount, $description, $refType, $refId) {
            $user = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $user) {
                return;
            }

            $user->saldo += $amount;
            $user->save();

            $user->balanceHistories()->create([
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'saldo' => 'decimal:2',
            'status' => 'boolean',
        ];
    }
}
