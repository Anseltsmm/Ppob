@extends('layouts.app')

@section('title', 'QRIS')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card text-center">
            <div class="card-body p-4 p-md-5">
                <div class="icon-circle bg-success-subtle text-success mx-auto mb-3" style="width:64px;height:64px;font-size:1.8rem;">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h4 class="fw-bold mb-1">Pembayaran QRIS</h4>
                <p class="text-muted mb-4">Scan kode QR di bawah ini untuk melakukan pembayaran ke {{ config('app.name') }}.</p>

                @if($payload)
                    <div class="bg-light rounded-4 p-4 mb-3 d-inline-block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=8&data={{ urlencode($payload) }}"
                             alt="QRIS {{ config('app.name') }}" class="img-fluid rounded-3"
                             style="width:260px;height:260px;">
                    </div>
                    <div class="small text-muted mb-4">
                        <i class="bi bi-info-circle"></i>
                        Buka aplikasi pembayaran (GoPay, OVO, DANA, ShopeePay, m-Banking) → pilih menu Scan/QRIS → pindai kode di atas.
                    </div>
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Metode ini menerima pembayaran dari semua aplikasi QRIS (termasuk e-wallet & m-banking).</span>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Kode QRIS belum tersedia saat ini. Silakan hubungi admin atau gunakan metode topup lainnya.
                    </div>
                    <a href="{{ route('customer.topup.index') }}" class="btn btn-primary fw-bold">
                        <i class="bi bi-plus-lg"></i> Topup Saldo
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
