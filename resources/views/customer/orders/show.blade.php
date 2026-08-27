@extends('layouts.app')

@section('title', 'Detail Order '.$order->order_code)

@section('content')
@php($customerMessage = $order->customerMessage())
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">Riwayat Order</a></li>
        <li class="breadcrumb-item active">{{ $order->order_code }}</li>
    </ol>
</nav>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-bold">Detail Transaksi</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted w-40">Kode Order</td>
                        <td class="fw-bold">{{ $order->order_code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Produk</td>
                        <td>{{ $order->product_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>{{ $order->category?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tujuan</td>
                        <td>{{ $order->destination }}</td>
                    </tr>
                    @if($order->qty)
                    <tr>
                        <td class="text-muted">Nominal</td>
                        <td>{{ number_format($order->qty, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Harga</td>
                        <td class="fw-bold">{{ number_format($order->sell_price, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><span class="badge bg-{{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">ID Transaksi (OkeConnect)</td>
                        <td>{{ $order->trx_id ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Serial Number</td>
                        <td class="fw-bold">{{ $order->sn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Order</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @if($order->checked_at)
                    <tr>
                        <td class="text-muted">Terakhir Dicek</td>
                        <td>{{ $order->checked_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-bold">Status Proses</div>
            <div class="card-body">
                @if($order->status === 'pending')
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning mb-3"></div>
                        <p class="mb-1">Order sedang diproses.</p>
                        <p class="text-muted small">Halaman ini akan otomatis ter-update ketika sistem menerima status dari server.</p>
                    </div>
                    <meta http-equiv="refresh" content="10">
                @elseif($order->status === 'success')
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i> Transaksi berhasil!
                        @if($order->sn)
                            <div class="mt-2 small">Serial Number: <strong>{{ $order->sn }}</strong></div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle-fill"></i> Transaksi gagal. Saldo Anda telah dikembalikan.
                        @if($customerMessage)
                            <div class="mt-2 small">{{ $customerMessage }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($customerMessage)
        <div class="card mt-3">
            <div class="card-header bg-white fw-bold">Response Server</div>
            <div class="card-body">
                <pre class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $customerMessage }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
