@extends('layouts.admin')

@section('title', 'Detail Topup '.$deposit->invoice)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Topup</h4>
    <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card">
    <div class="card-header bg-white fw-bold">Informasi Topup</div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <tr><td class="text-muted w-40">Invoice</td><td class="fw-bold">{{ $deposit->invoice }}</td></tr>
            <tr><td class="text-muted">Customer</td><td>{{ $deposit->user->name }} ({{ $deposit->user->email }})</td></tr>
            <tr><td class="text-muted">Reference (Tripay)</td><td>{{ $deposit->reference ?: '-' }}</td></tr>
            <tr><td class="text-muted">Nominal</td><td>{{ number_format($deposit->amount, 0, ',', '.') }}</td></tr>
            <tr><td class="text-muted">Fee Customer</td><td>{{ number_format($deposit->fee_customer, 0, ',', '.') }}</td></tr>
            <tr><td class="text-muted">Total Bayar</td><td class="fw-bold">{{ number_format($deposit->total_amount, 0, ',', '.') }}</td></tr>
            <tr><td class="text-muted">Metode</td><td>{{ $deposit->payment_name ?: $deposit->payment_method }}</td></tr>
            <tr><td class="text-muted">Kode Bayar</td><td>{{ $deposit->pay_code ?: '-' }}</td></tr>
            <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $deposit->statusBadge() }}">{{ $deposit->statusLabel() }}</span></td></tr>
            <tr><td class="text-muted">Waktu Dibuat</td><td>{{ $deposit->created_at->format('d/m/Y H:i:s') }}</td></tr>
            @if($deposit->expired_at)
            <tr><td class="text-muted">Berlaku Hingga</td><td>{{ $deposit->expired_at->format('d/m/Y H:i:s') }}</td></tr>
            @endif
            @if($deposit->paid_at)
            <tr><td class="text-muted">Waktu Dibayar</td><td>{{ $deposit->paid_at->format('d/m/Y H:i:s') }}</td></tr>
            @endif
        </table>
    </div>
</div>
@endsection
