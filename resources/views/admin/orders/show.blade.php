@extends('layouts.admin')

@section('title', 'Detail Transaksi '.$order->order_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Transaksi</h4>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-bold">Informasi Order</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted w-40">Kode Order</td><td class="fw-bold">{{ $order->order_code }}</td></tr>
                    <tr><td class="text-muted">Customer</td><td>{{ $order->user->name }} ({{ $order->user->email }})</td></tr>
                    <tr><td class="text-muted">Produk</td><td>{{ $order->product_name }}</td></tr>
                    <tr><td class="text-muted">Kategori</td><td>{{ $order->category?->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Tujuan</td><td>{{ $order->destination }}</td></tr>
                    @if($order->qty)
                    <tr><td class="text-muted">Nominal</td><td>{{ number_format($order->qty, 0, ',', '.') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Harga Modal</td><td>{{ number_format($order->buy_price, 0, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Harga Jual</td><td class="fw-bold">{{ number_format($order->sell_price, 0, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Profit</td><td class="text-success fw-bold">{{ number_format($order->sell_price - $order->buy_price, 0, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td></tr>
                    <tr><td class="text-muted">ID Transaksi (T#)</td><td>{{ $order->trx_id ?: '-' }}</td></tr>
                    <tr><td class="text-muted">Serial Number</td><td class="fw-bold">{{ $order->sn ?: '-' }}</td></tr>
                    <tr><td class="text-muted">Waktu Order</td><td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold">Aksi</div>
            <div class="card-body">
                @if($order->status === 'pending')
                    <form method="POST" action="{{ route('admin.orders.check-status', $order) }}">
                        @csrf
                        <button class="btn btn-warning w-100"><i class="bi bi-arrow-repeat"></i> Cek Status ke OkeConnect</button>
                    </form>
                    <div class="form-text mt-2">Order pending juga otomatis dicek oleh scheduler setiap menit.</div>
                @else
                    <div class="alert alert-light border mb-0">Order sudah berstatus final.</div>
                @endif
            </div>
        </div>

        @if($order->message)
        <div class="card">
            <div class="card-header bg-white fw-bold">Response OkeConnect</div>
            <div class="card-body">
                <pre class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $order->message }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
