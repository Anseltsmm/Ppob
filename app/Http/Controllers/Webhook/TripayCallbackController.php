<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\TripayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripayCallbackController extends Controller
{
    public function handle(Request $request, TripayService $tripay): JsonResponse
    {
        // Ambil payload mentah (Tripay mengirim JSON)
        $payload = $request->json()->all();

        Log::info('Tripay callback diterima', $payload);

        $signature = $payload['signature'] ?? '';
        $merchantRef = $payload['merchant_ref'] ?? '';
        $amountReceived = (int) ($payload['amount_received'] ?? 0);
        $status = $payload['status'] ?? '';
        $reference = $payload['reference'] ?? '';

        // 1. Validasi signature
        if (! $tripay->verifyCallback($signature, $merchantRef, $amountReceived)) {
            Log::warning('Tripay callback signature tidak valid', ['payload' => $payload]);

            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        // 2. Cari deposit berdasarkan merchant_ref (invoice)
        $deposit = Deposit::where('invoice', $merchantRef)->first();

        if (! $deposit) {
            Log::warning('Deposit tidak ditemukan untuk callback', ['merchant_ref' => $merchantRef]);

            return response()->json(['success' => false, 'message' => 'Deposit not found'], 404);
        }

        // 3. Proses sesuai status
        if ($status === 'PAID') {
            $deposit->markPaid($payload);
        } elseif (in_array($status, ['EXPIRED', 'FAILED'], true) && $deposit->status === 'UNPAID') {
            $deposit->update([
                'status' => $status,
                'reference' => $reference ?: $deposit->reference,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
