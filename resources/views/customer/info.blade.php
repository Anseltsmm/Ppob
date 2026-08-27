@extends('layouts.app')

@section('title', 'Info')

@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary"></i> Info Layanan</h4>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-shop text-primary"></i> Tentang {{ config('app.name') }}</div>
            <div class="card-body">
                <p class="mb-3">
                    {{ config('app.name') }} adalah layanan pembayaran digital (PPOB) yang melayani berbagai kebutuhan
                    transaksi secara <strong>cepat, aman, dan realtime</strong>.
                </p>
                <h6 class="fw-bold mb-2">Layanan yang Tersedia</h6>
                <div class="row g-2 mb-3">
                    @foreach([
                        ['bi-phone', 'Pulsa & Paket Data'],
                        ['bi-lightning-charge', 'Token PLN & Tagihan'],
                        ['bi-wallet2', 'Topup E-Wallet & Game'],
                        ['bi-receipt', 'Pembayaran Pascabayar'],
                    ] as [$icon, $label])
                        <div class="col-6">
                            <div class="border rounded-3 p-3 d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }} text-primary fs-5"></i>
                                <span class="small fw-semibold">{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <h6 class="fw-bold mb-2">Cara Transaksi</h6>
                <ol class="small text-muted ps-3 mb-0">
                    <li class="mb-1">Daftar / login akun Anda.</li>
                    <li class="mb-1">Topup saldo melalui berbagai metode pembayaran (VA, QRIS, e-wallet, Indomaret/Alfamart).</li>
                    <li class="mb-1">Pilih produk yang diinginkan dan masukkan nomor tujuan.</li>
                    <li class="mb-1">Transaksi diproses realtime, riwayat tercatat otomatis.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold"><i class="bi bi-headset text-primary"></i> Hubungi Kami</div>
            <div class="card-body">
                @php
                    $contacts = [
                        ['bi-telephone', 'Telepon', $settings['app_info_phone'] ?? '-'],
                        ['bi-whatsapp', 'WhatsApp', $settings['app_info_whatsapp'] ?? '-'],
                        ['bi-envelope', 'Email', $settings['app_info_email'] ?? '-'],
                        ['bi-geo-alt', 'Alamat', $settings['app_info_address'] ?? '-'],
                    ];
                @endphp
                @foreach($contacts as [$icon, $label, $value])
                    <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="icon-circle bg-primary-subtle text-primary"><i class="bi {{ $icon }}"></i></div>
                        <div class="min-w-0">
                            <div class="small text-muted">{{ $label }}</div>
                            <div class="fw-semibold small text-truncate">{{ $value }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white fw-bold"><i class="bi bi-clock text-primary"></i> Jam Operasional</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Layanan Transaksi</div>
                        <div class="text-muted small">{{ $settings['app_info_hours'] ?: 'Setiap hari, 24 jam (realtime)' }}</div>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle"></i> Online</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
