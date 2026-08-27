@extends('layouts.admin')

@section('title', 'Topup Saldo')

@php
    $activeStatus = $status ?? 'all';
@endphp

@push('styles')
<style>
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

    .search-bar { position: relative; }
    .search-bar input {
        border-radius: 999px;
        padding: .55rem 1rem .55rem 2.4rem;
        border: 1.5px solid #e2e8f0;
        font-size: .85rem; background: #fff;
        transition: border-color .15s;
    }
    .search-bar input:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.1); outline: none; }
    .search-bar .search-icon {
        position: absolute; left: .85rem; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: .9rem; pointer-events: none;
    }

    .date-group-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        color: #94a3b8; letter-spacing: .05em;
        padding: .6rem 0 .3rem;
    }

    .deposit-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        padding: .85rem 1rem;
        margin-bottom: .5rem;
        text-decoration: none; color: inherit;
        display: flex; gap: .75rem; align-items: flex-start;
        transition: box-shadow .15s, transform .15s;
    }
    .deposit-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07); transform: translateY(-1px); }
    .deposit-card .dep-icon {
        width: 42px; height: 42px; flex-shrink: 0;
        border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #fff;
    }
    .deposit-card .dep-body { flex: 1; min-width: 0; }
    .deposit-card .dep-top {
        display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem;
    }
    .deposit-card .dep-name {
        font-weight: 600; font-size: .85rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .deposit-card .dep-meta {
        font-size: .78rem; color: #64748b; margin-top: 2px;
    }
    .deposit-card .dep-bottom {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .35rem;
    }
    .deposit-card .dep-price {
        font-weight: 700; font-size: .88rem; color: #1e293b;
    }
    .deposit-card .dep-fee {
        font-size: .7rem; color: #94a3b8;
    }
    .deposit-card .dep-date {
        font-size: .7rem; color: #94a3b8;
    }

    .status-dot {
        width: 8px; height: 8px; border-radius: 50%; display: inline-block;
        margin-right: 4px; vertical-align: middle;
    }
    .status-dot.PAID { background: #22c55e; }
    .status-dot.UNPAID { background: #f59e0b; animation: pulse-dot 1.5s infinite; }
    .status-dot.EXPIRED, .status-dot.FAILED { background: #ef4444; }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: .4; }
    }

    .status-badge {
        font-size: .68rem; font-weight: 600;
        padding: .15rem .5rem; border-radius: 999px;
    }
    .status-badge.PAID { background: #dcfce7; color: #15803d; }
    .status-badge.UNPAID { background: #fef3c7; color: #b45309; }
    .status-badge.EXPIRED { background: #fee2e2; color: #dc2626; }
    .status-badge.FAILED { background: #fee2e2; color: #dc2626; }

    .pending-spinner {
        width: 14px; height: 14px; border-width: 2px;
        display: inline-block; vertical-align: middle;
    }

    .pagination .page-link {
        border-radius: 999px; margin: 0 2px;
        font-size: .8rem; padding: .35rem .7rem;
        border: 1px solid #e2e8f0; color: #475569;
    }
    .pagination .page-item.active .page-link {
        background: #2563eb; border-color: #2563eb; color: #fff;
    }
</style>
@endpush

@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-cash-stack text-success me-1"></i> Topup Saldo</h5>

{{-- Search --}}
<form method="GET" action="{{ route('admin.deposits.index') }}" class="mb-3">
    @if($activeStatus !== 'all')
        <input type="hidden" name="status" value="{{ $activeStatus }}">
    @endif
    @if($dateFrom)
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
    @endif
    @if($dateTo)
        <input type="hidden" name="date_to" value="{{ $dateTo }}">
    @endif
    <div class="search-bar">
        <i class="bi bi-search search-icon"></i>
        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari invoice, referensi, atau nama customer..." autocomplete="off">
    </div>
</form>

{{-- Date Filter --}}
<form method="GET" action="{{ route('admin.deposits.index') }}" class="mb-3" id="dateFilterForm">
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
            <a href="{{ route('admin.deposits.index', array_filter(['status' => $activeStatus !== 'all' ? $activeStatus : null, 'q' => $search])) }}"
               class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size:.75rem; white-space:nowrap;">
                <i class="bi bi-x-lg"></i> Reset
            </a>
        @endif
    </div>
</form>

{{-- Filter Tabs --}}
<div class="filter-tabs mb-3">
    <a href="{{ route('admin.deposits.index', array_filter(['q' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
       class="filter-tab {{ $activeStatus === 'all' ? 'active' : '' }}">
        Semua <span class="count">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('admin.deposits.index', array_filter(['status' => 'PAID', 'q' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
       class="filter-tab {{ $activeStatus === 'PAID' ? 'active' : '' }}">
        <span class="status-dot PAID"></span>Lunas <span class="count">{{ $counts['PAID'] }}</span>
    </a>
    <a href="{{ route('admin.deposits.index', array_filter(['status' => 'UNPAID', 'q' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
       class="filter-tab {{ $activeStatus === 'UNPAID' ? 'active' : '' }}">
        <span class="status-dot UNPAID"></span>Belum Dibayar <span class="count">{{ $counts['UNPAID'] }}</span>
    </a>
    <a href="{{ route('admin.deposits.index', array_filter(['status' => 'EXPIRED', 'q' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
       class="filter-tab {{ $activeStatus === 'EXPIRED' ? 'active' : '' }}">
        <span class="status-dot EXPIRED"></span>Kadaluarsa <span class="count">{{ $counts['EXPIRED'] }}</span>
    </a>
</div>

{{-- Deposits --}}
@if($deposits->count())
    @php
        $grouped = $deposits->getCollection()->groupBy(function ($deposit) {
            $date = $deposit->created_at->toDateString();
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();
            if ($date === $today) return 'Hari Ini';
            if ($date === $yesterday) return 'Kemarin';
            if ($deposit->created_at->isPast(now()->startOfWeek())) return 'Minggu Ini';
            return $deposit->created_at->locale('id')->translatedFormat('F Y');
        });
    @endphp

    @foreach($grouped as $label => $items)
        <div class="date-group-label">{{ $label }}</div>
        @foreach($items as $deposit)
            @php
                $method = strtolower($deposit->payment_name ?? $deposit->payment_method ?? '');
                $iconClass = match(true) {
                    str_contains($method, 'bank') || str_contains($method, 'bca') || str_contains($method, 'bri') || str_contains($method, 'bni') || str_contains($method, 'mandiri') => 'icon-bank',
                    str_contains($method, 'ewallet') || str_contains($method, 'gopay') || str_contains($method, 'ovo') || str_contains($method, 'dana') || str_contains($method, 'shopee') => 'icon-ewallet',
                    str_contains($method, 'qr') || str_contains($method, 'qris') => 'icon-qris',
                    default => 'icon-default',
                };
                $icon = match(true) {
                    str_contains($method, 'bank') || str_contains($method, 'bca') || str_contains($method, 'bri') || str_contains($method, 'bni') || str_contains($method, 'mandiri') => 'bi-bank',
                    str_contains($method, 'gopay') => 'bi-wallet2',
                    str_contains($method, 'ovo') => 'bi-phone',
                    str_contains($method, 'dana') => 'bi-wallet',
                    str_contains($method, 'shopee') => 'bi-bag',
                    str_contains($method, 'qr') || str_contains($method, 'qris') => 'bi-qr-code',
                    default => 'bi-cash-stack',
                };
            @endphp
            <a href="{{ route('admin.deposits.show', $deposit) }}" class="deposit-card">
                <div class="dep-icon {{ $iconClass }}">
                    <i class="bi {{ $icon }}"></i>
                </div>
                <div class="dep-body">
                    <div class="dep-top">
                        <div>
                            <div class="dep-name">{{ $deposit->user->name }}</div>
                            <div class="dep-meta">
                                {{ $deposit->invoice }}
                                @if($deposit->payment_name)
                                    &middot; {{ $deposit->payment_name }}
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            @if($deposit->status === 'UNPAID')
                                <span class="status-badge UNPAID">
                                    <i class="spinner-grow spinner-grow-sm pending-spinner"></i> Belum Dibayar
                                </span>
                            @else
                                <span class="status-badge {{ $deposit->status }}">
                                    {{ $deposit->statusLabel() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="dep-bottom">
                        <div>
                            <span class="dep-price">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</span>
                            @if($deposit->fee_customer > 0)
                                <span class="dep-fee">(fee Rp {{ number_format($deposit->fee_customer, 0, ',', '.') }})</span>
                            @endif
                        </div>
                        <span class="dep-date">{{ $deposit->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    @endforeach

    @if($deposits->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $deposits->links() }}
    </div>
    @endif
@else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
        @if($search || $activeStatus !== 'all' || $dateFrom || $dateTo)
            <p class="mb-2">Tidak ada topup yang cocok.</p>
            <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-x-lg"></i> Reset Filter
            </a>
        @else
            <p>Belum ada topup saldo.</p>
        @endif
    </div>
@endif
@endsection
