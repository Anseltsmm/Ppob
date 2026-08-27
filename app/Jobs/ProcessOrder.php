<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\OkeConnectService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public Order $order)
    {
    }

    public function handle(OkeConnectService $okeconnect): void
    {
        // Jangan proses ulang jika sudah berstatus final
        if ($this->order->status !== Order::STATUS_PENDING) {
            return;
        }

        $product = $this->order->product;

        if (! $okeconnect->isConfigured()) {
            Log::warning('OkeConnect belum dikonfigurasi, order di-refund', ['order' => $this->order->id]);
            $this->order->update(['message' => 'Transaksi dibatalkan: kredensial OkeConnect belum diatur.']);
            $this->markFailed([]);

            return;
        }

        try {
            $result = $okeconnect->transact(
                $product?->code ?? '',
                $this->order->destination,
                $this->order->order_code,
                $this->order->qty !== null ? (float) $this->order->qty : null
            );

            $this->order->update([
                'trx_id' => $result['trx_id'],
                'sn' => $result['sn'],
                'message' => $result['raw'],
                'checked_at' => now(),
            ]);

            match ($result['status']) {
                'success' => $this->markSuccess($result),
                'failed' => $this->markFailed($result),
                'pending' => $this->scheduleRecheck(),
                default => $this->handleUnknown($result),
            };
        } catch (\Throwable $e) {
            Log::error('ProcessOrder exception', [
                'order' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function markSuccess(array $result): void
    {
        $this->order->update([
            'status' => Order::STATUS_SUCCESS,
            'checked_at' => now(),
        ]);
    }

    private function markFailed(array $result): void
    {
        DB::transaction(function () {
            // Lock baris order & cek ulang status: jangan refund jika sudah berubah
            // (mis. callback sukses tiba saat request berlangsung) — mencegah refund ganda/palsu.
            $order = Order::whereKey($this->order->id)->lockForUpdate()->first();

            if (! $order || $order->status !== Order::STATUS_PENDING) {
                return;
            }

            // Refund saldo customer
            $user = User::find($order->user_id);
            if ($user) {
                $user->credit(
                    (float) $order->sell_price,
                    'Refund order '.$order->order_code.' ('.$order->product_name.')',
                    'order',
                    $order->id
                );
            }

            $order->update([
                'status' => Order::STATUS_FAILED,
                'checked_at' => now(),
            ]);
        });
    }

    private function scheduleRecheck(): void
    {
        // Order tetap pending; akan dicek ulang oleh job CheckPendingOrders
        $this->order->update(['checked_at' => now()]);
    }

    private function handleUnknown(array $result): void
    {
        // Status tidak dikenali — biarkan pending agar dicek ulang
        $this->order->update(['checked_at' => now()]);
    }
}
