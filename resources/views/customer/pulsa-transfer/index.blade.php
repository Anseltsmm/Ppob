@extends('layouts.app')

@section('title', 'Pulsa Transfer')

@push('styles')
<style>
    /* ===== Input nomor ===== */
    .step-label {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: #2563eb; background: #eff6ff;
        border-radius: 999px; padding: .25rem .65rem;
    }

    /* ===== Kartu produk ===== */
    .pt-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    @media (min-width: 768px) { .pt-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .pt-grid { grid-template-columns: repeat(4, 1fr); } }
    .pt-item {
        display: flex; flex-direction: column; justify-content: space-between; gap: .5rem;
        padding: .9rem .95rem;
        border: 1px solid #eef2f7; border-radius: .85rem;
        background: #fff; text-decoration: none; color: inherit;
        transition: all .15s; cursor: pointer;
    }
    .pt-item:hover { border-color: #c7d7fe; box-shadow: 0 6px 14px rgba(99,102,241,.12); transform: translateY(-2px); }
    .pt-item .pt-name {
        font-size: .82rem; font-weight: 600; color: #1e293b; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .pt-item .pt-denom { font-size: .7rem; color: #64748b; display: block; }
    .pt-item .pt-price { font-weight: 700; color: #3b82f6; display: block; }

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
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="step-label"><i class="bi bi-1-circle"></i> Nomor Tujuan</span>
            <span class="step-label disabled" id="step2Label"><i class="bi bi-2-circle"></i> Pilih Nominal</span>
        </div>
        <h6 class="fw-bold mb-1"><i class="bi bi-arrow-left-right text-primary"></i> Pulsa Transfer</h6>
        <p class="small text-muted mb-3">Transfer pulsa ke nomor tujuan (mis. By.U Direct).</p>

        <div class="row g-1 align-items-center">
            <div class="col-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white"><i class="bi bi-phone"></i></span>
                    <input type="tel" id="numberInput" class="form-control py-2 px-3" placeholder="Masukkan nomor tujuan (08xx...)" autocomplete="off" oninput="onNumberInput()">
                </div>
                <div class="small text-muted mt-1" id="numberHint"></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm d-none" id="productCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label"><i class="bi bi-2-circle"></i> Pilih Nominal</span>
            <span class="small text-muted">Transfer ke <strong id="ptNumberLabel"></strong></span>
        </div>
        <div id="productList">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>
                Masukkan nomor tujuan untuk melihat nominal.
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
    let debounceTimer = null;
    let currentProducts = [];
    let currentNumber = '';

    // ============ INPUT NOMOR ============
    function normalizeNumber() {
        return document.getElementById('numberInput').value.replace(/\D/g, '');
    }

    function onNumberInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async function () {
            const number = normalizeNumber();
            const hint = document.getElementById('numberHint');

            if (number.length === 0) {
                hint.textContent = '';
                document.getElementById('productCard').classList.add('d-none');
                return;
            }
            if (number.length < 8) {
                hint.textContent = 'Nomor minimal 8 digit.';
                document.getElementById('productCard').classList.add('d-none');
                return;
            }

            hint.textContent = '';
            await loadProducts(number);
        }, 400);
    }

    // ============ AMBIL PRODUK ============
    async function loadProducts(number) {
        const list = document.getElementById('productList');
        document.getElementById('productCard').classList.remove('d-none');
        document.getElementById('ptNumberLabel').textContent = number;
        list.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Memuat nominal...</div>';

        try {
            const res = await fetch('{{ route('customer.pulsa-transfer.products') }}' + '?number=' + encodeURIComponent(number), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!data.products || data.products.length === 0) {
                list.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada produk pulsa transfer untuk nomor ini.</div>';
                return;
            }

            currentNumber = number;
            currentProducts = data.products;
            renderProducts();
        } catch (e) {
            list.innerHTML = '<div class="text-center py-4 text-muted">Gagal memuat nominal.</div>';
        }
    }

    function renderProducts() {
        const list = document.getElementById('productList');
        list.innerHTML = '';

        const grid = document.createElement('div');
        grid.className = 'pt-grid';
        currentProducts.forEach(function (p) {
            const el = document.createElement('div');
            el.className = 'pt-item';
            el.onclick = () => openSheet(p);
            el.innerHTML = `
                <span class="pt-name">${p.name}</span>
                <span class="pt-denom">${p.denom || ''}</span>
                <span class="pt-price">Rp ${Number(p.sell_price).toLocaleString('id-ID')}</span>
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
        const number = currentNumber;
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
                    <span class="bs-label"><i class="bi bi-phone me-1"></i>Nomor Tujuan</span>
                    <span class="bs-value fw-semibold">${number}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-tag me-1"></i>Harga</span>
                    <span class="bs-value fw-semibold text-primary">${formatRupiah(product.sell_price)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-wallet2 me-1"></i>Sisa Saldo</span>
                    <span class="bs-value ${saldo >= product.sell_price ? '' : 'text-danger fw-semibold'}">${formatRupiah(saldo)}</span>
                </div>
            </div>

            <div id="bsAlert"></div>

            <button class="btn btn-primary w-100 btn-lg fw-semibold" id="bsSubmitBtn" onclick="submitOrder(${product.id}, '${number}')">
                <i class="bi bi-bag-check"></i> Proses Pembelian
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
    async function submitOrder(productId, number) {
        const btn = document.getElementById('bsSubmitBtn');
        const alertBox = document.getElementById('bsAlert');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const fd = new FormData();
        fd.append('destination', number);

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
                btn.innerHTML = '<i class="bi bi-bag-check"></i> Proses Pembelian';
            }
        } catch (e) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Terjadi kesalahan. Silakan coba lagi.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-check"></i> Proses Pembelian';
        }
    }
</script>
@endpush
