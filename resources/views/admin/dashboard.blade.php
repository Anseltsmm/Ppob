@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ===== Header Card ===== */
    .dash-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #6366f1 100%);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .dash-hero .deco {
        position: absolute; border-radius: 50%; pointer-events: none;
    }
    .dash-hero .d1 { width: 200px; height: 200px; background: rgba(255,255,255,.07); top: -80px; right: -40px; }
    .dash-hero .d2 { width: 120px; height: 120px; background: rgba(255,255,255,.05); bottom: -50px; left: 20%; }
    .dash-hero .d3 { width: 60px; height: 60px; background: rgba(255,255,255,.09); bottom: -20px; right: 18%; }

    /* ===== Stat Cards ===== */
    .stat-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: .85rem;
        transition: box-shadow .15s, transform .15s;
        text-decoration: none; color: inherit;
    }
    .stat-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.07); transform: translateY(-2px); }
    .stat-card .stat-icon {
        width: 46px; height: 46px; border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }
    .stat-card .stat-value { font-weight: 700; font-size: 1.35rem; line-height: 1.1; }
    .stat-card .stat-label { font-size: .72rem; color: #64748b; }

    .si-blue { background: #eff6ff; color: #2563eb; }
    .si-green { background: #f0fdf4; color: #16a34a; }
    .si-amber { background: #fffbeb; color: #d97706; }
    .si-red { background: #fef2f2; color: #dc2626; }
    .si-purple { background: #f5f3ff; color: #7c3aed; }
    .si-cyan { background: #ecfeff; color: #0891b2; }

    /* ===== OkeConnect Card ===== */
    .oke-card {
        border-radius: .85rem;
        padding: 1.1rem 1.25rem;
        background: #fff;
        border: 1px solid #eef2f7;
    }
    .oke-card .oke-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
    }

    /* ===== Pending Order Card ===== */
    .pending-card {
        background: #fff;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: .7rem;
        padding: .75rem 1rem;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        transition: box-shadow .15s;
    }
    .pending-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
    .pending-card .pc-body { flex: 1; min-width: 0; }
    .pending-card .pc-name {
        font-weight: 600; font-size: .85rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .pending-card .pc-meta { font-size: .75rem; color: #64748b; }
    .pending-card .pc-time { font-size: .7rem; color: #94a3b8; white-space: nowrap; }

    /* ===== Recent List ===== */
    .recent-item {
        display: flex; align-items: center; gap: .65rem;
        padding: .6rem 0;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none; color: inherit;
    }
    .recent-item:last-child { border-bottom: none; }
    .recent-item:hover { background: #f8fafc; margin: 0 -.75rem; padding-left: .75rem; padding-right: .75rem; border-radius: .5rem; }
    .recent-item .ri-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .95rem; flex-shrink: 0;
    }
    .recent-item .ri-body { flex: 1; min-width: 0; }
    .recent-item .ri-title {
        font-size: .82rem; font-weight: 600; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .recent-item .ri-sub { font-size: .72rem; color: #94a3b8; }
    .recent-item .ri-right { text-align: right; flex-shrink: 0; }
    .recent-item .ri-price { font-size: .85rem; font-weight: 700; color: #1e293b; }

    .badge-status {
        font-size: .65rem; font-weight: 600; padding: .15rem .45rem; border-radius: 999px;
    }

    /* ===== Section Title ===== */
    .section-title {
        font-size: .85rem; font-weight: 700; color: #1e293b;
        display: flex; align-items: center; gap: .4rem;
        margin-bottom: .75rem;
    }
    .section-title .see-all {
        margin-left: auto; font-size: .75rem; font-weight: 500;
        color: #2563eb; text-decoration: none;
    }
    .section-title .see-all:hover { text-decoration: underline; }

    /* ===== Chart ===== */
    .chart-wrap {
        position: relative;
        height: 240px;
    }
    .chart-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }
</style>
@endpush

@section('content')
{{-- ===== HERO HEADER ===== --}}
<div class="dash-hero">
    <span class="deco d1"></span>
    <span class="deco d2"></span>
    <span class="deco d3"></span>
    <div class="position-relative">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div style="font-size:.8rem; opacity:.75;">Selamat datang kembali,</div>
                <div class="fw-bold fs-4">{{ auth()->user()->name }}</div>
                <div style="font-size:.8rem; opacity:.7; margin-top:2px;">
                    <i class="bi bi-calendar3"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light fw-semibold">
                    <i class="bi bi-receipt"></i> Transaksi
                </a>
                <a href="{{ route('admin.products.import') }}" class="btn btn-sm btn-warning fw-semibold">
                    <i class="bi bi-cloud-download"></i> Import
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ===== STAT CARDS ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.customers.index') }}" class="stat-card h-100">
            <div class="stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value">{{ $stats['customer'] }}</div>
                <div class="stat-label">Customer</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.products.index') }}" class="stat-card h-100">
            <div class="stat-icon si-purple"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-value">{{ $stats['product'] }}</div>
                <div class="stat-label">Produk</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.orders.index') }}" class="stat-card h-100">
            <div class="stat-icon si-green"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
                <div class="stat-value">{{ $stats['order_today'] }}</div>
                <div class="stat-label">Order Hari Ini</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="stat-icon si-amber"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.15rem;">Rp {{ number_format($stats['saldo_total'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Saldo Customer</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== PROFIT + ORDER STATUS ===== --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="stat-icon si-green"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.15rem;">Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}</div>
                <div class="stat-label">Profit Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="stat-icon si-cyan"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['order_success'] }}</div>
                <div class="stat-label">Order Sukses</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="stat-card h-100">
            <div class="stat-icon si-amber"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value">{{ $stats['order_pending'] }}</div>
                <div class="stat-label">Order Pending @if($stats['order_pending_today']) <span class="text-muted">({{ $stats['order_pending_today'] }} hari ini)</span>@endif</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="stat-icon si-red"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="stat-value">{{ $stats['order_failed'] }}</div>
                <div class="stat-label">Order Gagal</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== OKECONNECT + CHART ===== --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="oke-card h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="oke-icon" style="background:#fef2f2; color:#dc2626;">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div>
                    <div style="font-size:.75rem; color:#64748b;">Saldo OkeConnect</div>
                    @if(! $okeconnectConfigured)
                        <div class="fw-bold">Belum dikonfigurasi</div>
                    @elseif($okeconnectError)
                        <div class="fw-bold text-danger" style="font-size:1rem;">{{ $okeconnectError }}</div>
                    @else
                        <div class="fw-bold" style="font-size:1.25rem;">Rp {{ number_format($okeconnectBalance ?? 0, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(! $okeconnectConfigured)
                    <span class="badge bg-secondary">Belum diatur</span>
                    <a href="{{ route('admin.settings.index') }}" class="small">Atur kredensial</a>
                @elseif($okeconnectError)
                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Gagal mengambil</span>
                @elseif($okeconnectLow)
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Saldo menipis</span>
                    <span class="small text-muted">Segera topup deposit.</span>
                @else
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terhubung</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title mb-3">
                    <i class="bi bi-graph-up text-primary"></i> Tren Order & Profit (7 Hari)
                </div>
                <div class="chart-wrap">
                    <canvas id="orderChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== ORDER PENDING ===== --}}
<div class="section-title">
    <i class="bi bi-hourglass-split text-warning"></i> Order Pending
    @if($pendingOrders->count())
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="see-all">Lihat semua →</a>
    @endif
</div>
@if($pendingOrders->count())
    @foreach($pendingOrders as $order)
        <a href="{{ route('admin.orders.show', $order) }}" class="pending-card">
            <div class="stat-icon si-amber" style="width:38px;height:38px;font-size:1rem;border-radius:10px;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="pc-body">
                <div class="pc-name">{{ $order->product_name }}</div>
                <div class="pc-meta">
                    {{ $order->user->name }} &middot; {{ $order->order_code }}
                    @if($order->checked_at)
                        &middot; Dicek {{ $order->checked_at->diffForHumans() }}
                    @else
                        &middot; <span class="text-danger fw-semibold">Belum dicek</span>
                    @endif
                </div>
            </div>
            <div class="pc-time">{{ $order->created_at->format('d/m H:i') }}</div>
            <i class="bi bi-chevron-right text-muted"></i>
        </a>
    @endforeach
@else
    <div class="text-center py-4 text-muted" style="font-size:.85rem;">
        <i class="bi bi-check-circle text-success" style="font-size:1.5rem;"></i>
        <div class="mt-1">Semua order sudah diproses.</div>
    </div>
@endif

<div class="mb-4"></div>

{{-- ===== ORDER & TOPUP TERBARU ===== --}}
<div class="row g-3">
    {{-- Order Terbaru --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="section-title">
                    <i class="bi bi-receipt text-primary"></i> Order Terbaru
                    <a href="{{ route('admin.orders.index') }}" class="see-all">Lihat semua →</a>
                </div>
                @foreach($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="recent-item">
                        <div class="ri-icon si-blue"><i class="bi bi-receipt"></i></div>
                        <div class="ri-body">
                            <div class="ri-title">{{ $order->product_name }}</div>
                            <div class="ri-sub">{{ $order->user->name }} &middot; {{ $order->order_code }}</div>
                        </div>
                        <div class="ri-right">
                            <div class="ri-price">Rp {{ number_format($order->sell_price, 0, ',', '.') }}</div>
                            <span class="badge-status badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </a>
                @endforeach
                @if(! $recentOrders->count())
                    <div class="text-center py-4 text-muted small">Belum ada order.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Topup Terbaru --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <div class="section-title">
                    <i class="bi bi-cash-stack text-success"></i> Topup Terbaru
                    <a href="{{ route('admin.deposits.index') }}" class="see-all">Lihat semua →</a>
                </div>
                @foreach($recentDeposits as $deposit)
                    <a href="{{ route('admin.deposits.show', $deposit) }}" class="recent-item">
                        <div class="ri-icon si-green"><i class="bi bi-wallet2"></i></div>
                        <div class="ri-body">
                            <div class="ri-title">{{ $deposit->user->name }}</div>
                            <div class="ri-sub">{{ $deposit->invoice }}</div>
                        </div>
                        <div class="ri-right">
                            <div class="ri-price">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</div>
                            <span class="badge-status badge bg-{{ $deposit->statusBadge() }}">{{ $deposit->statusLabel() }}</span>
                        </div>
                    </a>
                @endforeach
                @if(! $recentDeposits->count())
                    <div class="text-center py-4 text-muted small">Belum ada topup.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('orderChart');
    if (!el || typeof Chart === 'undefined') return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: @json($chart['labels']),
            datasets: [
                {
                    label: 'Order',
                    data: @json($chart['orders']),
                    borderColor: '#2563eb',
                    backgroundColor: (ctx) => {
                        const { ctx: c, chartArea } = ctx.chart;
                        if (!chartArea) return 'rgba(37, 99, 235, .05)';
                        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, 'rgba(37, 99, 235, .26)');
                        g.addColorStop(1, 'rgba(37, 99, 235, .02)');
                        return g;
                    },
                    borderWidth: 2.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#2563eb',
                    pointHoverBorderColor: '#fff',
                    fill: true,
                    tension: .45,
                },
                {
                    label: 'Profit (Rp)',
                    data: @json($chart['profits']),
                    borderColor: '#16a34a',
                    backgroundColor: (ctx) => {
                        const { ctx: c, chartArea } = ctx.chart;
                        if (!chartArea) return 'rgba(22, 163, 74, .05)';
                        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, 'rgba(22, 163, 74, .26)');
                        g.addColorStop(1, 'rgba(22, 163, 74, .02)');
                        return g;
                    },
                    borderWidth: 2.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#16a34a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#16a34a',
                    pointHoverBorderColor: '#fff',
                    fill: true,
                    tension: .45,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            layout: { padding: { top: 8 } },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, pointStyle: 'circle', padding: 18, boxWidth: 8, font: { size: 12, weight: '600' } },
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, .92)',
                    titleFont: { weight: '700' },
                    padding: 12,
                    cornerRadius: 10,
                    usePointStyle: true,
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.label === 'Profit (Rp)') {
                                return 'Profit: Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID');
                            }
                            return ctx.dataset.label + ': ' + ctx.parsed.y;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#64748b' },
                },
                y: {
                    beginAtZero: true,
                    position: 'left',
                    grid: { color: 'rgba(148, 163, 184, .14)', drawBorder: false },
                    ticks: { precision: 0, font: { size: 11 }, color: '#64748b' },
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b',
                        callback: function (value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        },
                    },
                },
            },
        },
    });
});
</script>
@endpush
