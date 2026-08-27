<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Layanan PPOB Terpercaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; }
        .feature-icon { width: 56px; height: 56px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark hero">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-lightning-charge-fill"></i> {{ config('app.name') }}</a>
        <div class="ms-auto">
            @auth
                <a href="{{ route('customer.dashboard') }}" class="btn btn-warning btn-sm fw-bold">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-warning btn-sm fw-bold">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

<section class="hero py-5">
    <div class="container text-center py-5">
        <h1 class="display-5 fw-bold mb-3">Layanan Pembayaran Digital<br>Mudah, Cepat & Terpercaya</h1>
        <p class="lead mb-4">Pulsa, paket data, token listrik, tagihan, topup e-wallet & game dalam satu aplikasi.</p>
        <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold">Mulai Sekarang</a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-phone"></i></div>
                        <h5>Pulsa & Paket Data</h5>
                        <p class="text-muted mb-0">Semua operator Indonesia dengan harga bersaing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-lightning"></i></div>
                        <h5>Token PLN & Tagihan</h5>
                        <p class="text-muted mb-0">Token listrik, PLN, dan berbagai tagihan bulanan.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-warning-subtle text-warning mb-3"><i class="bi bi-wallet2"></i></div>
                        <h5>Topup E-Wallet & Game</h5>
                        <p class="text-muted mb-0">GoPay, OVO, DANA, ShopeePay, dan voucher game.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-light">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold">Topup Saldo Mudah</h4>
                <p class="text-muted">Isi saldo Anda melalui berbagai metode pembayaran: Virtual Account, QRIS, e-wallet, Indomaret & Alfamart.</p>
                <ul class="list-unstyled text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Proses transaksi realtime</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Riwayat transaksi lengkap</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Saldo aman & tercatat</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="fw-bold text-primary">24/7</h2>
                    <p class="text-muted mb-0">Layanan transaksi setiap saat</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="text-center text-muted py-4">
    <small>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</small>
</footer>
</body>
</html>
