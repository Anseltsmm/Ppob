@extends('layouts.admin')

@section('title', 'Kategori')

@push('styles')
<style>
    .cat-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        gap: .75rem;
        transition: box-shadow .15s, transform .15s;
        position: relative;
    }
    .cat-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.07); transform: translateY(-2px); }
    .cat-card .cat-top {
        display: flex; align-items: center; gap: .75rem;
    }
    .cat-card .cat-icon {
        width: 52px; height: 52px; flex-shrink: 0;
        border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: #fff;
    }
    .cat-card .cat-info { flex: 1; min-width: 0; }
    .cat-card .cat-name {
        font-weight: 700; font-size: 1rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cat-card .cat-desc {
        font-size: .75rem; color: #94a3b8;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cat-card .cat-badge {
        font-size: .65rem; font-weight: 600;
        padding: .15rem .5rem; border-radius: 999px;
    }
    .cat-card .cat-badge.active { background: #dcfce7; color: #15803d; }
    .cat-card .cat-badge.inactive { background: #fee2e2; color: #dc2626; }

    .cat-card .cat-stats {
        display: flex; gap: 1rem;
        padding: .5rem 0;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
    }
    .cat-card .cat-stat {
        font-size: .75rem; color: #64748b;
        display: flex; align-items: center; gap: .3rem;
    }
    .cat-card .cat-stat strong {
        font-size: .95rem; color: #1e293b;
    }

    .cat-card .cat-actions {
        display: flex; gap: .5rem; flex-wrap: wrap;
    }
    .cat-card .cat-actions .btn {
        flex: 1; font-size: .75rem; padding: .4rem .5rem;
        border-radius: .5rem;
    }

    /* Add category card */
    .add-card {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: .85rem;
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        cursor: pointer;
        transition: all .15s;
        min-height: 200px;
        text-decoration: none; color: #64748b;
    }
    .add-card:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #2563eb;
    }
    .add-card .add-icon {
        width: 48px; height: 48px;
        border-radius: 50%;
        background: #e2e8f0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }
    .add-card:hover .add-icon { background: #dbeafe; }

    /* Modal */
    .modal-content { border-radius: 1rem; border: none; }
    .modal-header { border-bottom: 1px solid #f1f5f9; }
    .modal-footer { border-top: 1px solid #f1f5f9; }

    /* Icon gradient */
    .cat-grad-0 { background: linear-gradient(135deg, #60a5fa, #1d4ed8); }
    .cat-grad-1 { background: linear-gradient(135deg, #4ade80, #15803d); }
    .cat-grad-2 { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .cat-grad-3 { background: linear-gradient(135deg, #f472b6, #be185d); }
    .cat-grad-4 { background: linear-gradient(135deg, #a78bfa, #6d28d9); }
    .cat-grad-5 { background: linear-gradient(135deg, #22d3ee, #0e7490); }
    .cat-grad-6 { background: linear-gradient(135deg, #fb923c, #c2410c); }
    .cat-grad-7 { background: linear-gradient(135deg, #94a3b8, #475569); }

    .form-control:focus, .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-tags text-primary me-1"></i> Kategori</h5>
    <button type="button" class="btn btn-primary btn-sm rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i> Tambah
    </button>
</div>

<div class="row g-3">
    @foreach($categories as $index => $category)
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="cat-card h-100">
            <div class="cat-top">
                <div class="cat-icon cat-grad-{{ $index % 8 }}">
                    <i class="bi bi-{{ $category->icon ?: 'tag' }}"></i>
                </div>
                <div class="cat-info">
                    <div class="cat-name">{{ $category->name }}</div>
                    @if($category->description)
                        <div class="cat-desc">{{ $category->description }}</div>
                    @endif
                </div>
                @if($category->status)
                    <span class="cat-badge active">Aktif</span>
                @else
                    <span class="cat-badge inactive">Nonaktif</span>
                @endif
            </div>

            <div class="cat-stats">
                <div class="cat-stat">
                    <i class="bi bi-box-seam"></i>
                    <strong>{{ $category->products_count }}</strong> produk
                </div>
                <div class="cat-stat">
                    <i class="bi bi-sort-numeric-up"></i>
                    Urutan <strong>{{ $category->sort }}</strong>
                </div>
            </div>

            <div class="cat-actions">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $category->name }}">
                    <input type="hidden" name="icon" value="{{ $category->icon }}">
                    <input type="hidden" name="description" value="{{ $category->description }}">
                    <input type="hidden" name="sort" value="{{ $category->sort }}">
                    <input type="hidden" name="status" value="{{ $category->status ? 0 : 1 }}">
                    <button class="btn btn-sm {{ $category->status ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                        <i class="bi bi-{{ $category->status ? 'check-circle' : 'x-circle' }}"></i>
                        {{ $category->status ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $category->name }}">
                    <input type="hidden" name="icon" value="{{ $category->icon }}">
                    <input type="hidden" name="description" value="{{ $category->description }}">
                    <input type="hidden" name="status" value="{{ $category->status ? 1 : 0 }}">
                    <div class="input-group input-group-sm" style="width:90px;">
                        <span class="input-group-text bg-white" style="font-size:.7rem;">#</span>
                        <input type="number" name="sort" value="{{ $category->sort }}" class="form-control form-control-sm" style="font-size:.75rem;" onchange="this.form.submit()">
                    </div>
                </form>

                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}">
                    <i class="bi bi-pencil"></i>
                </button>

                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Hapus kategori &quot;{{ $category->name }}&quot;? Produk di dalamnya ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1"></i> Edit Kategori</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Icon (font)</label>
                            <input type="text" name="icon" class="form-control" id="catEditIconInput{{ $category->id }}" value="{{ $category->icon }}" placeholder="contoh: phone, lightning, joystick">
                            <div class="form-text">Nama icon dari Bootstrap Icons (tanpa prefix <code>bi-</code>), atau pilih dari grid di bawah. Kosongkan jika pakai gambar.</div>
                            @include('admin.brands._icon_picker', [
                                'inputId' => 'catEditIconInput'.$category->id,
                                'pickerId' => 'catEditPicker'.$category->id,
                                'selected' => $category->icon,
                            ])
                        </div>
                        <div class="mb-3 p-3 rounded-3" style="background:#f8fafc; border:1px solid #eef2f7;">
                            <label class="form-label small fw-semibold mb-2"><i class="bi bi-image"></i> Gambar Logo (opsional)</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="cat-icon cat-grad-5" id="catEditPreview{{ $category->id }}" style="width:54px;height:54px;">
                                    @if($category->hasImage())
                                        <img src="{{ $category->imageUrl() }}" alt="" style="width:100%;height:100%;object-fit:contain;padding:8px;">
                                    @else
                                        <i class="bi bi-box"></i>
                                    @endif
                                </div>
                                <div class="small text-muted">Pratinjau — gambar mengalahkan ikon font di menu customer.</div>
                            </div>
                            <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                            @if($category->hasImage())
                                <div class="form-check form-switch form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage{{ $category->id }}">
                                    <label class="form-check-label small text-danger" for="removeImage{{ $category->id }}"><i class="bi bi-x-circle"></i> Hapus gambar ini</label>
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <input type="text" name="description" class="form-control" value="{{ $category->description }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Urutan</label>
                            <input type="number" name="sort" class="form-control" value="{{ $category->sort }}">
                        </div>
                        <input type="hidden" name="status" value="{{ $category->status ? 1 : 0 }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold"><i class="bi bi-check-lg"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Add Card --}}
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="add-card" data-bs-toggle="modal" data-bs-target="#addModal">
            <div class="add-icon"><i class="bi bi-plus-lg"></i></div>
            <span class="fw-semibold small">Tambah Kategori</span>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Kategori</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama</label>
                        <input type="text" name="name" class="form-control" required placeholder="contoh: Pulsa">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Icon</label>
                        <input type="text" name="icon" class="form-control" id="catAddIconInput" placeholder="contoh: phone, lightning, joystick">
                        <div class="form-text">Nama icon dari Bootstrap Icons (tanpa prefix <code>bi-</code>), atau pilih dari grid di bawah.</div>
                        @include('admin.brands._icon_picker', ['inputId' => 'catAddIconInput', 'pickerId' => 'catAddPicker', 'selected' => ''])
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <input type="text" name="description" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Urutan</label>
                        <input type="number" name="sort" class="form-control" value="0">
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
