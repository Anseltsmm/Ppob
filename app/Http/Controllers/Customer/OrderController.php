<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Product;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('q');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Order::with('product', 'category')
            ->where('user_id', auth()->id());

        if (in_array($status, ['pending', 'success', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhere('order_code', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $userId = auth()->id();
        $baseQuery = Order::where('user_id', $userId);
        $counts = [
            'all' => (clone $baseQuery)->count(),
            'success' => (clone $baseQuery)->where('status', 'success')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
        ];

        return view('customer.orders.index', compact('orders', 'status', 'search', 'counts', 'dateFrom', 'dateTo'));
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        return view('customer.orders.show', compact('order'));
    }

    public function store(Request $request, Product $product, OkeConnectService $okeconnect)
    {
        if (! $product->status) {
            return $this->fail('Produk tidak tersedia.', $request);
        }

        if (! $okeconnect->isConfigured()) {
            return $this->fail('Transaksi sedang dinonaktifkan sementara. Hubungi admin.', $request);
        }

        $rules = [
            'destination' => ['required', 'string', 'max:30'],
        ];

        $qty = null;
        if ($product->type === 'opendenom' || $product->isBill()) {
            $rules['qty'] = ['required', 'numeric'];
            $validated = $request->validate($rules);
            $qty = (float) $validated['qty'];

            if ($product->min_nominal !== null && $qty < (float) $product->min_nominal) {
                return $this->fail('Nominal minimal '.number_format((float) $product->min_nominal, 0, ',', '.').'.', $request);
            }
            if ($product->max_nominal !== null && $qty > (float) $product->max_nominal) {
                return $this->fail('Nominal maksimal '.number_format((float) $product->max_nominal, 0, ',', '.').'.', $request);
            }
        } else {
            $validated = $request->validate($rules);
        }

        $price = $product->priceFor($qty);

        if ($price < 1) {
            return $this->fail('Harga produk belum diatur.', $request);
        }

        $user = auth()->user();

        if ((float) $user->saldo < $price) {
            return $this->fail('Saldo tidak mencukupi. Silakan topup saldo terlebih dahulu.', $request);
        }

        try {
            $order = DB::transaction(function () use ($user, $product, $validated, $price, $qty) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'order_code' => $this->generateOrderCode(),
                    'product_name' => $product->name,
                    'destination' => $validated['destination'],
                    'qty' => $qty,
                    'buy_price' => ($product->type === 'opendenom' || $product->isBill())
                        ? round(($qty ?? 0) + (float) $product->modal_price)
                        : $product->modal_price,
                    'sell_price' => $price,
                    'status' => Order::STATUS_PENDING,
                ]);

                $user->debit(
                    $price,
                    'Pembelian '.$product->name.' ke '.$validated['destination'],
                    'order',
                    $order->id
                );

                return $order;
            });

            ProcessOrder::dispatch($order);

            $message = 'Order berhasil dibuat. Menunggu pemrosesan...';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'order_code' => $order->order_code,
                    'order_url' => route('customer.orders.show', $order),
                ]);
            }

            return redirect()->route('customer.orders.show', $order)
                ->with('success', $message);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Terjadi kesalahan saat membuat order. Silakan coba lagi.', $request);
        }
    }

    /**
     * Kembalikan respons gagal (JSON jika dari AJAX, kalau tidak redirect back).
     */
    private function fail(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 422);
        }

        return back()->with('error', $message);
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);
    }

    private function generateOrderCode(): string
    {
        return 'INV'.date('ymdHis').strtoupper(Str::random(4));
    }
}
