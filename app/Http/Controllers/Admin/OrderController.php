<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('q');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Order::with('user', 'product', 'category');

        if (in_array($status, ['pending', 'success', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $base = Order::query();
        $counts = [
            'all' => (clone $base)->count(),
            'success' => (clone $base)->where('status', 'success')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'status', 'search', 'counts', 'dateFrom', 'dateTo'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    public function checkStatus(Order $order, OkeConnectService $okeconnect)
    {
        if ($order->status !== 'pending') {
            return back()->with('info', 'Order sudah berstatus final ('.$order->statusLabel().').');
        }

        if (! $okeconnect->isConfigured()) {
            return back()->with('error', 'Kredensial OkeConnect belum diatur.');
        }

        $product = $order->product;
        $result = $okeconnect->checkStatus(
            $product?->code ?? '',
            $order->destination,
            $order->order_code,
            $order->qty !== null ? (float) $order->qty : null
        );

        $order->update([
            'trx_id' => $result['trx_id'] ?? $order->trx_id,
            'sn' => $result['sn'] ?? $order->sn,
            'message' => $result['raw'],
            'checked_at' => now(),
        ]);

        if ($result['status'] === 'success') {
            $order->update(['status' => Order::STATUS_SUCCESS]);

            return back()->with('success', 'Order dikonfirmasi sukses oleh OkeConnect.');
        }

        if ($result['status'] === 'failed') {
            // Refund manual — lock baris & cek ulang status agar tidak refund ganda
            // bila status sudah berubah (mis. callback tiba bersamaan).
            $refunded = DB::transaction(function () use ($order) {
                $fresh = Order::whereKey($order->id)->lockForUpdate()->first();

                if (! $fresh || $fresh->status !== Order::STATUS_PENDING) {
                    return false;
                }

                $fresh->user?->credit(
                    (float) $fresh->sell_price,
                    'Refund order '.$fresh->order_code.' ('.$fresh->product_name.')',
                    'order',
                    $fresh->id
                );
                $fresh->update(['status' => Order::STATUS_FAILED]);

                return true;
            });

            return back()->with(
                $refunded ? 'success' : 'info',
                $refunded
                    ? 'Order gagal. Saldo customer telah direfund.'
                    : 'Status order sudah berubah. Tidak ada refund tambahan.'
            );
        }

        return back()->with('info', 'Status order masih pending di OkeConnect. Coba lagi beberapa saat lagi.');
    }
}
