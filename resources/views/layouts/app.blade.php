<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPOB') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            padding-bottom: 72px; /* ruang untuk bottom navigation */
        }
        .navbar-ppob {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            box-shadow: 0 2px 12px rgba(30, 58, 138, .25);
        }
        /* ===== Background: biru di atas, putih di bawah ===== */
        .page-hero {
            height: 140px;
            background: linear-gradient(180deg, #2563eb 0%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }
        .page-hero-tall { height: 130px; }
        .page-hero::before,
        .page-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }
        .page-hero::before {
            width: 260px; height: 260px;
            top: -130px; right: -70px;
        }
        .page-hero::after {
            width: 180px; height: 180px;
            bottom: -95px; left: 12%;
        }
        .saldo-badge {
            background: rgba(255,255,255,.15);
            border-radius: .5rem;
            padding: .35rem .8rem;
            color: #fff;
        }
        .card { border: none; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .product-card { transition: transform .15s, box-shadow .15s; cursor: pointer; text-decoration: none; color: inherit; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.12); }
        .icon-circle { width: 46px; height: 46px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        /* ===== Bottom Navigation ala aplikasi PPOB ===== */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -4px 16px rgba(0,0,0,.07);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: .4rem .25rem calc(.4rem + env(safe-area-inset-bottom));
            z-index: 1030;
        }
        .bn-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .2rem;
            padding: .3rem .6rem;
            min-width: 56px;
            color: #94a3b8;
            text-decoration: none;
            font-size: .68rem;
            font-weight: 600;
            border-radius: .6rem;
            transition: color .15s, background .15s;
        }
        .bn-item i { font-size: 1.3rem; line-height: 1; }
        .bn-item:hover { color: #2563eb; }
        .bn-item.active { color: #2563eb; }
        .bn-item.active i {
            background: #dbeafe;
            color: #1d4ed8;
            padding: .35rem .55rem;
            border-radius: .75rem;
        }
    </style>
    @stack('styles')
</head>
<body>
{{-- ==================== NAVBAR ATAS (logo, saldo, topup) ==================== --}}
<nav class="navbar navbar-dark navbar-ppob sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="bi bi-lightning-charge-fill"></i> {{ config('app.name') }}
        </a>
        @auth
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="saldo-badge d-none d-sm-inline">
                    <i class="bi bi-cash-coin"></i> <strong>{{ number_format(auth()->user()->saldo, 0, ',', '.') }}</strong>
                </span>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('customer.profile') }}"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('customer.deposits.index') }}"><i class="bi bi-wallet2"></i> Riwayat Topup</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-gear"></i> Admin Panel</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-warning btn-sm fw-bold">Daftar</a>
            </div>
        @endauth
    </div>
</nav>

{{-- ==================== BACKGROUND BIRU (di bawah header) ==================== --}}
@php
    $isDashboard = request()->routeIs('customer.dashboard');
    $noHero = request()->routeIs(['customer.pulsa.*', 'customer.voucher.*', 'customer.token-pln.*', 'customer.ewallet.*', 'customer.game.*', 'customer.tagihan.*', 'customer.cetak-voucher.*', 'customer.pulsa-transfer.*', 'customer.orders.*', 'customer.profile*']);
@endphp
@if(! $noHero)
<div class="page-hero {{ $isDashboard ? 'page-hero-tall' : '' }}"></div>
@endif

{{-- ==================== KONTEN ==================== --}}
<div class="container pb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

{{-- ==================== BOTTOM NAVIGATION ==================== --}}
@auth
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}" class="bn-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('customer.orders.index') }}" class="bn-item {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i>
        <span>Histori</span>
    </a>
    <a href="{{ route('customer.qris') }}" class="bn-item {{ request()->routeIs('customer.qris') ? 'active' : '' }}">
        <i class="bi bi-qr-code-scan"></i>
        <span>QRIS</span>
    </a>
    <a href="{{ route('customer.info') }}" class="bn-item {{ request()->routeIs('customer.info') ? 'active' : '' }}">
        <i class="bi bi-info-circle"></i>
        <span>Info</span>
    </a>
    <a href="{{ route('customer.profile') }}" class="bn-item {{ request()->routeIs('customer.profile*') ? 'active' : '' }}">
        <i class="bi bi-person"></i>
        <span>Profile</span>
    </a>
</nav>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
