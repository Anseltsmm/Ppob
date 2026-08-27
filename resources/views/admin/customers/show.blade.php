@extends('layouts.admin')

@section('title', 'Detail Customer - ' . $customer->name)

@push('styles')
<style>
    .profile-card {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 1rem;
        padding: 1.5rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .profile-card .deco {
        position: absolute; border-radius: 50%; pointer-events: none;
    }
    .profile-card .d1 { width: 150px; height: 150px; background: rgba(255,255,255,.08); top: -60px; right: -30px; }
    .profile-card .d2 { width: 80px; height: 80px; background: rgba(255,255,255,.06); bottom: -30px; left: 15%; }

    .profile-card .avatar {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 700;
        border: 3px solid rgba(255,255,255,.3);
    }
    .profile-card .profile-name { font-weight: 700; font-size: 1.2rem; }
    .profile-card .profile-meta { font-size: .8rem; opacity: .8; }

    .stat-mini {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .75rem;
        padding: .75rem .9rem;
        text-align: center;
    }
    .stat-mini .stat-value { font-weight: 700; font-size: 1.1rem; color: #1e293b; }
    .stat-mini .stat-label { font-size: .7rem; color: #64748b; }

    .section-title {
        font-size: .85rem; font-weight: 700; color: #1e293b;
        display: flex; align-items: center; gap: .4rem;
        margin-bottom: .75rem;
    }

    .history-item {
        display: flex; align-items: center; gap: .65rem;
        padding: .55rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .history-item:last-child { border-bottom: none; }
    .history-item .hi-icon {
        width: 34px; height: 34px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem; flex-shrink: 0;
    }
    .history-item .hi-body { flex: 1; min-width: 0; }
    .history-item .hi-desc {
        font-size: .8rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .history-item .hi-time { font-size: .7rem; color: #94a3b8; }
    .history-item .hi-amount { font-weight: 700; font-size: .85rem; white-space: nowrap; }
    .history-item .hi-amount.credit { color: #16a34a; }
    .history-item .hi-amount.debit { color: #dc2626; }

    .order-item {
        display: flex; align-items: center; gap: .65rem;
        padding: .55rem 0;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none; color: inherit;
    }
    .order-item:last-child { border-bottom: none; }
    .order-item:hover { background: #f8fafc; margin: 0 -.5rem; padding-left: .5rem; padding-right: .5rem; border-radius: .5rem; }
    .order-item .oi-icon {
        width: 34px; height: 34px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem; flex-shrink: 0;
    }
    .order-item .oi-body { flex: 1; min-width: 0; }
    .order-item .oi-name {
        font-size: .8rem; font-weight: 600; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .order-item .oi-meta { font-size: .7rem; color: #94a3b8; }
    .order-item .oi-right { text-align: right; flex-shrink: 0; }
    .order-item .oi-price { font-weight: 700; font-size: .85rem; color: #1e293b; }

    .status-badge {
        font-size: .65rem; font-weight: 600;
        padding: .15rem .45rem; border-radius: 999px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
</style>
@endpush

@section('content')
{{-- Profile Hero --}}
<div class="profile-card">
    <span class="deco d1"></span>
    <span class="deco d2"></span>
    <div class="position-relative d-flex align-items-center gap-3">
        <div class="avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
        <div>
            <div class="profile-name">{{ $customer->name }}</div>
            <div class="profile-meta">
                {{ $customer->email }}
                @if($customer->phone) &middot; {{ $customer->phone }} @endif
            </div>
            <div class="profile-meta">
                Bergabung {{ $customer->created_at->locale('id')->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>
</div>

{{-- Stat Mini --}}
<div class="row g-2 mb-4">
    <div class="col-4">
        <div class="stat-mini">
            <div class="stat-value" style="color:#2563eb;">Rp {{ number_format($customer->saldo, 0, ',', '.') }}</div>
            <div class="stat-label">Saldo</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-mini">
            <div class="stat-value">{{ $customer->orders_count ?? $orders->count() }}</div>
            <div class="stat-label">Total Order</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-mini">
            @if($customer->status)
                <div class="stat-value" style="color:#16a34a;">Aktif</div>
            @else
                <div class="stat-value" style="color:#dc2626;">Nonaktif</div>
            @endif
            <div class="stat-label">Status</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Sidebar: Aksi --}}
    <div class="col-lg-4">
        {{-- Adjust Saldo --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title"><i class="bi bi-wallet2 text-primary"></i> Penyesuaian Saldo</div>
                <form method="POST" action="{{ route('admin.customers.adjust-saldo', $customer) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="type" class="form-select form-select-sm">
                            <option value="credit">Tambah Saldo (+)</option>
                            <option value="debit">Kurangi Saldo (-)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" name="amount" class="form-control form-control-sm" placeholder="Nominal" step="100" min="1" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Keterangan (opsional)">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 fw-semibold"><i class="bi bi-check-lg"></i> Simpan</button>
                </form>
            </div>
        </div>

        {{-- Toggle Status --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.customers.toggle', $customer) }}" onsubmit="return confirm('Ubah status customer?')">
                    @csrf
                    <button class="btn btn-sm w-100 fw-semibold {{ $customer->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                        <i class="bi bi-{{ $customer->status ? 'x-circle' : 'check-circle' }}"></i>
                        {{ $customer->status ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Back --}}
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm w-100">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- Main: Order & Mutasi --}}
    <div class="col-lg-8">
        {{-- Order Terbaru --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="section-title">
                    <i class="bi bi-receipt text-primary"></i> Order Terbaru
                </div>
                @if($orders->count())
                    @foreach($orders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="order-item">
                            <div class="oi-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-receipt"></i></div>
                            <div class="oi-body">
                                <div class="oi-name">{{ $order->product_name }}</div>
                                <div class="oi-meta">#{{ $order->order_code }} &middot; {{ $order->destination }}</div>
                            </div>
                            <div class="oi-right">
                                <div class="oi-price">Rp {{ number_format($order->sell_price, 0, ',', '.') }}</div>
                                <span class="status-badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted small">Belum ada order.</div>
                @endif
            </div>
        </div>

        {{-- Mutasi Saldo --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title">
                    <i class="bi bi-arrow-left-right text-success"></i> Mutasi Saldo
                </div>
                @if($histories->count())
                    @foreach($histories as $history)
                        <div class="history-item">
                            <div class="hi-icon {{ $history->type === 'credit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                <i class="bi bi-{{ $history->type === 'credit' ? 'arrow-down-left' : 'arrow-up-right' }}"></i>
                            </div>
                            <div class="hi-body">
                                <div class="hi-desc">{{ $history->description }}</div>
                                <div class="hi-time">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="hi-amount {{ $history->type }}">
                                {{ $history->type === 'credit' ? '+' : '-' }}Rp {{ number_format($history->amount, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted small">Belum ada mutasi.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
