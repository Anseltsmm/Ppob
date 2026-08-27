<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PendingInquiry;
use App\Models\Setting;
use App\Models\User;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OkeconnectCallbackController extends Controller
{
    /**
     * OkeConnect mengirim callback via GET dengan parameter:
     * refid -> refID yang kita kirim saat transaksi (order_code)
     * message -> teks status transaksi
     */
    public function handle(Request $request, OkeConnectService $okeconnect)
    {
        // Validasi shared-secret (dibaca langsung, tanpa cache, agar token yang
        // baru diregenerate langsung berlaku). Callback OkeConnect tidak punya
        // signature, jadi token query param adalah satu-satunya autentikasi.
        $token = Setting::where('key', 'okeconnect_callback_token')->value('value');
        $requestToken = (string) $request->query('token', '');

        if (! is_string($token) || $token === '' || ! hash_equals($token, $requestToken)) {
            Log::warning('OkeConnect callback ditolak: token tidak valid', ['refid' => $request->query('refid')]);

            return response('UNAUTHORIZED', 401);
        }

        $refId = $request->query('refid');
        $message = (string) $request->query('message', '');

        Log::info('OkeConnect callback diterima', ['refid' => $refId, 'message' => $message]);

        $order = Order::where('order_code', $refId)->first();

        // Cek apakah ini inquiry cek ID pelanggan (bukan order)
        if (! $order) {
            $inquiry = PendingInquiry::where('ref_id', $refId)->first();
            if ($inquiry) {
                return $this->handleInquiryCallback($inquiry, $message, $okeconnect);
            }

            Log::warning('Order tidak ditemukan untuk callback OkeConnect', ['refid' => $refId]);

            return response('ORDER_NOT_FOUND', 404);
        }

        $result = $okeconnect->parse([
            'body' => $message,
        ], 'callback');

        if ($result['status'] === 'unknown') {
            Log::warning('Callback OkeConnect tidak bisa diparse', ['refid' => $refId, 'message' => $message]);

            return response('OK', 200);
        }

        $order->update([
            'trx_id' => $result['trx_id'] ?? $order->trx_id,
            'sn' => $result['sn'] ?? $order->sn,
            'message' => $message,
            'checked_at' => now(),
        ]);

        if ($result['status'] === 'success' && $order->status !== Order::STATUS_SUCCESS) {
            $order->update(['status' => Order::STATUS_SUCCESS]);
        } elseif ($result['status'] === 'failed' && $order->status === Order::STATUS_PENDING) {
            $this->markFailed($order);
        }

        return response('OK', 200);
    }

    /**
     * Handle callback untuk inquiry cek ID pelanggan (CPLN).
     * Extract nama pelanggan dari message & update pending_inquiries.
     */
    private function handleInquiryCallback(PendingInquiry $inquiry, string $message, OkeConnectService $okeconnect): \Symfony\Component\HttpFoundation\Response
    {
        $result = $okeconnect->parse(['body' => $message], 'inquiry');
        $info = $okeconnect->parseInquiry(['body' => $message]);

        // Extract nama dari raw message
        $customerName = $info['customer_name'] ?? null;
        if (! $customerName && preg_match('/(?:NAMA\s*(?:PELANGGAN|PEMILIK)?|CUSTOMER|ATAS NAMA)\s*[:=]?\s*([A-Za-z][A-Za-z .\'-]{2,50})/i', $message, $m)) {
            $customerName = trim($m[1]);
        }

        if ($customerName) {
            $inquiry->update([
                'status' => 'success',
                'customer_name' => $customerName,
                'raw' => $message,
            ]);
        } elseif ($result['status'] === 'failed') {
            $inquiry->update([
                'status' => 'failed',
                'raw' => $message,
            ]);
        } else {
            // Simpan raw untuk debugging
            $inquiry->update(['raw' => $message]);
        }

        Log::info('Inquiry callback diproses', [
            'ref_id' => $inquiry->ref_id,
            'status' => $inquiry->fresh()->status,
            'customer_name' => $customerName,
        ]);

        return response('OK', 200);
    }

    private function markFailed(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->refresh();
            if ($order->status !== Order::STATUS_PENDING) {
                return;
            }

            $user = User::find($order->user_id);
            if ($user) {
                $user->credit(
                    (float) $order->sell_price,
                    'Refund order '.$order->order_code.' ('.$order->product_name.')',
                    'order',
                    $order->id
                );
            }

            $order->update(['status' => Order::STATUS_FAILED]);
        });
    }
}
