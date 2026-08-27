@extends('layouts.app')

@section('title', 'Pembayaran '.$deposit->invoice)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('customer.topup.index') }}">Topup</a></li>
        <li class="breadcrumb-item active">{{ $deposit->invoice }}</li>
    </ol>
</nav>

@if($deposit->status === 'PAID')
    <div class="alert alert-success text-center py-4">
        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
        <h5 class="fw-bold">Pembayaran Berhasil!</h5>
        <p class="mb-0">Saldo sebesar <strong>{{ number_format($deposit->amount, 0, ',', '.') }}</strong> telah ditambahkan ke akun Anda.</p>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-success mt-3">Ke Dashboard</a>
    </div>
@elseif($deposit->status === 'EXPIRED' || $deposit->status === 'FAILED')
    <div class="alert alert-danger text-center py-4">
        <i class="bi bi-x-circle-fill fs-1 d-block mb-2"></i>
        <h5 class="fw-bold">Pembayaran {{ $deposit->statusLabel() }}</h5>
        <p class="mb-0">Silakan buat topup baru.</p>
        <a href="{{ route('customer.topup.index') }}" class="btn btn-danger mt-3">Topup Ulang</a>
    </div>
@else
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-bold">Rincian Pembayaran</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Invoice</td>
                        <td class="fw-bold">{{ $deposit->invoice }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode</td>
                        <td>{{ $deposit->payment_name ?: $deposit->payment_method }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nominal</td>
                        <td>{{ number_format($deposit->amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($deposit->fee_customer > 0)
                    <tr>
                        <td class="text-muted">Biaya Admin</td>
                        <td>{{ number_format($deposit->fee_customer, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="table-light">
                        <td class="fw-bold">Total Bayar</td>
                        <td class="fw-bold text-primary fs-5">{{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($deposit->expired_at)
                    <tr>
                        <td class="text-muted">Berlaku Hingga</td>
                        <td class="{{ $deposit->expired_at->isPast() ? 'text-danger fw-bold' : '' }}">
                            {{ $deposit->expired_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @if($deposit->pay_code)
        <div class="card mb-3">
            <div class="card-body p-4 text-center">
                <div class="text-muted small mb-1">Kode Pembayaran Anda</div>
                <div class="display-6 fw-bold text-primary mb-3">{{ $deposit->pay_code }}</div>
                <button class="btn btn-outline-primary btn-sm" onclick="copyPayCode()"><i class="bi bi-clipboard"></i> Salin</button>
            </div>
        </div>
        @endif

        @if($deposit->pay_url)
        <div class="card mb-3">
            <div class="card-body p-4 text-center">
                <p class="text-muted mb-2">Selesaikan pembayaran melalui halaman berikut:</p>
                <a href="{{ $deposit->pay_url }}" target="_blank" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-box-arrow-up-right"></i> Bayar Sekarang
                </a>
            </div>
        </div>
        @endif

        @if(!empty($instructions))
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold">Instruksi Pembayaran</div>
            <div class="card-body">
                @foreach($instructions as $instruction)
                    <h6 class="fw-bold mt-3 mb-2">{{ $instruction['title'] ?? '' }}</h6>
                    <ol class="small text-muted ps-3">
                        @foreach(($instruction['steps'] ?? []) as $step)
                            <li class="mb-1">{!! str_replace('{{pay_code}}', $deposit->pay_code ?? '', $step) !!}</li>
                        @endforeach
                    </ol>
                @endforeach
            </div>
        </div>
        @endif

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Sudah melakukan pembayaran? Klik tombol di bawah untuk memverifikasi status secara manual.
            <div class="mt-2">
                <a href="{{ route('customer.topup.check', $deposit) }}" class="btn btn-info btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Cek Status Pembayaran
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function copyPayCode() {
        const code = @json($deposit->pay_code ?? '');
        if (!code) return;
        navigator.clipboard.writeText(code).then(() => {
            alert('Kode pembayaran disalin!');
        });
    }
</script>
@endpush
@endsection
