@extends('layouts.admin')

@section('title', 'Import Produk OkeConnect')

@section('content')
@php
    $operatorColors = [
        'Telkomsel' => ['text' => '#ef4444', 'bg' => '#fef2f2'],
        'Indosat' => ['text' => '#8b5cf6', 'bg' => '#f5f3ff'],
        'XL' => ['text' => '#ec4899', 'bg' => '#fdf2f8'],
        'AXIS' => ['text' => '#16a34a', 'bg' => '#f0fdf4'],
        'Three' => ['text' => '#6366f1', 'bg' => '#eef2ff'],
        'Smartfren' => ['text' => '#0891b2', 'bg' => '#ecfeff'],
    ];
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Import Produk OkeConnect</h4>
        <p class="text-muted small mb-0">Muat daftar harga dari OkeConnect, pilih produk, lalu impor ke database.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

@if($error)
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ $error }}</span>
    </div>
@endif

{{-- URL daftar harga --}}
<div class="card shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-8 col-lg-9">
                <label class="form-label small fw-semibold text-muted mb-1">URL daftar harga OkeConnect</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                    <input type="url" name="url" value="{{ $url }}" class="form-control" placeholder="https://okeconnect.com/harga/json?id=...&produk=pulsa" required>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary px-3"><i class="bi bi-cloud-download me-1"></i> Muat Produk</button>
            </div>
        </form>
        <div class="small text-muted mt-2">
            <i class="bi bi-info-circle me-1"></i>
            Kode yang sudah ada di database akan <strong>diperbarui</strong> (tidak diduplikasi). Pemetaan operator: <code>Axis</code> → <code>AXIS</code>, <code>XL - Axis</code> → <code>XL</code>, <code>By U</code> → <code>Telkomsel</code>.
        </div>
    </div>
</div>

