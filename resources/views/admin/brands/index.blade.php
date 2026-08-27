@extends('layouts.admin')

@section('title', 'Brand Produk')

@push('styles')
<style>
    .brand-hero {
        background: linear-gradient(135deg, #1e293b 0%, #4c1d95 55%, #7c3aed 100%);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .brand-hero .deco { position: absolute; border-radius: 50%; pointer-events: none; }
    .brand-hero .d1 { width: 200px; height: 200px; background: rgba(255,255,255,.06); top: -80px; right: -30px; }
    .brand-hero .d2 { width: 110px; height: 110px; background: rgba(255,255,255,.05); bottom: -45px; left: 25%; }

    .mini-stat {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: .75rem;
        padding: .7rem 1rem;
        min-width: 110px;
    }
    .mini-stat .ms-value { font-weight: 700; font-size: 1.15rem; line-height: 1.1; }
    .mini-stat .ms-label { font-size: .7rem; opacity: .75; }

    .brand-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    @media (min-width: 768px) { .brand-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 1200px) { .brand-grid { grid-template-columns: repeat(4, 1fr); } }

    .brand-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .9rem;
        padding: 1.1rem;
        display: flex; flex-direction: column; gap: .8rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        transition: box-shadow .15s, transform .15s;
        position: relative;
    }
    .brand-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.08); transform: translateY(-2px); }

    .brand-top { display: flex; align-items: center; gap: .8rem; }
    .brand-logo {
        width: 52px; height: 52px; flex-shrink: 0;
        border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: #fff;
        overflow: hidden;
    }
    .brand-logo img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }
    .brand-name { font-weight: 700; font-size: .98rem; color: #1e293b; }
    .brand-code { font-size: .72rem; color: #94a3b8; font-family: ui-monospace, monospace; }

    .brand-actions { display: flex; gap: .4rem; }
    .btn-action {
        width: 30px; height: 30px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }
    .btn-action i { font-size: .9rem; }

    .add-card {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: .9rem;
        padding: 1.1rem;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .5rem; cursor: pointer; transition: all .15s;
        min-height: 150px; text-decoration: none; color: #64748b;
    }
    .add-card:hover { border-color: #7c3aed; background: #f5f3ff; color: #7c3aed; }
    .add-card .add-icon {
        width: 46px; height: 46px; border-radius: 50%;
        background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }
    .add-card:hover .add-icon { background: #ede9fe; }

    .modal-content { border-radius: 1rem; border: none; }
    .modal-header { border-bottom: 1px solid #f1f5f9; }
    .modal-footer { border-top: 1px solid #f1f5f9; }

    /* Icon picker */
    .icon-grid {
        display: grid; grid-template-columns: repeat(6, 1fr); gap: .5rem;
        max-height: 220px; overflow-y: auto; padding: .25rem;
    }
    .icon-opt {
        border: 1.5px solid #e2e8f0; border-radius: .55rem;
        width: 100%; aspect-ratio: 1;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #475569; background: #fff; cursor: pointer;
        transition: all .12s;
    }
    .icon-opt:hover { border-color: #a78bfa; color: #7c3aed; background: #faf5ff; }
    .icon-opt.selected { border-color: #7c3aed; color: #fff; background: #7c3aed; }

    /* Preview */
    .logo-preview {
        width: 56px; height: 56px; border-radius: 14px; overflow: hidden;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.6rem; color: #fff; flex-shrink: 0;
    }
    .logo-preview img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }

</style>
@endpush

@section('content')
{{-- ===== HEADER HERO ===== --}}
<div class="brand-hero">
    <span class="deco d1"></span>
    <span class="deco d2"></span>
    <div class="position-relative d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div style="font-size:.8rem; opacity:.75;"><i class="bi bi-tag"></i> Brand &amp; Operator</div>
            <div class="fw-bold fs-4">Brand Produk</div>
            <div style="font-size:.8rem; opacity:.7; margin-top:2px;">
                Kelola icon logo untuk tiap brand produk — ikon font atau upload gambar.
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="mini-stat">
                <div class="ms-value">{{ $brands->count() }}</div>
                <div class="ms-label">Total Brand</div>
            </div>
            <button type="button" class="btn btn-light fw-semibold align-self-center" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg"></i> Tambah Brand
            </button>
        </div>
    </div>
</div>

{{-- ===== GRID BRAND ===== --}}
<div class="brand-grid">
    @forelse($brands as $brand)
    <div class="brand-card">
        <div class="brand-top">
            @if($brand->hasImage())
                <div class="brand-logo" style="background:#f1f5f9; border:1px solid #e2e8f0;">
                    <img src="{{ $brand->iconUrl() }}" alt="{{ $brand->name }}">
                </div>
            @else
                <div class="brand-logo" style="background:{{ $brand->color ?? 'linear-gradient(135deg,#a78bfa,#7c3aed)' }}">
                    <i class="{{ $brand->iconClasses() }}"></i>
                </div>
            @endif
            <div style="min-width:0;">
                <div class="brand-name">{{ $brand->name }}</div>
                <div class="brand-code">{{ $brand->color ?? 'no color' }}</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-{{ $brand->status ? 'success' : 'secondary' }}">● {{ $brand->status ? 'Aktif' : 'Nonaktif' }}</span>
            <span class="badge bg-light text-dark" style="font-weight:500;">{{ $brand->products_count }} produk</span>
        </div>

        <div class="brand-actions">
            <button type="button" class="btn btn-light btn-action" data-bs-toggle="modal" data-bs-target="#editModal{{ $brand->id }}" title="Edit">
                <i class="bi bi-pencil text-primary"></i>
            </button>
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="d-inline">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $brand->name }}">
                <input type="hidden" name="icon_font" value="{{ $brand->icon_font }}">
                <input type="hidden" name="color" value="{{ $brand->color }}">
                <input type="hidden" name="status" value="{{ $brand->status ? 0 : 1 }}">
                <button class="btn btn-light btn-action" title="{{ $brand->status ? 'Nonaktifkan' : 'Aktifkan' }}">
                    <i class="bi bi-power {{ $brand->status ? 'text-warning' : 'text-success' }}"></i>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="d-inline" onsubmit="return confirm('Hapus brand &quot;{{ $brand->name }}&quot;?')">
                @csrf @method('DELETE')
                <button class="btn btn-light btn-action" title="Hapus">
                    <i class="bi bi-trash text-danger"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal{{ $brand->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Brand</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="logo-preview" id="editPreview{{ $brand->id }}" style="background:{{ $brand->color ?? '#7c3aed' }}">
                                @if($brand->hasImage())
                                    <img src="{{ $brand->iconUrl() }}" alt="">
                                @else
                                    <i class="{{ $brand->iconClasses() }}"></i>
                                @endif
                            </div>
                            <div class="small text-muted">Pratinjau icon</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Brand</label>
                            <input type="text" name="name" class="form-control" value="{{ $brand->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Warna Aksen</label>
                            <input type="color" name="color" class="form-control form-control-color" value="{{ $brand->color ?? '#7c3aed' }}" oninput="document.getElementById('editPreview{{ $brand->id }}').style.background=this.value">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Icon (font)</label>
                            <input type="text" name="icon_font" class="form-control" id="editIconInput{{ $brand->id }}" value="{{ $brand->icon_font }}" placeholder="contoh: sim, wifi">
                            <div class="form-text small">Isi nama Bootstrap icon, atau pilih dari grid di bawah. Kosongkan jika pakai gambar.</div>
                            @include('admin.brands._icon_picker', ['inputId' => 'editIconInput'.$brand->id, 'pickerId' => 'editPicker'.$brand->id, 'selected' => $brand->icon_font])
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Upload Gambar Logo</label>
                            <input type="file" name="icon_image" class="form-control" accept="image/*">
                            @if($brand->hasImage())
                                <div class="form-text">Gambar aktif: <a href="{{ $brand->iconUrl() }}" target="_blank">lihat</a> — upload baru untuk mengganti.</div>
                            @endif
                        </div>
                        <input type="hidden" name="status" value="{{ $brand->status ? 1 : 0 }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold"><i class="bi bi-check-lg"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted" style="grid-column: 1 / -1;">
        <i class="bi bi-tags fs-1 d-block mb-2"></i>
        Belum ada brand. Tambahkan brand produk Anda.
    </div>
    @endforelse

    {{-- Add Card --}}
    <div class="add-card" data-bs-toggle="modal" data-bs-target="#addModal">
        <div class="add-icon"><i class="bi bi-plus-lg"></i></div>
        <span class="fw-semibold small">Tambah Brand</span>
    </div>
</div>

{{-- ===== ADD MODAL ===== --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Brand</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Brand</label>
                        <input type="text" name="name" class="form-control" required placeholder="contoh: Telkomsel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Warna Aksen</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" class="form-control form-control-color" value="#7c3aed" oninput="this.parentElement.querySelector('span').textContent=this.value">
                            <span class="small text-muted" style="min-width:70px;">#7c3aed</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Icon (font)</label>
                        <input type="text" name="icon_font" class="form-control" id="addIconInput" placeholder="contoh: sim, wifi">
                        <div class="form-text small">Isi nama Bootstrap icon, atau pilih dari grid di bawah. Kosongkan jika pakai gambar.</div>
                        @include('admin.brands._icon_picker', ['inputId' => 'addIconInput', 'pickerId' => 'addPicker', 'selected' => ''])
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Upload Gambar Logo</label>
                        <input type="file" name="icon_image" class="form-control" accept="image/*">
                        <div class="form-text small">Opsional — pakai gambar logo (PNG/SVG) jika punya.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold"><i class="bi bi-plus-lg"></i> Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection