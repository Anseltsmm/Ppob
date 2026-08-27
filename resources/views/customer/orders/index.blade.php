@extends('layouts.app')

@php
    $pageTitle = 'Riwayat Order';
    $activeStatus = $status ?? 'all';
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
    /* ===== Filter Tabs ===== */
    .filter-tabs {
        display: flex; gap: .4rem;
        overflow-x: auto; -webkit-overflow-scrolling: touch;
        scrollbar-width: none; padding-bottom: 2px;
    }
    .filter-tabs::-webkit-scrollbar { display: none; }
    .filter-tab {
        flex: 0 0 auto;
        padding: .4rem .85rem;
        border-radius: 999px;
        font-size: .78rem; font-weight: 600;
        border: 1.5px solid #e2e8f0;
        background: #fff; color: #64748b;
        transition: all .15s;
        white-space: nowrap;
        text-decoration: none;
    }
    .filter-tab:hover { border-color: #93c5fd; color: #2563eb; }
    .filter-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .filter-tab .count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px;
        border-radius: 999px; font-size: .65rem; font-weight: 700;
        background: #e2e8f0; color: #475569; padding: 0 5px;
        margin-left: 4px;
    }
    .filter-tab.active .count { background: rgba(255,255,255,.25); color: #fff; }

    /* ===== Search Bar ===== */
    .search-bar {
        position: relative;
    }
    .search-bar input {
        border-radius: 999px;
        padding: .55rem 1rem .55rem 2.4rem;
        border: 1.5px solid #e2e8f0;
        font-size: .85rem;
        background: #fff;
        transition: border-color .15s;
    }
    .search-bar input:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.1); outline: none; }
    .search-bar .search-icon {
        position: absolute; left: .85rem; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: .9rem; pointer-events: none;
    }

    /* ===== Date Group ===== */
    .date-group-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        color: #94a3b8; letter-spacing: .05em;
        padding: .6rem 0 .3rem;
    }

    /* ===== Order Card ===== */
    .order-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        padding: .85rem 1rem;
        margin-bottom: .5rem;
        text-decoration: none; color: inherit;
        display: flex; gap: .75rem; align-items: flex-start;
        transition: box-shadow .15s, transform .15s;
    }
    .order-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07); transform: translateY(-1px); }
    .order-card .order-icon {
        width: 42px; height: 42px; flex-shrink: 0;
        border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #fff;
    }
    .order-card .order-body { flex: 1; min-width: 0; }
    .order-card .order-top {
        display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem;
    }
    .order-card .order-name {
        font-weight: 600; font-size: .85rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .order-card .order-dest {
        font-size: .78rem; color: #64748b; margin-top: 2px;
    }
    .order-card .order-bottom {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .35rem;
    }
    .order-card .order-price {
        font-weight: 700; font-size: .88rem; color: #1e293b;
    }
    .order-card .order-date {
        font-size: .7rem; color: #94a3b8;
    }

    /* Status dot */
    .status-dot {
        width: 8px; height: 8px; border-radius: 50%; display: inline-block;
        margin-right: 4px; vertical-align: middle;
    }
    .status-dot.success { background: #22c55e; }
    .status-dot.pending { background: #f59e0b; animation: pulse-dot 1.5s infinite; }
    .status-dot.failed { background: #ef4444; }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: .4; }
    }

    /* Badge status */
    .status-badge {
        font-size: .68rem; font-weight: 600;
        padding: .15rem .5rem; border-radius: 999px;
    }
    .status-badge.success { background: #dcfce7; color: #15803d; }
    .status-badge.pending { background: #fef3c7; color: #b45309; }
    .status-badge.failed { background: #fee2e2; color: #dc2626; }

    /* Icon gradient per category */
    .icon-pulsa { background: linear-gradient(135deg, #60a5fa, #1d4ed8); }
    .icon-data { background: linear-gradient(135deg, #4ade80, #15803d); }
    .icon-pln { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .icon-ewallet { background: linear-gradient(135deg, #f472b6, #be185d); }
    .icon-game { background: linear-gradient(135deg, #a78bfa, #6d28d9); }
    .icon-tagihan { background: linear-gradient(135deg, #22d3ee, #0e7490); }
    .icon-voucher { background: linear-gradient(135deg, #fb923c, #c2410c); }
    .icon-transfer { background: linear-gradient(135deg, #38bdf8, #0369a1); }
    .icon-default { background: linear-gradient(135deg, #94a3b8, #475569); }

    /* ===== Pagination ===== */
    .pagination .page-link {
        border-radius: 999px; margin: 0 2px;
        font-size: .8rem; padding: .35rem .7rem;
        border: 1px solid #e2e8f0; color: #475569;
    }
    .pagination .page-item.active .page-link {
        background: #2563eb; border-color: #2563eb; color: #fff;
    }

    /* Pending spinner inline */
    .pending-spinner {
        width: 14px; height: 14px; border-width: 2px;
        display: inline-block; vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="mt-3">
    {{-- Header --}}
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-1"></i> {{ $pageTitle }}</h5>

    {{-- Search --}}
    <form method="GET" action="{{ route('customer.orders.index') }}" class="mb-3">
        @if($activeStatus !== 'all')
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        <div class="search-bar">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari produk, nomor, atau kode order..." autocomplete="off">
        </div>
    </form>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('customer.orders.index') }}" class="mb-3" id="dateFilterForm">
        @if($activeStatus !== 'all')
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        @if($search)
            <input type="hidden" name="q" value="{{ $search }}">
        @endif
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width:0;">
                <i class="bi bi-calendar3 text-muted" style="font-size:.85rem;"></i>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm py-1 px-2" style="font-size:.8rem;" onchange="document.getElementById('dateFilterForm').submit()">
                <span class="text-muted" style="font-size:.75rem;">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm py-1 px-2" style="font-size:.8rem;" onchange="document.getElementById('dateFilterForm').submit()">
            </div>
            @if($dateFrom || $dateTo)
                <a href="{{ route('customer.orders.index', array_filter(['status' => $activeStatus !== 'all' ? $activeStatus : null, 'q' => $search])) }}"
                   class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size:.75rem; white-space:nowrap;">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Filter Tabs --}}
    <div class="filter-tabs mb-3">
        <a href="{{ route('customer.orders.index', array_filter(['q' => $search])) }}"
           class="filter-tab {{ $activeStatus === 'all' ? 'active' : '' }}">
            Semua <span class="count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('customer.orders.index', array_filter(['status' => 'success', 'q' => $search])) }}"
           class="filter-tab {{ $activeStatus === 'success' ? 'active' : '' }}">
            <span class="status-dot success"></span>Berhasil <span class="count">{{ $counts['success'] }}</span>
        </a>
        <a href="{{ route('customer.orders.index', array_filter(['status' => 'pending', 'q' => $search])) }}"
           class="filter-tab {{ $activeStatus === 'pending' ? 'active' : '' }}">
            <span class="status-dot pending"></span>Proses <span class="count">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('customer.orders.index', array_filter(['status' => 'failed', 'q' => $search])) }}"
           class="filter-tab {{ $activeStatus === 'failed' ? 'active' : '' }}">
            <span class="status-dot failed"></span>Gagal <span class="count">{{ $counts['failed'] }}</span>
        </a>
    </div>

    {{-- Orders --}}
    @if($orders->count())
        @php
            $grouped = $orders->getCollection()->groupBy(function ($order) {
                $date = $order->created_at->toDateString();
                $today = now()->toDateString();
                $yesterday = now()->subDay()->toDateString();
                if ($date === $today) return 'Hari Ini';
                if ($date === $yesterday) return 'Kemarin';
                if ($order->created_at->isPast(now()->startOfWeek())) return 'Minggu Ini';
                return $order->created_at->locale('id')->translatedFormat('F Y');
            });
        @endphp

        @foreach($grouped as $label => $items)
            <div class="date-group-label">{{ $label }}</div>
            @foreach($items as $order)
                @php
                    $catName = strtolower($order->category?->name ?? '');
                    $iconClass = match(true) {
                        str_contains($catName, 'pulsa') && !str_contains($catName, 'transfer') => 'icon-pulsa',
                        str_contains($catName, 'data') || str_contains($catName, 'voucher') => 'icon-data',
                        str_contains($catName, 'pln') || str_contains($catName, 'token') => 'icon-pln',
                        str_contains($catName, 'ewallet') || str_contains($catName, 'e-wallet') => 'icon-ewallet',
                        str_contains($catName, 'game') => 'icon-game',
                        str_contains($catName, 'tagihan') || str_contains($catName, 'pascabayar') => 'icon-tagihan',
                        str_contains($catName, 'transfer') => 'icon-transfer',
                        default => 'icon-default',
                    };
                    $icon = match(true) {
                        str_contains($catName, 'pln') || str_contains($catName, 'token') => 'bi-lightning-charge-fill',
                        str_contains($catName, 'data') || str_contains($catName, 'voucher') => 'bi-wifi',
                        str_contains($catName, 'ewallet') || str_contains($catName, 'e-wallet') => 'bi-wallet2',
                        str_contains($catName, 'game') => 'bi-controller',
                        str_contains($catName, 'tagihan') || str_contains($catName, 'pascabayar') => 'bi-receipt',
                        str_contains($catName, 'transfer') => 'bi-arrow-left-right',
                        default => 'bi-phone',
                    };
                @endphp
                <a href="{{ route('customer.orders.show', $order) }}" class="order-card">
                    <div class="order-icon {{ $iconClass }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="order-body">
                        <div class="order-top">
                            <div>
                                <div class="order-name">{{ $order->product_name }}</div>
                                <div class="order-dest">
                                    <i class="bi bi-hash"></i>{{ $order->destination }}
                                    @if($order->qty && $order->qty > 0)
                                        &middot; {{ number_format($order->qty, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                @if($order->status === 'pending')
                                    <span class="status-badge pending">
                                        <i class="spinner-grow spinner-grow-sm pending-spinner"></i> Proses
                                    </span>
                                @else
                                    <span class="status-badge {{ $order->statusBadge() }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="order-bottom">
                            <span class="order-price">Rp {{ number_format($order->sell_price, 0, ',', '.') }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endforeach

        @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $orders->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
            @if($search || $activeStatus !== 'all')
                <p class="mb-2">Tidak ada order yang cocok.</p>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="bi bi-x-lg"></i> Reset Filter
                </a>
            @else
                <p class="mb-1">Belum ada riwayat order.</p>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-plus-lg"></i> Mulai Belanja
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    @if($activeStatus === 'pending' && $orders->count())
    // Auto-refresh jika ada order pending
    let refreshTimer = setInterval(() => {
        window.location.reload();
    }, 15000);

    // Pause auto-refresh saat tab tidak aktif (hemat resources)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(refreshTimer);
        } else {
            refreshTimer = setInterval(() => window.location.reload(), 15000);
        }
    });
    @endif
</script>
@endpush
