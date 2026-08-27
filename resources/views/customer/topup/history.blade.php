@extends('layouts.app')

@section('title', 'Riwayat Topup')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Riwayat Topup Saldo</h4>
    <a href="{{ route('customer.topup.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Topup Baru</a>
</div>

<div class="card">
    @if($deposits->count())
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Nominal</th>
                    <th>Biaya</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th class="text-end">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deposits as $deposit)
                <tr>
                    <td><a href="{{ route('customer.topup.pay', $deposit) }}">{{ $deposit->invoice }}</a></td>
                    <td>{{ number_format($deposit->amount, 0, ',', '.') }}</td>
                    <td class="text-muted">{{ number_format($deposit->fee_customer, 0, ',', '.') }}</td>
                    <td class="fw-bold">{{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                    <td class="small">{{ $deposit->payment_name ?: $deposit->payment_method }}</td>
                    <td><span class="badge bg-{{ $deposit->statusBadge() }}">{{ $deposit->statusLabel() }}</span></td>
                    <td class="text-end small text-muted">{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $deposits->links() }}</div>
    @else
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-wallet2 fs-1 d-block mb-2"></i>
        Belum ada topup. <a href="{{ route('customer.topup.index') }}">Topup sekarang</a>.
    </div>
    @endif
</div>
@endsection
