@extends('layouts.admin')

@section('title', 'Customer')

@push('styles')
<style>
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

    .cust-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        padding: .85rem 1rem;
        margin-bottom: .5rem;
        text-decoration: none; color: inherit;
        display: flex; gap: .75rem; align-items: center;
        transition: box-shadow .15s, transform .15s;
    }
    .cust-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07); transform: translateY(-1px); }
    .cust-card .cust-avatar {
        width: 44px; height: 44px; flex-shrink: 0;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1rem; color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }
    .cust-card .cust-body { flex: 1; min-width: 0; }
    .cust-card .cust-top {
        display: flex; justify-content: space-between; align-items: center; gap: .5rem;
    }
    .cust-card .cust-name {
        font-weight: 600; font-size: .88rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cust-card .cust-meta {
        font-size: .75rem; color: #94a3b8; margin-top: 1px;
    }
    .cust-card .cust-bottom {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .25rem;
    }
    .cust-card .cust-saldo {
        font-weight: 700; font-size: .88rem; color: #2563eb;
    }
    .cust-card .cust-orders {
        font-size: .72rem; color: #64748b;
    }
    .cust-card .cust-date {
        font-size: .7rem; color: #94a3b8;
    }

    .status-badge {
        font-size: .65rem; font-weight: 600;
        padding: .15rem .45rem; border-radius: 999px;
    }
    .status-badge.active { background: #dcfce7; color: #15803d; }
    .status-badge.inactive { background: #fee2e2; color: #dc2626; }

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
<h5 class="fw-bold mb-3"><i class="bi bi-people text-primary me-1"></i> Customer</h5>

{{-- Search --}}
<form method="GET" action="{{ route('admin.customers.index') }}" class="mb-3">
    <div class="search-bar">
        <i class="bi bi-search search-icon"></i>
        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, email, atau nomor HP..." autocomplete="off">
    </div>
</form>

{{-- Customer List --}}
@if($customers->count())
    @foreach($customers as $customer)
        <a href="{{ route('admin.customers.show', $customer) }}" class="cust-card">
            <div class="cust-avatar">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div class="cust-body">
                <div class="cust-top">
                    <div>
                        <div class="cust-name">{{ $customer->name }}</div>
                        <div class="cust-meta">
                            {{ $customer->email }}
                            @if($customer->phone) &middot; {{ $customer->phone }} @endif
                        </div>
                    </div>
                    <div class="text-end">
                        @if($customer->status)
                            <span class="status-badge active">Aktif</span>
                        @else
                            <span class="status-badge inactive">Nonaktif</span>
                        @endif
                    </div>
                </div>
                <div class="cust-bottom">
                    <div>
                        <span class="cust-saldo">Rp {{ number_format($customer->saldo, 0, ',', '.') }}</span>
                        <span class="cust-orders">&middot; {{ $customer->orders_count }} order</span>
                    </div>
                    <span class="cust-date">{{ $customer->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </a>
    @endforeach

    @if($customers->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $customers->links() }}
    </div>
    @endif
@else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-2"></i>
        @if($search)
            <p class="mb-2">Tidak ada customer yang cocok.</p>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-x-lg"></i> Reset
            </a>
        @else
            <p>Belum ada customer terdaftar.</p>
        @endif
    </div>
@endif
@endsection
