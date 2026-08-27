<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OkeConnectService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ambang saldo OkeConnect yang dianggap menipis (dalam rupiah).
     */
    private const OKE_BALANCE_LOW = 100_000;

    public function index()
    {
        $today = now()->startOfDay();

        $stats = [
            'customer' => User::where('role', 'customer')->count(),
            'product' => Product::count(),
            'order_today' => Order::where('created_at', '>=', $today)->count(),
            'order_success' => Order::where('status', 'success')->count(),
            'order_pending' => Order::where('status', 'pending')->count(),
            'order_pending_today' => Order::where('status', 'pending')->where('created_at', '>=', $today)->count(),
            'order_failed' => Order::where('status', 'failed')->count(),
            'revenue_today' => Order::where('created_at', '>=', $today)
                ->where('status', 'success')
                ->sum(DB::raw('sell_price - buy_price')),
            'deposit_today' => Deposit::where('created_at', '>=', $today)->where('status', 'PAID')->sum('amount'),
            'saldo_total' => User::where('role', 'customer')->sum('saldo'),
        ];

        $chart = $this->orderChart();

        $pendingOrders = Order::with('user', 'product')
            ->where('status', 'pending')
            ->orderByRaw('checked_at IS NULL DESC, checked_at ASC, created_at ASC')
            ->limit(8)
            ->get();

        $recentOrders = Order::with('user')->latest()->limit(10)->get();
        $recentDeposits = Deposit::with('user')->latest()->limit(10)->get();

        [$okeconnectBalance, $okeconnectError, $okeconnectConfigured, $okeconnectLow] = $this->okeconnectBalance();

        return view('admin.dashboard', compact(
            'stats', 'chart', 'pendingOrders', 'recentOrders', 'recentDeposits',
            'okeconnectBalance', 'okeconnectError', 'okeconnectConfigured', 'okeconnectLow'
        ));
    }

    /**
     * Data grafik jumlah order & profit 7 hari terakhir.
     *
     * @return array{labels: array<int, string>, orders: array<int, int>, profits: array<int, float>}
     */
    private function orderChart(): array
    {
        $days = 7;
        $start = now()->subDays($days - 1)->startOfDay();

        $orderCounts = Order::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $profits = Order::where('status', 'success')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, SUM(sell_price - buy_price) as profit')
            ->groupBy('date')
            ->pluck('profit', 'date');

        $labels = [];
        $orders = [];
        $profitSeries = [];

        foreach (range($days - 1, 0) as $offset) {
            $date = now()->subDays($offset)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->locale('id')->translatedFormat('d M');
            $orders[] = (int) ($orderCounts[$date] ?? 0);
            $profitSeries[] = (float) ($profits[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'orders' => $orders,
            'profits' => $profitSeries,
        ];
    }

    /**
     * Saldo deposit OkeConnect, di-cache 5 menit agar dashboard tetap cepat
     * walau banyak admin yang membuka.
     *
     * @return array{0: ?float, 1: ?string, 2: bool, 3: bool} [saldo, error, configured, low]
     */
    private function okeconnectBalance(): array
    {
        $okeconnect = app(OkeConnectService::class);

        if (! $okeconnect->isConfigured()) {
            return [null, null, false, false];
        }

        try {
            $result = Cache::remember('okeconnect.balance', 300, fn () => $okeconnect->getBalance());
        } catch (\Throwable) {
            return [null, 'Gagal mengambil saldo.', true, false];
        }

        if (($result['status'] ?? '') === 'error') {
            return [null, $result['message'] ?? 'Gagal mengambil saldo.', true, false];
        }

        $balance = isset($result['saldo']) ? (float) $result['saldo'] : null;

        return [$balance, null, true, $balance !== null && $balance < self::OKE_BALANCE_LOW];
    }
}
