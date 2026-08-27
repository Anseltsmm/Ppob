<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\OkeConnectService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPendingOrders implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    /**
     * Cek ulang semua order pending yang sudah lewat batas waktu tunggu (>= 60 detik).
     */
    public function handle(OkeConnectService $okeconnect): void
    {
        if (! $okeconnect->isConfigured()) {
            return;
        }

        $orders = Order::where('status', Order::STATUS_PENDING)
            ->where(function ($q) {
                $q->whereNull('checked_at')
                    ->orWhere('checked_at', '<=', now()->subSeconds(60));
            })
            ->limit(50)
            ->get();

        foreach ($orders as $order) {
            $product = $order->product;

            try {
                $result = $okeconnect->checkStatus(
                    $product?->code ?? '',
                    $order->destination,
                    $order->order_code,
                    $order->qty !== null ? (float) $order->qty : null
                );

                $order->update([
                    'trx_id' => $result['trx_id'],
                    'sn' => $result['sn'],
                    'message' => $result['raw'],
                    'checked_at' => now(),
                ]);

                match ($result['status']) {
                    'success' => $order->update(['status' => Order::STATUS_SUCCESS]),
                    'failed' => $this->markFailed($order),
                    default => null, // pending / nodata / unknown — tunggu cek berikutnya
                };
            } catch (\Throwable $e) {
                Log::error('CheckPendingOrders exception', [
                    'order' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function markFailed(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Lock baris order & cek ulang status: jangan refund jika sudah berubah
            // (mis. callback sukses tiba saat snapshot diambil) — cegah refund ganda.
            $fresh = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->status !== Order::STATUS_PENDING) {
                return;
            }

            $user = User::find($fresh->user_id);
            if ($user) {
                $user->credit(
                    (float) $fresh->sell_price,
                    'Refund order '.$fresh->order_code.' ('.$fresh->product_name.')',
                    'order',
                    $fresh->id
                );
            }

            $fresh->update(['status' => Order::STATUS_FAILED]);
        });
    }
}
