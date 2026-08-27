@extends('layouts.app')

@section('title', 'Cetak Voucher')

@push('styles')
<style>
    /* ===== Tile brand ===== */
    .cv-brand {
        display: flex; flex-direction: column; align-items: center; gap: .35rem;
        padding: .85rem .5rem;
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 1rem;
        cursor: pointer;
        transition: all .15s;
        height: 100%;
    }
    .cv-brand:hover { border-color: #c7d7fe; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(99,102,241,.12); }
    .cv-brand.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .cv-logo {
        width: 46px; height: 46px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 1.05rem;
        box-shadow: 0 4px 10px rgba(0,0,0,.14);
    }
    .cv-name { font-weight: 700; font-size: .8rem; text-align: center; line-height: 1.2; }

    /* ===== Step header ===== */
    .step-label {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: #2563eb; background: #eff6ff;
        border-radius: 999px; padding: .25rem .65rem;
    }
    .step-label.done { background: #f0fdf4; color: #16a34a; }
    .step-label.disabled { background: #f1f5f9; color: #94a3b8; }

    /* ===== Kartu produk ===== */
    .cv-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    @media (min-width: 768px) { .cv-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .cv-grid { grid-template-columns: repeat(4, 1fr); } }
    .cv-item {
        display: flex; flex-direction: column; justify-content: space-between; gap: .5rem;
        padding: .9rem .95rem;
        border: 1px solid #eef2f7; border-radius: .85rem;
        background: #fff; text-decoration: none; color: inherit;
        transition: all .15s; cursor: pointer;
    }
    .cv-item:hover { border-color: #c7d7fe; box-shadow: 0 6px 14px rgba(99,102,241,.12); transform: translateY(-2px); }
    .cv-item .cv-denom { font-weight: 700; font-size: 1rem; line-height: 1.2; display: block; color: #1e293b; }
    .cv-item .cv-name {
        font-size: .72rem; color: #475569; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .cv-item .cv-region { font-size: .68rem; color: #94a3b8; display: block; }
    .cv-item .cv-price { font-weight: 700; color: #3b82f6; display: block; }

    /* ===== Bottom sheet ===== */
    .bottom-sheet-overlay {
        position: fixed; inset: 0; z-index: 1050;
        background: rgba(15,23,42,.5);
        opacity: 0; visibility: hidden; transition: all .25s;
    }
    .bottom-sheet-overlay.show { opacity: 1; visibility: visible; }
    .bottom-sheet {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 1051;
        background: #fff;
        border-radius: 1.5rem 1.5rem 0 0;
        padding: 1.1rem 1.25rem calc(1.1rem + env(safe-area-inset-bottom));
        transform: translateY(100%); transition: transform .3s ease;
        max-height: 92vh; overflow-y: auto;
    }
    .bottom-sheet.show { transform: translateY(0); }
    .bs-handle { width: 42px; height: 5px; background: #e2e8f0; border-radius: 3px; margin: 0 auto 1rem; }
    .bs-title { padding-top: .25rem; }
    .bs-desc { font-size: .85rem; color: #475569; background: #f0f9ff; border: 1px solid #e0f2fe; border-radius: .6rem; padding: .6rem .75rem; }
    .bs-detail { background: #f8fafc; border: 1px solid #eef2f7; border-radius: .9rem; padding: .2rem .9rem; }
    .bs-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem 0; gap: .5rem; }
    .bs-row + .bs-row { border-top: 1px dashed #e2e8f0; }
    .bs-label { font-size: .85rem; color: #64748b; }
    .bs-value { font-size: .9rem; }

    .cv-info {
        display: flex; gap: .6rem; align-items: flex-start;
        background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        border-radius: .7rem; padding: .7rem .85rem; font-size: .82rem;
    }
    .cv-info i { font-size: 1.1rem; line-height: 1.2; }
</style>
@endpush

@section('content')
{{-- ============ STEP 1: PILIH OPERATOR ============ --}}
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="step-label"><i class="bi bi-1-circle"></i> Pilih Operator</span>
            <span class="step-label disabled" id="step2Label"><i class="bi bi-2-circle"></i> Pilih Voucher</span>
        </div>
        <h6 class="fw-bold mb-1"><i class="bi bi-printer text-primary"></i> Cetak Voucher</h6>
        <p class="small text-muted mb-3">Pilih operator, lalu pilih voucher yang ingin diaktifkan.</p>

        <div class="cv-info mb-3">
            <i class="bi bi-info-circle"></i>
            <span>Voucher ini berupa <strong>kode voucher (SN)</strong> yang bisa Anda aktifkan sendiri — bukan langsung terisi ke nomor. Setelah pembayaran sukses, kode voucher muncul di halaman order.</span>
        </div>

        @if($brands->isNotEmpty())
            <div class="row g-2">
                @foreach($brands as $brand => $count)
                <div class="col-4 col-md-3 col-lg-2">
                    <button type="button" class="cv-brand w-100" data-brand="{{ $brand }}" onclick="selectBrand('{{ $brand }}')">
                        <span class="cv-logo" style="background: {{ \App\Support\VoucherBrands::color($brand) }};">{{ strtoupper(substr($brand, 0, 1)) }}</span>
                        <span class="cv-name">{{ $brand }}</span>
                    </button>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-printer fs-1 d-block mb-2"></i>
                Belum ada produk cetak voucher tersedia.
            </div>
        @endif
    </div>
</div>

{{-- ============ STEP 2: PILIH VOUCHER ============ --}}
<div class="card border-0 shadow-sm d-none" id="productCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label"><i class="bi bi-2-circle"></i> Pilih Voucher</span>
            <span class="small text-muted"><strong id="cvBrandLabel"></strong></span>
        </div>
        <div id="productList">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>
                Pilih operator untuk melihat voucher.
            </div>
        </div>
    </div>
</div>

{{-- Bottom sheet konfirmasi --}}
<div class="bottom-sheet-overlay" id="bsOverlay" onclick="closeSheet()"></div>
<div class="bottom-sheet" id="bsSheet">
    <div class="bs-handle"></div>
    <div id="bsBody"></div>
</div>
@endsection

@push('scripts')
<script>
    let selectedBrand = null;
    let currentProducts = [];

    // ============ PILIH OPERATOR ============
    function selectBrand(brand) {
        selectedBrand = brand;

        document.querySelectorAll('.cv-brand').forEach(function (b) {
            b.classList.toggle('active', b.dataset.brand === brand);
        });

        document.getElementById('cvBrandLabel').textContent = brand;
        document.getElementById('productCard').classList.remove('d-none');
        loadProducts(brand);
    }

    // ============ AMBIL PRODUK ============
    async function loadProducts(brand) {
        const list = document.getElementById('productList');
        list.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Memuat voucher...</div>';

        try {
            const res = await fetch('{{ route('customer.cetak-voucher.products') }}' + '?brand=' + encodeURIComponent(brand), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!data.products || data.products.length === 0) {
                list.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada voucher untuk ' + brand + '.</div>';
                return;
            }

            currentProducts = data.products;
            renderProducts();
        } catch (e) {
            list.innerHTML = '<div class="text-center py-4 text-muted">Gagal memuat voucher.</div>';
        }
    }

    function renderProducts() {
        const list = document.getElementById('productList');
        list.innerHTML = '';

        const grid = document.createElement('div');
        grid.className = 'cv-grid';
        currentProducts.forEach(function (p) {
            const el = document.createElement('div');
            el.className = 'cv-item';
            el.onclick = () => openSheet(p);
            el.innerHTML = `
                <span class="cv-denom">${p.denom || p.name}</span>
                <span class="cv-name">${p.name}</span>
                ${p.region ? `<span class="cv-region"><i class="bi bi-geo-alt"></i> ${p.region}</span>` : ''}
                <span class="cv-price">Rp ${Number(p.sell_price).toLocaleString('id-ID')}</span>
            `;
            grid.appendChild(el);
        });
        list.appendChild(grid);
    }

    // ============ BOTTOM SHEET ============
    function formatRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function openSheet(product) {
        const saldo = {{ auth()->user()->saldo ?? 0 }};
        const hasDesc = product.description && product.description.trim() && product.description !== product.name;

        const body = document.getElementById('bsBody');
        body.innerHTML = `
            <div class="bs-title mb-3">
                <div class="fw-bold fs-5">${product.name}</div>
                ${hasDesc ? `<div class="bs-desc mt-2"><i class="bi bi-info-circle me-1 text-primary"></i>${product.description}</div>` : ''}
            </div>

            <div class="bs-detail mb-3">
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-broadcast me-1"></i>Operator</span>
                    <span class="bs-value fw-semibold">${product.brand}</span>
                </div>
                ${product.region ? `
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-geo-alt me-1"></i>Wilayah</span>
                    <span class="bs-value">${product.region}</span>
                </div>` : ''}
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-tag me-1"></i>Harga</span>
                    <span class="bs-value fw-semibold text-primary">${formatRupiah(product.sell_price)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-wallet2 me-1"></i>Sisa Saldo</span>
                    <span class="bs-value ${saldo >= product.sell_price ? '' : 'text-danger fw-semibold'}">${formatRupiah(saldo)}</span>
                </div>
            </div>

            <div class="cv-info mb-3">
                <i class="bi bi-key"></i>
                <span>Setelah pembayaran sukses, <strong>kode voucher (SN)</strong> akan tampil di halaman order. Simpan kode tersebut untuk aktivasi.</span>
            </div>

            <div id="bsAlert"></div>

            <button class="btn btn-primary w-100 btn-lg fw-semibold" id="bsSubmitBtn" onclick="submitOrder(${product.id})">
                <i class="bi bi-bag-check"></i> Beli Voucher
            </button>
        `;
        document.getElementById('bsOverlay').classList.add('show');
        document.getElementById('bsSheet').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet() {
        document.getElementById('bsOverlay').classList.remove('show');
        document.getElementById('bsSheet').classList.remove('show');
        document.body.style.overflow = '';
    }

    // ============ SUBMIT ORDER ============
    async function submitOrder(productId) {
        const btn = document.getElementById('bsSubmitBtn');
        const alertBox = document.getElementById('bsAlert');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const fd = new FormData();
        fd.append('destination', '-');

        try {
            const res = await fetch('{{ route('customer.orders.store', ['product' => '__ID__']) }}'.replace('__ID__', productId), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            });
            const data = await res.json();

            if (data.success) {
                closeSheet();
                window.location.href = data.order_url;
            } else {
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">' + (data.error || 'Terjadi kesalahan.') + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bag-check"></i> Beli Voucher';
            }
        } catch (e) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Terjadi kesalahan. Silakan coba lagi.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-check"></i> Beli Voucher';
        }
    }
</script>
@endpush
