@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ===== Kartu Saldo ===== */
    .saldo-overlap {
        margin-top: -85px; /* kartu tetap tinggi, perbatasan biru-putih memotong kartu */
    }
    .saldo-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #6366f1 100%);
        border-radius: 1.25rem;
        box-shadow: 0 12px 32px rgba(37, 99, 235, .28);
        position: relative;
        overflow: hidden;
    }
    .saldo-card .deco {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .saldo-card .deco-1 {
        width: 220px; height: 220px;
        background: rgba(255,255,255,.08);
        top: -80px; right: -60px;
    }
    .saldo-card .deco-2 {
        width: 140px; height: 140px;
        background: rgba(255,255,255,.06);
        bottom: -60px; left: 20%;
    }
    .saldo-card .deco-3 {
        width: 70px; height: 70px;
        background: rgba(255,255,255,.1);
        bottom: -25px; right: 22%;
    }
    .saldo-card .card-chip {
        width: 44px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        position: relative;
        display: inline-block;
    }
    .saldo-card .card-chip::after {
        content: '';
        position: absolute;
        inset: 6px;
        border: 1.5px dashed rgba(0,0,0,.25);
        border-radius: 4px;
    }
    .btn-topup {
        box-shadow: 0 4px 12px rgba(245, 158, 11, .35);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-topup:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(245, 158, 11, .45);
    }

    /* ===== Kategori Layanan ===== */
    .category-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .6rem;
        padding: 1rem .5rem;
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 1.1rem;
        text-decoration: none;
        color: inherit;
        height: 100%;
        transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    .category-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(0,0,0,.1);
        border-color: #c7d7fe;
    }
    .category-item .cat-tile {
        width: 58px; height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #fff;
        box-shadow: 0 6px 14px rgba(0,0,0,.14);
        transition: transform .15s;
    }
    .category-item:hover .cat-tile { transform: scale(1.08); }
    .cat-grad-0 { background: linear-gradient(135deg, #60a5fa, #1d4ed8); }
    .cat-grad-1 { background: linear-gradient(135deg, #4ade80, #15803d); }
    .cat-grad-2 { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .cat-grad-3 { background: linear-gradient(135deg, #f472b6, #be185d); }
    .cat-grad-4 { background: linear-gradient(135deg, #a78bfa, #6d28d9); }
    .cat-grad-5 { background: linear-gradient(135deg, #22d3ee, #0e7490); }
</style>
@endpush

@section('content')
{{-- ==================== KARTU SALDO (kompak, melayang di tengah) ==================== --}}
<div class="saldo-overlap saldo-card text-white p-3 p-md-4 mb-4">
    <span class="deco deco-1"></span>
    <span class="deco deco-2"></span>
    <span class="deco deco-3"></span>

    <div class="position-relative">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="card-chip"></span>
                <span class="fw-bold small">{{ config('app.name') }}</span>
            </div>
            <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-1" style="font-size:.7rem;">
                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
            </span>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="text-white-50" style="font-size:.72rem;">Saldo Anda</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fs-3 fw-bold lh-1" id="saldoText">Rp {{ number_format(auth()->user()->saldo, 0, ',', '.') }}</span>
                    <button type="button" class="btn btn-link text-white p-0 border-0" style="font-size:.85rem;" onclick="toggleSaldo()" title="Sembunyikan/Tampilkan saldo">
                        <i class="bi bi-eye" id="saldoEye"></i>
                    </button>
                </div>
                <div class="text-white-50 mt-1" style="font-size:.72rem;">
                    <i class="bi bi-clock"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </div>
            </div>
            <a href="{{ route('customer.topup.index') }}" class="btn btn-warning fw-bold btn-topup px-4 py-2">
                <i class="bi bi-plus-lg"></i> Topup Saldo
            </a>
        </div>
    </div>
</div>

{{-- ==================== MENU KATEGORI LAYANAN ==================== --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i> Kategori Layanan</h5>
    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" id="toggleCategories">
        <i class="bi bi-chevron-expand"></i> <span id="toggleCategoriesText">Tampilkan Semua</span>
    </button>
</div>

@if($categories->count())
    <div class="row g-3">
        @foreach($categories as $index => $category)
            @php($catLabel = match($category->name) {
                'Pulsa' => 'Pulsa&Data',
                'Paket Data' => 'Voucher Data',
                'Pulsa Transfer' => 'Pulsa Transfer',
                default => $category->name,
            })
            <div class="col-4 col-md-2 category-col {{ $index >= 3 ? 'd-none' : '' }}" data-index="{{ $index }}">
                @if(strtolower($category->name) === 'pulsa')
                    <a href="{{ route('customer.pulsa.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'paket data')
                    <a href="{{ route('customer.voucher.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'token pln')
                    <a href="{{ route('customer.token-pln.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'e-wallet')
                    <a href="{{ route('customer.ewallet.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'game')
                    <a href="{{ route('customer.game.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'pascabayar')
                    <a href="{{ route('customer.tagihan.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'receipt' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'cetak voucher')
                    <a href="{{ route('customer.cetak-voucher.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'printer' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @elseif(strtolower($category->name) === 'pulsa transfer')
                    <a href="{{ route('customer.pulsa-transfer.index') }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'arrow-left-right' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @else
                    <a href="{{ route('shop.category', $category) }}" class="category-item">
                        <span class="cat-tile cat-grad-{{ $index % 6 }}">
                            <i class="bi bi-{{ $category->icon ?: 'box' }}"></i>
                        </span>
                        <span class="fw-semibold small text-center">{{ $catLabel }}</span>
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-grid fs-1 d-block mb-2"></i>
            Belum ada kategori layanan tersedia.
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function toggleSaldo() {
        const el = document.getElementById('saldoText');
        const eye = document.getElementById('saldoEye');
        if (el.dataset.hidden) {
            el.textContent = '{{ 'Rp ' . number_format(auth()->user()->saldo, 0, ',', '.') }}';
            delete el.dataset.hidden;
            eye.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            el.dataset.hidden = '1';
            el.textContent = 'Rp •••••••';
            eye.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }

    // Toggle Kategori Layanan: Tampilkan Semua / Tampilkan Lebih Sedikit
    (function () {
        const btn = document.getElementById('toggleCategories');
        if (!btn) return;

        const text = document.getElementById('toggleCategoriesText');
        const icon = btn.querySelector('i');
        const cols = Array.from(document.querySelectorAll('.category-col'));
        const LIMIT = 3;
        let expanded = false;

        // Sembunyikan tombol jika semua kategori sudah tampil semua
        if (cols.length <= LIMIT) {
            btn.style.display = 'none';
            return;
        }

        btn.addEventListener('click', function () {
            expanded = !expanded;
            cols.forEach((col, i) => {
                col.classList.toggle('d-none', !expanded && i >= LIMIT);
            });
            text.textContent = expanded ? 'Tampilkan Lebih Sedikit' : 'Tampilkan Semua';
            icon.className = expanded ? 'bi bi-chevron-contract' : 'bi bi-chevron-expand';
        });
    })();
</script>
@endpush
