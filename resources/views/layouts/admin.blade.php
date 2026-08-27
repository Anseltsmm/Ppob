<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f1f4f9; }

        /* ===== Sidebar (desktop: fixed left, mobile: slide overlay) ===== */
        .sidebar {
            background: #1e293b;
            min-height: 100vh;
            transition: transform .25s ease;
        }
        .sidebar .nav-link {
            color: #cbd5e1; border-radius: .5rem; margin-bottom: .15rem;
            padding: .55rem .75rem; font-size: .88rem;
            display: flex; align-items: center; gap: .6rem;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .nav-link.active { background: #2563eb; color: #fff; }
        .sidebar .brand { color: #fff; }
        .sidebar .nav-link i { font-size: 1.1rem; width: 20px; text-align: center; }

        /* Desktop: sidebar always visible */
        @media (min-width: 992px) {
            .sidebar { position: fixed; top: 0; left: 0; width: 220px; z-index: 1040; }
            .admin-main { margin-left: 220px; }
        }

        /* Mobile: sidebar hidden, slide out on toggle */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed; top: 0; left: 0; width: 260px;
                z-index: 1060;
                transform: translateX(-100%);
            }
            .sidebar.show { transform: translateX(0); }
            .admin-main { margin-left: 0; }
        }

        /* Overlay backdrop */
        .sidebar-overlay {
            position: fixed; inset: 0; z-index: 1055;
            background: rgba(15,23,42,.5);
            opacity: 0; visibility: hidden;
            transition: all .25s;
        }
        .sidebar-overlay.show { opacity: 1; visibility: visible; }

        /* Top bar (mobile only) */
        .admin-topbar {
            position: sticky; top: 0; z-index: 1045;
            background: #1e293b;
            padding: .6rem 1rem;
            display: flex; align-items: center; gap: .75rem;
        }

        .hamburger {
            background: none; border: none; color: #fff;
            font-size: 1.3rem; padding: .25rem;
            cursor: pointer; line-height: 1;
        }
        .hamburger:focus { outline: none; box-shadow: none; }

        .topbar-brand {
            color: #fff; font-weight: 700; font-size: 1rem;
            display: flex; align-items: center; gap: .4rem;
        }

        .card { border: none; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    </style>
    @stack('styles')
</head>
<body>

{{-- ===== SIDEBAR OVERLAY (mobile) ===== --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ===== SIDEBAR ===== --}}
<aside class="sidebar p-3" id="sidebar">
    <div class="brand fw-bold fs-5 mb-4 px-2 d-flex align-items-center justify-content-between">
        <span><i class="bi bi-lightning-charge-fill"></i> {{ config('app.name') }}</span>
        <button type="button" class="btn-close btn-close-white d-lg-none" onclick="closeSidebar()" style="font-size:.65rem;"></button>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                <i class="bi bi-receipt"></i> Transaksi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}" href="{{ route('admin.deposits.index') }}">
                <i class="bi bi-cash-stack"></i> Topup Saldo
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                <i class="bi bi-box-seam"></i> Produk
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">
                <i class="bi bi-bookmark-heart"></i> Brand
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                <i class="bi bi-people"></i> Customer
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        </li>
        <li class="nav-item mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,.1);">
            <a class="nav-link" href="{{ route('customer.dashboard') }}">
                <i class="bi bi-arrow-left-circle"></i> Lihat Situs
            </a>
        </li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</aside>

{{-- ===== MAIN CONTENT ===== --}}
<div class="admin-main">
    {{-- Topbar (mobile only) --}}
    <div class="admin-topbar d-lg-none">
        <button type="button" class="hamburger" onclick="openSidebar()" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <span class="topbar-brand">
            <i class="bi bi-lightning-charge-fill"></i> {{ config('app.name') }}
            <span class="badge bg-primary" style="font-size:.6rem;">Admin</span>
        </span>
    </div>

    <div class="p-3 p-lg-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('show');
    document.getElementById('sidebarOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
    document.body.style.overflow = '';
}
// Tutup sidebar dengan swipe kiri
(function () {
    let touchStartX = 0;
    const sidebar = document.getElementById('sidebar');
    sidebar.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
    sidebar.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (diff > 50) closeSidebar();
    }, { passive: true });
})();
</script>
@stack('scripts')
</body>
</html>
