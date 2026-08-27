<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\TripayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    public function index(TripayService $tripay)
    {
        $channels = $tripay->isConfigured() ? $tripay->paymentChannels() : [];

        return view('customer.topup.index', compact('channels'));
    }

    public function store(Request $request, TripayService $tripay)
    {
        if (! $tripay->isConfigured()) {
            return back()->with('error', 'Payment gateway belum dikonfigurasi. Hubungi admin.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'method' => ['required', 'string'],
        ]);

        $amount = (int) $validated['amount'];
        $user = auth()->user();

        // Hitung fee via Tripay
        $feeData = $tripay->feeCalculator($amount, $validated['method']);
        $fee = 0;
        if (! empty($feeData) && isset($feeData[0]['total_fee']['customer'])) {
            $fee = (int) $feeData[0]['total_fee']['customer'];
        }

        $invoice = 'INV'.date('ymdHis').strtoupper(Str::random(4));

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'invoice' => $invoice,
            'amount' => $amount,
            'fee_customer' => $fee,
            'total_amount' => $amount + $fee,
            'payment_method' => $validated['method'],
            'status' => 'UNPAID',
        ]);

        $result = $tripay->createTransaction(
            method: $validated['method'],
            merchantRef: $invoice,
            amount: $amount + $fee,
            customer: [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            orderItems: [[
                'sku' => 'TOPUP',
                'name' => 'Topup Saldo PPOB',
                'price' => $amount + $fee,
                'quantity' => 1,
            ]],
            options: [
                'callback_url' => route('webhook.tripay'),
                'return_url' => route('customer.topup.pay', $deposit),
                'expired_time' => 60,
            ]
        );

        // Tripay mengembalikan ['success' => bool, 'message' => ..., 'data' => [...]].
        // Error (success=false) juga menghasilkan array — jadi cek sukses + reference secara eksplisit,
        // agar deposit tidak menggantung di UNPAID tanpa reference (scheduler tidak bisa mengeceknya).
        if (empty($result) || ($result['success'] ?? false) !== true || empty($result['reference'])) {
            Log::warning('Gagal membuat transaksi Tripay', [
                'invoice' => $invoice,
                'message' => $result['message'] ?? 'unknown',
            ]);
            $deposit->update(['status' => 'FAILED']);

            return back()->with('error', 'Gagal membuat transaksi pembayaran di Tripay: '.($result['message'] ?? 'Silakan coba lagi.'));
        }

        $deposit->update([
            'reference' => $result['reference'],
            'pay_code' => $result['pay_code'] ?? null,
            'pay_url' => $result['pay_url'] ?? null,
            'checkout_url' => $result['checkout_url'] ?? null,
            'payment_name' => $result['payment_name'] ?? null,
            'expired_at' => isset($result['expired_at']) ? Carbon::createFromTimestamp((int) $result['expired_at']) : now()->addHour(),
        ]);

        return redirect()->route('customer.topup.pay', $deposit);
    }

    public function pay(Deposit $deposit, TripayService $tripay)
    {
        abort_unless($deposit->user_id === auth()->id(), 403);

        if ($deposit->status === 'UNPAID' && $deposit->reference) {
            try {
                $data = $tripay->detailTransaction($deposit->reference);
                if (($data['status'] ?? null) === 'PAID') {
                    $deposit->markPaid($data);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal cek status deposit', ['deposit' => $deposit->id, 'error' => $e->getMessage()]);
            }
        }

        $instructions = [];
        if ($deposit->payment_method && $deposit->status === 'UNPAID' && $tripay->isConfigured()) {
            try {
                $instructions = $tripay->paymentInstruction(
                    $deposit->payment_method,
                    $deposit->pay_code,
                    (int) $deposit->total_amount
                );
            } catch (\Throwable $e) {
                Log::warning('Gagal ambil instruksi pembayaran', ['error' => $e->getMessage()]);
            }
        }

        return view('customer.topup.pay', compact('deposit', 'instructions'));
    }

    public function history()
    {
        $deposits = Deposit::where('user_id', auth()->id())->latest()->paginate(15);

        return view('customer.topup.history', compact('deposits'));
    }

    public function checkStatus(Deposit $deposit, TripayService $tripay)
    {
        abort_unless($deposit->user_id === auth()->id(), 403);

        if ($deposit->status === 'PAID') {
            return redirect()->route('customer.topup.pay', $deposit);
        }

        if ($deposit->reference && $tripay->isConfigured()) {
            $data = $tripay->detailTransaction($deposit->reference);
            $status = $data['status'] ?? null;

            if ($status === 'PAID') {
                $deposit->markPaid($data);

                return redirect()->route('customer.topup.pay', $deposit)
                    ->with('success', 'Pembayaran terkonfirmasi! Saldo Anda telah ditambahkan.');
            }

            if (in_array($status, ['EXPIRED', 'FAILED'], true) && $deposit->status === 'UNPAID') {
                $deposit->update(['status' => $status]);

                return redirect()->route('customer.topup.pay', $deposit)
                    ->with('error', 'Status pembayaran: '.$status.'. Silakan buat topup baru.');
            }
        }

        return redirect()->route('customer.topup.pay', $deposit)
            ->with('info', 'Pembayaran belum terdeteksi. Silakan selesaikan pembayaran lalu cek kembali.');
    }
}
