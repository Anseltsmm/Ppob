<?php

namespace App\Jobs;

use App\Models\Deposit;
use App\Services\TripayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckPendingDeposits implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    /**
     * Cek status deposit UNPAID ke Tripay dan kredit saldo jika sudah PAID.
     */
    public function handle(TripayService $tripay): void
    {
        if (! $tripay->isConfigured()) {
            return;
        }

        $deposits = Deposit::where('status', 'UNPAID')
            ->whereNotNull('reference')
            ->limit(50)
            ->get();

        foreach ($deposits as $deposit) {
            try {
                $data = $tripay->detailTransaction($deposit->reference);
                $status = $data['status'] ?? null;

                if ($status === 'PAID') {
                    $deposit->markPaid($data);
                } elseif (in_array($status, ['EXPIRED', 'FAILED'], true) && $deposit->status === 'UNPAID') {
                    $deposit->update(['status' => $status]);
                }
            } catch (\Throwable $e) {
                Log::error('CheckPendingDeposits exception', [
                    'deposit' => $deposit->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
