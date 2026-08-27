@extends('layouts.app')

@section('title', 'Top Up E-Wallet')

@push('styles')
<style>
    /* ===== Tile brand e-wallet ===== */
    .ew-brand {
        display: flex; flex-direction: column; align-items: center; gap: .35rem;
        padding: .85rem .5rem;
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 1rem;
        cursor: pointer;
        transition: all .15s;
        height: 100%;
    }
    .ew-brand:hover { border-color: #c7d7fe; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(99,102,241,.12); }
    .ew-brand.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .ew-logo {
        width: 46px; height: 46px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 1.05rem;
        box-shadow: 0 4px 10px rgba(0,0,0,.14);
    }
    .ew-name { font-weight: 700; font-size: .8rem; }

    /* ===== Step header ===== */
    .step-label {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: #2563eb; background: #eff6ff;
        border-radius: 999px; padding: .25rem .65rem;
    }
    .step-label.done { background: #f0fdf4; color: #16a34a; }
    .step-label.disabled { background: #f1f5f9; color: #94a3b8; }

    /* ===== Chip nominal bebas ===== */
    .denom-chip {
        border: 1.5px solid #e2e8f0; background: #fff;
        border-radius: .65rem; padding: .45rem .7rem;
        font-weight: 600; font-size: .82rem; color: #334155;
        cursor: pointer; transition: all .15s;
    }
    .denom-chip:hover { border-color: #93c5fd; }
    .denom-chip.active { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }

    /* ===== Kartu produk (fixed & open denom) ===== */
    .ew-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    @media (min-width: 768px) { .ew-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .ew-grid { grid-template-columns: repeat(4, 1fr); } }
    .ew-item {
        display: flex; flex-direction: column; justify-content: space-between; gap: .5rem;
        padding: .9rem .95rem;
        border: 1px solid #eef2f7; border-radius: .85rem;
        background: #fff; text-decoration: none; color: inherit;
        transition: all .15s; cursor: pointer;
    }
    .ew-item:hover { border-color: #c7d7fe; box-shadow: 0 6px 14px rgba(99,102,241,.12); transform: translateY(-2px); }
    .ew-item .ew-name { font-size: .85rem; line-height: 1.3; display: block; }
    .ew-item .ew-price { font-weight: 700; color: #3b82f6; display: block; }

    .ew-opendenom {
        border: 1.5px dashed #60a5fa; background: linear-gradient(180deg, #f0f7ff, #fff);
        display: flex; flex-direction: column; gap: .4rem;
        padding: 1rem 1.1rem; border-radius: 1rem; cursor: pointer;
        transition: all .15s;
    }
    .ew-opendenom:hover { border-color: #2563eb; box-shadow: 0 6px 14px rgba(37,99,235,.14); }
    .ew-opendenom .ew-od-title { font-weight: 700; font-size: .95rem; color: #1d4ed8; }
    .ew-opendenom .ew-od-sub { font-size: .78rem; color: #64748b; }

    /* ===== Bottom sheet (sama dengan halaman layanan) ===== */
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

    /* ===== Kontak ===== */
    .contact-avatar {
        width: 40px; height: 40px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
{{-- ============ STEP 1: PILIH E-WALLET ============ --}}
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="step-label" id="step1Label"><i class="bi bi-1-circle"></i> Pilih E-Wallet</span>
            <span class="step-label disabled" id="step2Label"><i class="bi bi-2-circle"></i> Nomor</span>
            <span class="step-label disabled" id="step3Label"><i class="bi bi-3-circle"></i> Nominal</span>
        </div>
        <h6 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary"></i> Top Up E-Wallet</h6>
        <p class="small text-muted mb-3">Pilih e-wallet tujuan, lalu masukkan nomor yang terdaftar.</p>

        @if($brands->isNotEmpty())
            <div class="row g-2" id="brandGrid">
                @foreach($brands as $brand => $count)
                <div class="col-4 col-md-3 col-lg-2">
                    <button type="button" class="ew-brand w-100" data-brand="{{ $brand }}" onclick="selectBrand('{{ $brand }}')">
                        <span class="ew-logo" style="background: {{ \App\Support\EwalletBrands::color($brand) }};">{{ strtoupper(substr($brand, 0, 1)) }}</span>
                        <span class="ew-name">{{ $brand }}</span>
                    </button>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-wallet2 fs-1 d-block mb-2"></i>
                Belum ada produk e-wallet tersedia.
            </div>
        @endif
    </div>
</div>

{{-- ============ STEP 2: NOMOR TUJUAN ============ --}}
<div class="card border-0 shadow-sm mb-3 d-none" id="numberCard">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label" id="numberStepLabel"><i class="bi bi-2-circle"></i> Nomor Tujuan</span>
            <span class="small text-muted">Nomor HP terdaftar di <strong id="numberBrandLabel"></strong></span>
        </div>
        <div class="row g-1 align-items-center">
            <div class="col-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white"><i class="bi bi-phone"></i></span>
                    <input type="text" id="phoneNumber" class="form-control py-2 px-3" placeholder="08xxxxxxxxxx" inputmode="numeric" autocomplete="off" oninput="onNumberInput()">
                    <button type="button" class="btn btn-outline-primary" id="btnOpenContacts" onclick="openContacts()" title="Pilih dari kontak">
                        <i class="bi bi-person-lines-fill"></i>
                    </button>
                </div>
                <div class="small text-muted mt-1" id="numberHint"></div>
            </div>
        </div>
    </div>
</div>

{{-- ============ STEP 3: NOMINAL / PRODUK ============ --}}
<div class="card border-0 shadow-sm d-none" id="productCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label" id="productStepLabel"><i class="bi bi-3-circle"></i> Pilih Nominal</span>
        </div>
        <div id="productList">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>
                Masukkan nomor tujuan untuk melihat nominal.
            </div>
        </div>
    </div>
</div>

{{-- Modal kontak --}}
<div class="modal fade" id="contactsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-1"></i>Pilih Kontak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="loadDeviceContacts()">
                        <i class="bi bi-phone"></i> Dari Perangkat
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="showAddContact()">
                        <i class="bi bi-person-plus"></i> Kontak Tersimpan
                    </button>
                </div>
                <div id="contactsList" style="max-height:45vh; overflow-y:auto;"></div>
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
    let selectedBrand = null;
    let currentProducts = [];
    let requestedKey = null;

    // ============ PILIH BRAND ============
    function selectBrand(brand) {
        selectedBrand = brand;

        document.querySelectorAll('.ew-brand').forEach(function (b) {
            b.classList.toggle('active', b.dataset.brand === brand);
        });

        document.getElementById('step1Label').classList.add('done');
        document.getElementById('numberStepLabel').classList.remove('disabled');
        document.getElementById('numberBrandLabel').textContent = brand;
        document.getElementById('numberCard').classList.remove('d-none');

        const input = document.getElementById('phoneNumber');
        input.focus();

        // Reload produk jika nomor sudah valid
        const number = normalizeNumber();
        if (number.length >= 8) onNumberInput();
        else resetProducts();
    }

    // ============ NOMOR TUJUAN ============
    function normalizeNumber() {
        let num = document.getElementById('phoneNumber').value.replace(/[^0-9]/g, '');
        if (num.startsWith('62')) num = '0' + num.slice(2);
        return num;
    }

    function onNumberInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async function () {
            const number = normalizeNumber();
            const hint = document.getElementById('numberHint');

            if (number.length === 0) {
                hint.textContent = '';
                resetProducts();
                return;
            }
            if (number.length < 8) {
                hint.textContent = 'Nomor belum lengkap (min 8 digit).';
                resetProducts();
                return;
            }

            hint.textContent = '';
            await loadProducts(number);
        }, 400);
    }

    // ============ AMBIL PRODUK ============
    async function loadProducts(number) {
        if (!selectedBrand) return;

        const key = selectedBrand + '|' + number;
        requestedKey = key;

        const list = document.getElementById('productList');
        document.getElementById('productCard').classList.remove('d-none');
        document.getElementById('productStepLabel').classList.remove('disabled');
        list.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Memuat produk...</div>';

        try {
            const res = await fetch('{{ route('customer.ewallet.products') }}' + '?brand=' + encodeURIComponent(selectedBrand) + '&number=' + encodeURIComponent(number), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (requestedKey !== key) return;

            if (!data.products || data.products.length === 0) {
                list.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada produk untuk ' + selectedBrand + '.</div>';
                return;
            }

            currentProducts = data.products;
            renderProducts();
        } catch (e) {
            if (requestedKey === key) {
                list.innerHTML = '<div class="text-center py-4 text-muted">Gagal memuat produk.</div>';
            }
        }
    }

    function renderProducts() {
        const list = document.getElementById('productList');
        list.innerHTML = '';

        const openDenom = currentProducts.filter(p => p.type === 'opendenom');
        const fixed = currentProducts.filter(p => p.type !== 'opendenom');

        // Open denom: kartu isi nominal sendiri
        openDenom.forEach(function (p) {
            const card = document.createElement('div');
            card.className = 'ew-opendenom mb-3';
            card.onclick = () => openSheet(p);
            card.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span class="ew-od-title"><i class="bi bi-pencil-square me-1"></i>${p.name}</span>
                    <i class="bi bi-chevron-right text-primary"></i>
                </div>
                <span class="ew-od-sub">Isi nominal sendiri (min Rp ${Number(p.min_nominal).toLocaleString('id-ID')} — maks Rp ${Number(p.max_nominal).toLocaleString('id-ID')}) + biaya admin</span>
            `;
            list.appendChild(card);
        });

        // Fixed denom: grid produk
        if (fixed.length) {
            const grid = document.createElement('div');
            grid.className = 'ew-grid';
            fixed.forEach(function (p) {
                const el = document.createElement('div');
                el.className = 'ew-item';
                el.onclick = () => openSheet(p);
                el.innerHTML = `
                    <span class="ew-name">${p.name}</span>
                    <span class="ew-price">Rp ${Number(p.sell_price).toLocaleString('id-ID')}</span>
                `;
                grid.appendChild(el);
            });
            list.appendChild(grid);
        }
    }

    function resetProducts() {
        currentProducts = [];
        document.getElementById('productCard').classList.add('d-none');
    }

    // ============ BOTTOM SHEET ============
    function formatRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function openSheet(product) {
        const number = normalizeNumber();
        const saldo = {{ auth()->user()->saldo ?? 0 }};
        const isOpen = product.type === 'opendenom';
        const hasDesc = product.description && product.description.trim() && product.description !== product.name;

        let priceHtml = '';
        if (isOpen) {
            // Daftar nominal cepat dalam rentang min/max
            const candidates = [10000, 20000, 25000, 50000, 100000, 200000, 500000, 1000000];
            const chips = candidates.filter(c => c >= (product.min_nominal || 0) && c <= (product.max_nominal || 9999999));
            const chipsHtml = chips.length
                ? chips.map(c => `<button type="button" class="denom-chip" data-nominal="${c}" onclick="setNominal(this)">${formatRupiah(c)}</button>`).join('')
                : '';

            priceHtml = `
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-pencil-square me-1"></i>Nominal Top Up</span>
                    <span class="bs-value"></span>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">${chipsHtml}</div>
                <input type="text" id="bsNominal" class="form-control text-end fw-bold" inputmode="numeric" placeholder="Nominal (min ${formatRupiah(product.min_nominal)})" oninput="onNominalInput(${product.id})">
                <div class="small text-muted mt-1" id="bsNominalHint">Min ${formatRupiah(product.min_nominal)} — maks ${formatRupiah(product.max_nominal)}</div>
                <div class="bs-row">
                    <span class="bs-label">Biaya Admin</span>
                    <span class="bs-value" id="bsAdminFee">${formatRupiah(product.admin_fee)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label fw-semibold">Total Bayar</span>
                    <span class="bs-value fw-bold text-primary fs-6" id="bsTotal">—</span>
                </div>
            `;
        } else {
            priceHtml = `
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-tag me-1"></i>Harga</span>
                    <span class="bs-value fw-semibold text-primary">${formatRupiah(product.sell_price)}</span>
                </div>
            `;
        }

        document.getElementById('bsBody').innerHTML = `
            <div class="bs-title mb-3">
                <div class="fw-bold fs-5">${product.name}</div>
                ${hasDesc ? `<div class="bs-desc mt-2"><i class="bi bi-info-circle me-1 text-primary"></i>${product.description}</div>` : ''}
            </div>
            <div class="bs-detail mb-3">
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-wallet2 me-1"></i>E-Wallet</span>
                    <span class="bs-value fw-semibold">${selectedBrand}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-phone me-1"></i>Nomor Tujuan</span>
                    <span class="bs-value fw-semibold" id="bsNumber">${number}</span>
                </div>
                ${priceHtml}
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-wallet2 me-1"></i>Sisa Saldo</span>
                    <span class="bs-value ${saldo >= (isOpen ? 0 : product.sell_price) ? '' : 'text-danger fw-semibold'}" id="bsSaldo">${formatRupiah(saldo)}</span>
                </div>
            </div>
            <div id="bsAlert"></div>
            <button class="btn btn-primary w-100 btn-lg fw-semibold" id="bsSubmitBtn" onclick="submitOrder(${product.id})">
                <i class="bi bi-bag-check"></i> Proses Pembelian
            </button>
        `;

        // State untuk open denom
        if (isOpen) {
            document.getElementById('bsSubmitBtn').dataset.openDenom = '1';
            onNominalInput(product.id);
        }

        document.getElementById('bsOverlay').classList.add('show');
        document.getElementById('bsSheet').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet() {
        document.getElementById('bsOverlay').classList.remove('show');
        document.getElementById('bsSheet').classList.remove('show');
        document.body.style.overflow = '';
    }

    // ============ OPEN DENOM: NOMINAL ============
    function setNominal(btn) {
        document.querySelectorAll('.denom-chip').forEach(c => c.classList.toggle('active', c === btn));
        document.getElementById('bsNominal').value = btn.dataset.nominal;
        const product = currentProducts.find(p => p.type === 'opendenom');
        if (product) onNominalInput(product.id);
    }

    function onNominalInput(productId) {
        const product = currentProducts.find(p => p.id === productId);
        if (!product || product.type !== 'opendenom') return;

        const input = document.getElementById('bsNominal');
        const raw = input.value.replace(/[^0-9]/g, '');
        const qty = parseInt(raw, 10) || 0;
        const min = product.min_nominal || 0;
        const max = product.max_nominal || 0;
        const hint = document.getElementById('bsNominalHint');
        const total = document.getElementById('bsTotal');
        const btn = document.getElementById('bsSubmitBtn');

        input.value = qty ? qty.toLocaleString('id-ID') : '';

        if (qty < min || qty > max) {
            hint.textContent = 'Nominal harus antara ' + formatRupiah(min) + ' — ' + formatRupiah(max);
            hint.className = 'small text-danger mt-1';
            total.textContent = '—';
            btn.disabled = true;
        } else {
            hint.textContent = 'Min ' + formatRupiah(min) + ' — maks ' + formatRupiah(max);
            hint.className = 'small text-muted mt-1';
            total.textContent = formatRupiah(qty + product.admin_fee);
            btn.disabled = false;
        }
    }

    // ============ SUBMIT ORDER ============
    async function submitOrder(productId) {
        const number = document.getElementById('bsNumber').textContent;
        const btn = document.getElementById('bsSubmitBtn');
        const alertBox = document.getElementById('bsAlert');

        let qty = null;
        if (btn.dataset.openDenom) {
            qty = parseInt(document.getElementById('bsNominal').value.replace(/[^0-9]/g, ''), 10) || 0;
            const product = currentProducts.find(p => p.id === productId);
            if (qty < (product.min_nominal || 0) || qty > (product.max_nominal || 0)) {
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Nominal tidak valid. Cek kembali nominal yang diisi.</div>';
                return;
            }
        }

        if (!number || number.length < 8) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Nomor tujuan tidak valid.</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
        alertBox.innerHTML = '';

        try {
            const res = await fetch('/order/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ destination: number, qty: qty })
            });
            const data = await res.json();

            if (!res.ok) {
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">' + (data.error || 'Gagal membuat order.') + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bag-check"></i> Proses Pembelian';
                return;
            }

            alertBox.innerHTML = '<div class="alert alert-success py-2 small mb-2">' + data.message + '</div>';
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Order dibuat';

            setTimeout(() => {
                closeSheet();
                document.getElementById('productList').innerHTML =
                    '<div class="text-center py-5">' +
                    '<div class="text-success fs-1"><i class="bi bi-check-circle"></i></div>' +
                    '<div class="fw-semibold mt-2">' + data.message + '</div>' +
                    '<a href="/orders" class="btn btn-outline-primary btn-sm mt-3">Lihat Riwayat</a>' +
                    '</div>';
            }, 1200);
        } catch (e) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Terjadi kesalahan. Silakan coba lagi.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-check"></i> Proses Pembelian';
        }
    }

    // ============ KONTAK ============
    const CONTACTS_KEY = 'ppob_contacts_' + {{ auth()->id() }};

    function getSavedContacts() {
        try { return JSON.parse(localStorage.getItem(CONTACTS_KEY)) || []; }
        catch (e) { return []; }
    }

    function openContacts() {
        renderSavedContacts();
        new bootstrap.Modal(document.getElementById('contactsModal')).show();
    }

    function renderSavedContacts() {
        const list = document.getElementById('contactsList');
        const saved = getSavedContacts();

        let html = '<div class="small fw-semibold text-muted mb-2">Kontak Tersimpan (' + saved.length + ')</div>';
        if (saved.length === 0) {
            html += '<div class="text-center py-4 text-muted"><i class="bi bi-person-plus fs-1 d-block mb-2"></i>Belum ada kontak tersimpan.</div>';
        } else {
            saved.forEach(function (c) {
                html += '<button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 w-100 text-start border rounded mb-2 p-2" onclick="pickContact(\'' + c.number + '\')">' +
                    '<span class="contact-avatar">' + (c.name || '?').charAt(0).toUpperCase() + '</span>' +
                    '<span class="flex-grow-1"><span class="d-block fw-semibold">' + (c.name || 'Tanpa Nama').replaceAll('<', '&lt;') + '</span>' +
                    '<span class="small text-muted">' + c.number + '</span></span>' +
                    '<span class="text-muted"><i class="bi bi-chevron-right"></i></span></button>';
            });
        }
        list.innerHTML = html;
    }

    function renderAddContact() {
        document.getElementById('contactsList').innerHTML = `
            <div class="small fw-semibold text-muted mb-2">Tambah Kontak Baru</div>
            <div class="mb-2">
                <label class="form-label small">Nama</label>
                <input type="text" class="form-control" id="newContactName" placeholder="Nama kontak">
            </div>
            <div class="mb-3">
                <label class="form-label small">Nomor HP</label>
                <input type="text" class="form-control" id="newContactNumber" placeholder="08xxxxxxxxxx" inputmode="numeric">
            </div>
            <button type="button" class="btn btn-success w-100" onclick="saveContact()"><i class="bi bi-check-lg"></i> Simpan Kontak</button>
        `;
    }

    function showAddContact() {
        renderAddContact();
    }

    function saveContact() {
        const name = document.getElementById('newContactName').value.trim();
        let number = document.getElementById('newContactNumber').value.replace(/[^0-9]/g, '');
        if (number.startsWith('62')) number = '0' + number.slice(2);
        if (number.length < 8) { alert('Nomor HP tidak valid.'); return; }

        const contacts = getSavedContacts();
        contacts.push({ name: name || 'Tanpa Nama', number: number });
        localStorage.setItem(CONTACTS_KEY, JSON.stringify(contacts));
        renderSavedContacts();
    }

    async function loadDeviceContacts() {
        if (navigator.contacts && navigator.contacts.select) {
            try {
                const contacts = await navigator.contacts.select(['name', 'tel'], { multiple: true });
                renderDeviceContacts(contacts);
                return;
            } catch (e) { /* ditolak / batal */ }
        }
        renderSavedContacts();
        alert('Perangkat/browser tidak mendukung akses kontak. Gunakan Kontak Tersimpan.');
    }

    function renderDeviceContacts(contacts) {
        const list = document.getElementById('contactsList');
        const withTel = contacts.filter(c => c.tel && c.tel.length);
        let html = '<div class="small fw-semibold text-muted mb-2">Kontak Perangkat</div>';
        if (withTel.length === 0) {
            html += '<div class="text-center py-4 text-muted">Tidak ada kontak dengan nomor telepon.</div>';
        } else {
            withTel.forEach(function (c) {
                const tel = (c.tel[0] || '').replace(/[^0-9]/g, '');
                if (!tel) return;
                const n = tel.startsWith('62') ? '0' + tel.slice(2) : tel;
                html += '<button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 w-100 text-start border rounded mb-2 p-2" onclick="pickContact(\'' + n + '\')">' +
                    '<span class="contact-avatar">' + (c.name || '?').charAt(0).toUpperCase() + '</span>' +
                    '<span class="flex-grow-1"><span class="d-block fw-semibold">' + (c.name || 'Tanpa Nama').replaceAll('<', '&lt;') + '</span>' +
                    '<span class="small text-muted">' + n + '</span></span>' +
                    '<span class="text-muted"><i class="bi bi-chevron-right"></i></span></button>';
            });
        }
        list.innerHTML = html;
    }

    function pickContact(number) {
        const input = document.getElementById('phoneNumber');
        input.value = number;
        input.focus();
        onNumberInput();
        bootstrap.Modal.getInstance(document.getElementById('contactsModal'))?.hide();
    }
</script>
@endpush