@if($stats)
    {{-- Ringkasan --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="badge rounded-pill bg-white text-dark border px-3 py-2"><i class="bi bi-box-seam text-primary me-1"></i> Total <strong class="ms-1">{{ number_format($stats['total']) }}</strong></span>
        <span class="badge rounded-pill px-3 py-2" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0"><i class="bi bi-check-circle-fill me-1"></i> Aktif <strong class="ms-1">{{ number_format($stats['active']) }}</strong></span>
        <span class="badge rounded-pill px-3 py-2" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0"><i class="bi bi-dash-circle me-1"></i> Nonaktif <strong class="ms-1">{{ number_format($stats['inactive']) }}</strong></span>
        <span class="badge rounded-pill px-3 py-2" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe"><i class="bi bi-plus-circle-fill me-1"></i> Baru <strong class="ms-1">{{ number_format($stats['new']) }}</strong></span>
        <span class="badge rounded-pill px-3 py-2" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a"><i class="bi bi-arrow-repeat me-1"></i> Update <strong class="ms-1">{{ number_format($stats['existing']) }}</strong></span>
    </div>
@endif

@if($paginator)
    {{-- Toolbar filter --}}
    <form method="GET" class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <input type="hidden" name="url" value="{{ $url }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Cari produk</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama / kode produk...">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Operator</label>
                    <select name="operator" class="form-select form-select-sm">
                        <option value="">Semua Operator</option>
                        @foreach($operatorOptions as $op)
                            <option value="{{ $op }}" {{ request('operator') == $op ? 'selected' : '' }}>{{ $op }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Kategori</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categoryOptions as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-sm btn-primary px-3">Filter</button>
                    @if(request()->hasAny(['q', 'operator', 'category', 'status']))
                        <a href="{{ route('admin.products.import', ['url' => $url]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.products.import-store') }}" id="import-form">
        @csrf
        <input type="hidden" name="url" value="{{ $url }}">

        <div class="card shadow-sm">
            <div class="table-responsive import-table-wrap">
                <table class="table table-hover align-middle mb-0 import-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">
                                <input type="checkbox" id="check-all" class="form-check-input" title="Pilih semua di halaman ini">
                            </th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Operator</th>
                            <th class="text-end">Modal</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-end">Jual</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">DB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginator as $item)
                        <tr>
                            <td><input type="checkbox" name="codes[]" value="{{ $item['code'] }}" class="form-check-input row-check" data-modal="{{ $item['modal_price'] }}" data-existing="{{ isset($existingProducts[$item['code']]) ? 1 : 0 }}"></td>
                            <td><code class="text-primary fw-semibold">{{ $item['code'] }}</code></td>
                            <td class="fw-semibold">{{ $item['name'] }}</td>
                            <td><span class="badge bg-light text-dark">{{ $item['category'] }}</span></td>
                            <td>
                                <span class="op-badge" style="color: {{ $operatorColors[$item['operator']]['text'] ?? '#3b82f6' }}; background: {{ $operatorColors[$item['operator']]['bg'] ?? '#eff6ff' }};">
                                    {{ $item['operator'] }}
                                </span>
                            </td>
                            <td class="text-end tabular-nums text-muted">{{ number_format($item['modal_price'], 0, ',', '.') }}</td>
                            <td class="text-end tabular-nums">
                                @if(isset($existingProducts[$item['code']]))
                                    @php
                                        $oldModal = (float) $existingProducts[$item['code']]->modal_price;
                                        $newModal = (float) $item['modal_price'];
                                        $diff = $newModal - $oldModal;
                                    @endphp
                                    @if($diff == 0)
                                        <span class="badge status-badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0">Sama</span>
                                    @elseif($diff > 0)
                                        <span class="badge status-badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca" title="Harga lama {{ number_format($oldModal, 0, ',', '.') }} → baru {{ number_format($newModal, 0, ',', '.') }}"><i class="bi bi-arrow-up"></i> +{{ number_format($diff, 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge status-badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0" title="Harga lama {{ number_format($oldModal, 0, ',', '.') }} → baru {{ number_format($newModal, 0, ',', '.') }}"><i class="bi bi-arrow-down"></i> -{{ number_format(abs($diff), 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end tabular-nums fw-semibold sell-preview">{{ number_format($item['modal_price'], 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($item['active'])
                                    <span class="badge status-badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                                @else
                                    <span class="badge status-badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0"><i class="bi bi-dash-circle"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($existingProducts[$item['code']]))
                                    <span class="badge status-badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a"><i class="bi bi-arrow-repeat"></i> Update</span>
                                @else
                                    <span class="badge status-badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe"><i class="bi bi-plus-circle"></i> Baru</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom py-2">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="small text-muted">
                        Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}</strong>–<strong>{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong>{{ $paginator->total() }}</strong> produk
                    </span>
                    <span class="small text-muted">
                        <i class="bi bi-info-circle text-primary"></i>
                        Centang di header hanya memilih produk <strong>di halaman ini</strong> (maks. {{ $paginator->perPage() }}). Untuk memilih semua hasil filter, centang di setiap halaman.
                    </span>
                </div>
                @if($paginator->hasPages())
                    @php
                        $lastPage = $paginator->lastPage();
                        $currentPage = $paginator->currentPage();
                        $pageStart = max(1, $currentPage - 2);
                        $pageEnd = min($lastPage, $currentPage + 2);
                    @endphp
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            @if($pageStart > 1)
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                                @if($pageStart > 2)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                            @endif
                            @for($page = $pageStart; $page <= $pageEnd; $page++)
                                <li class="page-item {{ $currentPage == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if($pageEnd < $lastPage)
                                @if($pageEnd < $lastPage - 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                            @endif
                            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>

            {{-- Action bar sticky --}}
            <div class="import-actionbar px-3 py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Markup harga jual</label>
                        <select name="markup_type" id="markup-type" class="form-select form-select-sm">
                            <option value="none">Tanpa markup</option>
                            <option value="nominal" selected>+ Nominal (Rp)</option>
                            <option value="percent">+ Persen (%)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Nilai markup</label>
                        <input type="number" name="markup_value" id="markup-value" class="form-control form-control-sm" min="0" step="any" value="1000">
                    </div>
                    <div class="col-md-7 d-flex flex-wrap justify-content-end align-items-center gap-2">
                        <span class="small text-muted">Terpilih <strong id="selected-count" class="fs-6 text-primary">0</strong> produk</span>
                        <a href="#" id="clear-selection" class="small text-muted text-decoration-none d-none">Bersihkan</a>
                        <button type="submit" formaction="{{ route('admin.products.sync-prices') }}" class="btn btn-outline-primary" id="sync-prices-btn" title="Perbarui harga modal & harga jual semua produk yang sudah ada di database (mengikuti markup di samping)">
                            <i class="bi bi-arrow-repeat me-1"></i> Perbarui Harga
                        </button>
                        <button class="btn btn-success px-4" id="import-btn" disabled><i class="bi bi-plus-circle me-1"></i> Import Terpilih</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal konfirmasi import --}}
    <div class="modal fade" id="import-confirm-modal" tabindex="-1" aria-labelledby="import-confirm-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="import-confirm-title"><i class="bi bi-shield-check text-success me-1"></i> Konfirmasi Import</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Anda akan mengimpor <strong id="confirm-total" class="text-primary">0</strong> produk:
                        <span class="badge status-badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe"><i class="bi bi-plus-circle"></i> <span id="confirm-new">0</span> baru</span>
                        <span class="badge status-badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a"><i class="bi bi-arrow-repeat"></i> <span id="confirm-update">0</span> update</span>
                    </p>
                    <div class="alert alert-warning py-2 small d-none" id="confirm-update-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Produk bertanda <strong>Update</strong> akan <strong>ditimpa</strong> — nama, harga modal, harga jual (sesuai markup), operator, dan status diganti mengikuti daftar harga.
                    </div>
                    <p class="small text-muted mb-0">Markup: <strong id="confirm-markup">-</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="confirm-import-btn"><i class="bi bi-check-lg me-1"></i> Ya, Import</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal konfirmasi perbarui harga --}}
    <div class="modal fade" id="sync-confirm-modal" tabindex="-1" aria-labelledby="sync-confirm-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sync-confirm-title"><i class="bi bi-arrow-repeat text-primary me-1"></i> Perbarui Harga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        <strong id="confirm-sync-count" class="text-primary">{{ number_format($stats['existing']) }}</strong> produk yang sudah ada di database akan diperbarui harganya sesuai daftar terbaru.
                    </p>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Harga modal dan <strong>harga jual diganti sesuai markup yang dipilih</strong> (harga jual manual akan ditimpa). Produk baru <strong>tidak</strong> dibuat.
                    </div>
                    <p class="small text-muted mb-0">Markup: <strong id="confirm-sync-markup">-</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirm-sync-btn"><i class="bi bi-check-lg me-1"></i> Ya, Perbarui</button>
                </div>
            </div>
        </div>
    </div>
@elseif($url && ! $error)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-inbox"></i>
        @if(request()->hasAny(['q', 'operator', 'category', 'status']))
            <span>Tidak ada produk yang cocok dengan filter. <a href="{{ route('admin.products.import', ['url' => $url]) }}" class="alert-link">Reset filter</a></span>
        @else
            <span>Tidak ada produk ditemukan dari URL tersebut.</span>
        @endif
    </div>
@elseif(! $url)
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-6 text-muted mb-3"><i class="bi bi-cloud-arrow-down"></i></div>
            <h5 class="fw-semibold mb-1">Belum ada daftar produk</h5>
            <p class="text-muted small mb-0">Masukkan URL daftar harga OkeConnect di atas, lalu klik <strong>Muat Produk</strong>.</p>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .import-table-wrap { max-height: 60vh; overflow: auto; }
    .import-table-wrap thead th {
        position: sticky; top: 0; z-index: 5;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 #e5e7eb;
    }
    .import-table tbody tr:has(.row-check:checked) { background-color: #eff6ff !important; }
    .import-table tbody tr:has(.row-check:checked) td:first-child { box-shadow: inset 3px 0 0 #2563eb; }
    .op-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .72rem; font-weight: 600;
        padding: .3em .7em; border-radius: 999px;
        border: 1px solid rgba(0,0,0,.06); white-space: nowrap;
    }
    .status-badge { font-size: .72rem; font-weight: 600; }
    .tabular-nums { font-variant-numeric: tabular-nums; }
    .import-actionbar {
        position: sticky; bottom: 0; z-index: 10;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(6px);
        border-top: 1px solid #e9ecef;
        box-shadow: 0 -4px 16px rgba(0,0,0,.05);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('check-all');
    const rows = document.querySelectorAll('.row-check');
    const count = document.getElementById('selected-count');
    const btn = document.getElementById('import-btn');
    const clearBtn = document.getElementById('clear-selection');
    const markupType = document.getElementById('markup-type');
    const markupValue = document.getElementById('markup-value');

    function update() {
        const n = document.querySelectorAll('.row-check:checked').length;
        count.textContent = n;
        btn.disabled = n === 0;
        clearBtn.classList.toggle('d-none', n === 0);
        const total = rows.length;
        checkAll.checked = total > 0 && n === total;
        checkAll.indeterminate = n > 0 && n < total;
    }

    function format(n) {
        return n.toLocaleString('id-ID');
    }

    function recompute() {
        const type = markupType.value;
        const val = parseFloat(markupValue.value) || 0;

        rows.forEach(function (row) {
            const modal = parseFloat(row.dataset.modal) || 0;
            let sell = modal;
            if (type === 'nominal') sell = modal + val;
            if (type === 'percent') sell = modal * (1 + val / 100);
            row.closest('tr').querySelector('.sell-preview').textContent = format(Math.round(sell));
        });
    }

    checkAll.addEventListener('change', function () {
        rows.forEach(function (r) { r.checked = checkAll.checked; });
        update();
    });

    rows.forEach(function (r) { r.addEventListener('change', update); });

    clearBtn.addEventListener('click', function (e) {
        e.preventDefault();
        rows.forEach(function (r) { r.checked = false; });
        update();
    });

    markupType.addEventListener('change', recompute);
    markupValue.addEventListener('input', recompute);

    // Sinkronkan preview dengan markup default saat halaman pertama dimuat
    recompute();

    // ===== Konfirmasi import & perbarui harga =====
    const importForm = document.getElementById('import-form');
    const confirmModalEl = document.getElementById('import-confirm-modal');
    const syncBtn = document.getElementById('sync-prices-btn');
    const syncModalEl = document.getElementById('sync-confirm-modal');
    let importConfirmed = false;
    let syncConfirmed = false;

    function markupText() {
        const type = markupType.value;
        const raw = parseFloat(markupValue.value) || 0;
        if (type === 'nominal') return '+ Rp' + format(raw);
        if (type === 'percent') return '+ ' + raw + '%';
        return 'Tanpa markup (harga jual = harga modal)';
    }

    if (importForm && typeof bootstrap !== 'undefined') {
        const confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;

        function openImportConfirm() {
            if (! confirmModal) return;
            const checked = document.querySelectorAll('.row-check:checked');
            if (checked.length === 0) return;

            let updateCount = 0;
            checked.forEach(function (r) {
                if (r.dataset.existing === '1') updateCount++;
            });
            const newCount = checked.length - updateCount;

            document.getElementById('confirm-total').textContent = checked.length;
            document.getElementById('confirm-new').textContent = newCount;
            document.getElementById('confirm-update').textContent = updateCount;
            document.getElementById('confirm-update-warning').classList.toggle('d-none', updateCount === 0);
            document.getElementById('confirm-markup').textContent = markupText();

            confirmModal.show();
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openImportConfirm();
        });

        // Cegah submit langsung (mis. tekan Enter di kolom markup) tanpa konfirmasi
        importForm.addEventListener('submit', function (e) {
            if (syncConfirmed || importConfirmed) return;
            e.preventDefault();
            openImportConfirm();
        });

        document.getElementById('confirm-import-btn').addEventListener('click', function () {
            importConfirmed = true;
            importForm.submit();
        });

        // ===== Perbarui harga semua produk existing =====
        if (syncBtn && syncModalEl) {
            const syncModal = new bootstrap.Modal(syncModalEl);

            syncBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('confirm-sync-markup').textContent = markupText();
                syncModal.show();
            });

            document.getElementById('confirm-sync-btn').addEventListener('click', function () {
                syncConfirmed = true;
                if (typeof importForm.requestSubmit === 'function') {
                    importForm.requestSubmit(syncBtn);
                } else {
                    importForm.action = syncBtn.getAttribute('formaction');
                    importForm.submit();
                }
            });
        }
    }
});
</script>
@endpush
