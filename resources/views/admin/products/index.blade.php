@extends('layouts.admin')

@section('title', 'Produk')

@push('styles')
<style>
    /* ===== Header Hero ===== */
    .prod-hero {
        background: linear-gradient(135deg, #1e293b 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .prod-hero .deco { position: absolute; border-radius: 50%; pointer-events: none; }
    .prod-hero .d1 { width: 200px; height: 200px; background: rgba(255,255,255,.06); top: -80px; right: -30px; }
    .prod-hero .d2 { width: 110px; height: 110px; background: rgba(255,255,255,.05); bottom: -45px; left: 25%; }

    /* ===== Stat mini ===== */
    .mini-stat {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: .75rem;
        padding: .7rem 1rem;
        min-width: 110px;
    }
    .mini-stat .ms-value { font-weight: 700; font-size: 1.15rem; line-height: 1.1; }
    .mini-stat .ms-label { font-size: .7rem; opacity: .75; }

    /* ===== Filter bar ===== */
    .filter-bar {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .9rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* ===== Table card ===== */
    .prod-table-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .9rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .prod-table-card .table { margin-bottom: 0; }
    .prod-table-card .table thead th {
        font-size: .72rem; text-transform: uppercase; letter-spacing: .04em;
        color: #64748b; font-weight: 700; border-bottom: 1px solid #eef2f7;
        padding: .85rem 1.25rem;
    }
    .prod-table-card .table tbody td {
        padding: .8rem 1.25rem; border-color: #f1f5f9; vertical-align: middle;
    }
    .prod-table-card .table tbody tr:hover { background: #f8fafc; }
    .prod-table-card .table tbody tr:last-child td { border-bottom: none; }

    /* ===== Product identity ===== */
    .prod-id { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .prod-avatar {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.05rem;
        overflow: hidden;
    }
    .prod-avatar img { width: 100%; height: 100%; object-fit: contain; padding: 5px; }
    .prod-name { font-weight: 600; color: #1e293b; font-size: .88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .prod-code { font-size: .72rem; color: #94a3b8; font-family: ui-monospace, monospace; }

    /* ===== Badges & chips ===== */
    .chip-type {
        font-size: .7rem; font-weight: 600; padding: .22rem .55rem; border-radius: 999px;
    }
    .chip-prepay { background: #eff6ff; color: #2563eb; }
    .chip-opendenom { background: #f5f3ff; color: #7c3aed; }
    .chip-bill { background: #ecfeff; color: #0891b2; }

    .price-cell .p-main { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .price-cell .p-sub { font-size: .7rem; color: #94a3b8; }
    .profit-badge {
        font-size: .72rem; font-weight: 700; padding: .2rem .5rem; border-radius: 999px;
        display: inline-block;
    }
    .profit-pos { background: #f0fdf4; color: #16a34a; }
    .profit-neg { background: #fef2f2; color: #dc2626; }

    /* ===== Action buttons ===== */
    .action-group { display: flex; gap: .35rem; justify-content: flex-end; }
    .btn-action {
        width: 30px; height: 30px; padding: 0; display: inline-flex;
        align-items: center; justify-content: center; border-radius: 8px;
    }
    .btn-action i { font-size: .9rem; }

    /* ===== Select category badge color ===== */
    .cat-badge { font-size: .72rem; font-weight: 500; }

    /* ===== Pagination ===== */
    .pagination-wrap {
        padding: 1rem 1.25rem; border-top: 1px solid #eef2f7;
        overflow-x: auto; -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .pagination-wrap nav { display: flex; justify-content: center; min-width: max-content; margin: 0 auto; }
    .pagination-wrap .pagination { margin-bottom: 0; gap: .3rem; }
    .pagination .page-item { flex-shrink: 0; }
    .pagination .page-item .page-link {
        border: none; border-radius: .5rem !important;
        min-width: 34px; height: 34px; padding: 0 .55rem;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .8rem; font-weight: 600; color: #475569;
        background: #f1f5f9;
    }
    .pagination .page-item .page-link:hover { background: #e2e8f0; color: #1e293b; }
    .pagination .page-item.active .page-link {
        background: #2563eb; color: #fff; box-shadow: 0 2px 6px rgba(37,99,235,.3);
    }
    .pagination .page-item.disabled .page-link { opacity: .45; }
    .pagination .page-item .page-link i { font-size: .85rem; }
</style>
@endpush

@section('content')
{{-- ===== HEADER HERO ===== --}}
<div class="prod-hero">
    <span class="deco d1"></span>
    <span class="deco d2"></span>
    <div class="position-relative d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div style="font-size:.8rem; opacity:.75;"><i class="bi bi-box-seam"></i> Katalog Produk</div>
            <div class="fw-bold fs-4">Produk Digital</div>
            <div style="font-size:.8rem; opacity:.7; margin-top:2px;">
                Kelola produk dari daftar harga OkeConnect — prepaid, open denom, dan pascabayar.
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="mini-stat">
                <div class="ms-value">{{ $summary['total'] }}</div>
                <div class="ms-label">Total Produk</div>
            </div>
            <div class="mini-stat">
                <div class="ms-value">{{ $summary['active'] }}</div>
                <div class="ms-label">Aktif</div>
            </div>
            <div class="mini-stat">
                <div class="ms-value">{{ $summary['categories'] }}</div>
                <div class="ms-label">Kategori</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== FILTER BAR ===== --}}
<div class="filter-bar">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Kategori</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Cari</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama produk...">
            </div>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if(request('category') || request('q'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Reset</a>
            @endif
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
            <a href="{{ route('admin.products.import') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-cloud-download"></i> Import OkeConnect</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
        </div>
    </form>
</div>

{{-- ===== TABLE ===== --}}
<div class="prod-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Tipe</th>
                    <th class="text-end">Modal</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Profit</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $avatarColors = ['#eff6ff', '#fef2f2', '#f0fdf4', '#fffbeb', '#f5f3ff', '#ecfeff', '#fdf2f8'];
                    $avatarColor = $avatarColors[$product->id % count($avatarColors)];
                    $isBill = $product->inquiry_code !== null;
                    $modal = (float) $product->modal_price;
                    $profit = $product->type === 'opendenom'
                        ? ((float) $product->admin_fee - $modal)
                        : ($isBill ? 0 : ((float) $product->sell_price - $modal));
                    $brand = $brandMap[$product->operator] ?? null;
                @endphp
                <tr>
                    <td>
                        <div class="prod-id">
                            @if($brand && $brand->hasImage())
                                <div class="prod-avatar" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                    <img src="{{ $brand->iconUrl() }}" alt="">
                                </div>
                            @else
                                @php
                                    $icon = $product->type === 'opendenom' ? 'bi-arrow-left-right'
                                        : ($isBill ? 'bi-receipt' : 'bi-phone-fill');
                                @endphp
                                <div class="prod-avatar" style="background:{{ $avatarColor }}; color:#334155;">
                                    @if($brand && $brand->icon_font)
                                        <i class="bi bi-{{ $brand->icon_font }}" title="{{ $product->operator }}"></i>
                                    @else
                                        <i class="bi {{ $icon }}"></i>
                                    @endif
                                </div>
                            @endif
                            <div style="min-width:0;">
                                <div class="prod-name" style="max-width:220px;">{{ $product->name }}</div>
                                <div class="prod-code">{{ $product->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="cat-badge badge bg-light text-dark">{{ $product->category->name }}</span>
                    </td>
                    <td>
                        @if($isBill)
                            <span class="chip-type chip-bill">Pascabayar</span>
                        @elseif($product->type === 'opendenom')
                            <span class="chip-type chip-opendenom">Open Denom</span>
                        @else
                            <span class="chip-type chip-prepay">Prepaid</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="price-cell">
                            <div class="p-main">Rp {{ number_format($modal, 0, ',', '.') }}</div>
                            @if($isBill)
                                <div class="p-sub">margin admin</div>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="price-cell">
                            @if($product->type === 'opendenom')
                                <div class="p-main">Nominal + Rp {{ number_format($product->admin_fee, 0, ',', '.') }}</div>
                                <div class="p-sub">min {{ number_format($product->min_nominal, 0, ',', '.') }} • max {{ number_format($product->max_nominal, 0, ',', '.') }}</div>
                            @elseif($isBill)
                                <div class="p-main">Nominal + Rp {{ number_format($product->admin_fee, 0, ',', '.') }}</div>
                                <div class="p-sub">dihitung saat bayar</div>
                            @else
                                <div class="p-main">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        @if($isBill)
                            <span class="text-muted" style="font-size:.72rem;">—</span>
                        @else
                            <span class="profit-badge {{ $profit >= 0 ? 'profit-pos' : 'profit-neg' }}">
                                {{ $profit >= 0 ? '+' : '−' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill bg-{{ $product->status ? 'success' : 'secondary' }}">
                            ● {{ $product->status ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-light btn-action" title="Edit">
                                <i class="bi bi-pencil text-primary"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.toggle', $product) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-light btn-action" title="{{ $product->status ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="bi bi-power {{ $product->status ? 'text-warning' : 'text-success' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-light btn-action" title="Hapus">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        Belum ada produk yang cocok.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="pagination-wrap">{{ $products->links('vendor.pagination.bootstrap-5') }}</div>
    @endif
</div>
@endsection