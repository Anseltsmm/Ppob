@extends('layouts.app')

@section('title', 'Top Up Game')

@push('styles')
<style>
    /* ===== Tile brand game ===== */
    .game-brand {
        display: flex; flex-direction: column; align-items: center; gap: .35rem;
        padding: .85rem .5rem;
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 1rem;
        cursor: pointer;
        transition: all .15s;
        height: 100%;
    }
    .game-brand:hover { border-color: #c7d7fe; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(99,102,241,.12); }
    .game-brand.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .game-logo {
        width: 46px; height: 46px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 1.05rem;
        box-shadow: 0 4px 10px rgba(0,0,0,.14);
    }
    .game-name { font-weight: 700; font-size: .8rem; text-align: center; line-height: 1.2; }

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
    .game-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    @media (min-width: 768px) { .game-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .game-grid { grid-template-columns: repeat(4, 1fr); } }
    .game-item {
        display: flex; flex-direction: column; justify-content: space-between; gap: .5rem;
        padding: .9rem .95rem;
        border: 1px solid #eef2f7; border-radius: .85rem;
        background: #fff; text-decoration: none; color: inherit;
        transition: all .15s; cursor: pointer;
    }
    .game-item:hover { border-color: #c7d7fe; box-shadow: 0 6px 14px rgba(99,102,241,.12); transform: translateY(-2px); }
    .game-item .game-brand-label { font-size: .7rem; color: #64748b; display: block; }
    .game-item .game-name {
        font-size: .72rem; color: #475569; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .game-item .game-price { font-weight: 700; color: #3b82f6; display: block; }

    .game-opendenom {
        border: 1.5px dashed #a78bfa; background: linear-gradient(180deg, #f5f3ff, #fff);
        display: flex; flex-direction: column; gap: .4rem;
        padding: 1rem 1.1rem; border-radius: 1rem; cursor: pointer;
        transition: all .15s;
    }
    .game-opendenom:hover { border-color: #7c3aed; box-shadow: 0 6px 14px rgba(124,58,237,.14); }
    .game-opendenom .go-title { font-weight: 700; font-size: .95rem; color: #6d28d9; }
    .game-opendenom .go-sub { font-size: .78rem; color: #64748b; }

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

    .denom-chip {
        border: 1.5px solid #e2e8f0; background: #fff;
        border-radius: .65rem; padding: .45rem .7rem;
        font-weight: 600; font-size: .82rem; color: #334155;
        cursor: pointer; transition: all .15s;
    }
    .denom-chip:hover { border-color: #93c5fd; }
    .denom-chip.active { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
</style>
@endpush

@section('content')
@php
    $palette = ['#6366f1', '#ef4444', '#f59e0b', '#10b981', '#ec4899', '#06b6d4', '#8b5cf6', '#f97316', '#14b8a6', '#3b82f6'];
@endphp
{{-- ============ STEP 1: PILIH GAME ============ --}}
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="step-label"><i class="bi bi-1-circle"></i> Pilih Game</span>
            <span class="step-label disabled" id="step2Label"><i class="bi bi-2-circle"></i> User ID</span>
            <span class="step-label disabled" id="step3Label"><i class="bi bi-3-circle"></i> Nominal</span>
        </div>
        <h6 class="fw-bold mb-1"><i class="bi bi-joystick text-primary"></i> Top Up Game</h6>
        <p class="small text-muted mb-3">Pilih game, lalu masukkan User ID / ID pemain Anda.</p>

        @if($brands->isNotEmpty())
            <div class="row g-2">
                @foreach($brands as $brand => $count)
                <div class="col-4 col-md-3 col-lg-2">
                    <button type="button" class="game-brand w-100" data-brand="{{ $brand }}" onclick="selectBrand('{{ $brand }}')">
                        <span class="game-logo" style="background: {{ $palette[$loop->index % count($palette)] }};">{{ strtoupper(substr($brand, 0, 1)) }}</span>
                        <span class="game-name">{{ $brand }}</span>
                    </button>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-joystick fs-1 d-block mb-2"></i>
                Belum ada produk game tersedia.
            </div>
        @endif
    </div>
</div>

{{-- ============ STEP 2: USER ID ============ --}}
<div class="card border-0 shadow-sm mb-3 d-none" id="userIdCard">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label"><i class="bi bi-2-circle"></i> User ID</span>
            <span class="small text-muted">ID pemain <strong id="userIdBrandLabel"></strong></span>
        </div>
        <div class="row g-1 align-items-center">
            <div class="col-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white"><i class="bi bi-person-vcard"></i></span>
                    <input type="text" id="userId" class="form-control py-2 px-3" placeholder="Contoh: 81234567" autocomplete="off" oninput="onUserIdInput()">
                </div>
                <div class="small text-muted mt-1" id="userIdHint"></div>
            </div>
        </div>
    </div>
</div>

{{-- ============ STEP 3: NOMINAL ============ --}}
<div class="card border-0 shadow-sm d-none" id="productCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label"><i class="bi bi-3-circle"></i> Pilih Nominal</span>
        </div>
        <div id="productList">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>
                Masukkan User ID untuk melihat nominal.
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

    // ============ PILIH GAME ============
    function selectBrand(brand) {
        selectedBrand = brand;

        document.querySelectorAll('.game-brand').forEach(function (b) {
            b.classList.toggle('active', b.dataset.brand === brand);
        });

        document.getElementById('userIdBrandLabel').textContent = brand;
        document.getElementById('userIdCard').classList.remove('d-none');
        document.getElementById('userId').focus();

        const userId = normalizeUserId();
        if (userId.length >= 4) onUserIdInput();
        else resetProducts();
    }

    // ============ USER ID ============
    function normalizeUserId() {
        return document.getElementById('userId').value.trim();
    }

    function onUserIdInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async function () {
            const userId = normalizeUserId();
            const hint = document.getElementById('userIdHint');

            if (userId.length === 0) {
                hint.textContent = '';
                resetProducts();
                return;
            }
            if (userId.length < 4) {
                hint.textContent = 'User ID minimal 4 karakter.';
                resetProducts();
                return;
            }
            if (!/^[a-zA-Z0-9-]+$/.test(userId)) {
                hint.textContent = 'User ID hanya boleh huruf, angka, dan tanda hubung (-).';
                resetProducts();
                return;
            }

            hint.textContent = '';
            await loadProducts(userId);
        }, 400);
    }

    // ============ AMBIL PRODUK ============
    async function loadProducts(userId) {
        if (!selectedBrand) return;

        const key = selectedBrand + '|' + userId;
        requestedKey = key;

        const list = document.getElementById('productList');
        document.getElementById('productCard').classList.remove('d-none');
        list.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Memuat produk...</div>';

        try {
            const res = await fetch('{{ route('customer.game.products') }}' + '?brand=' + encodeURIComponent(selectedBrand) + '&user_id=' + encodeURIComponent(userId), {
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

        openDenom.forEach(function (p) {
            const card = document.createElement('div');
            card.className = 'game-opendenom mb-3';
            card.onclick = () => openSheet(p);
            card.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span class="go-title"><i class="bi bi-pencil-square me-1"></i>${p.name}</span>
                    <i class="bi bi-chevron-right text-primary"></i>
                </div>
                <span class="go-sub">Isi nominal sendiri (min Rp ${Number(p.min_nominal).toLocaleString('id-ID')} — maks Rp ${Number(p.max_nominal).toLocaleString('id-ID')}) + biaya admin</span>
            `;
            list.appendChild(card);
        });

        if (fixed.length) {
            const grid = document.createElement('div');
            grid.className = 'game-grid';
            fixed.forEach(function (p) {
                const el = document.createElement('div');
                el.className = 'game-item';
                el.onclick = () => openSheet(p);
                el.innerHTML = `
                    <span class="game-name">${p.name}</span>
                    <span class="game-brand-label">${p.brand || selectedBrand}</span>
                    <span class="game-price">Rp ${Number(p.sell_price).toLocaleString('id-ID')}</span>
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
        const userId = normalizeUserId();
        const saldo = {{ auth()->user()->saldo ?? 0 }};
        const isOpen = product.type === 'opendenom';
        const hasDesc = product.description && product.description.trim() && product.description !== product.name;

        let priceHtml = '';
        if (isOpen) {
            const candidates = [10000, 20000, 25000, 50000, 100000, 200000, 500000, 1000000];
            const chips = candidates.filter(c => c >= (product.min_nominal || 0) && c <= (product.max_nominal || 9999999));
            const chipsHtml = chips.length
                ? chips.map(c => `<button type="button" class="denom-chip" data-nominal="${c}" onclick="setNominal(this)">${formatRupiah(c)}</button>`).join('')
                : '';

            priceHtml = `
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-pencil-square me-1"></i>Nominal</span>
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
                    <span class="bs-label"><i class="bi bi-joystick me-1"></i>Game</span>
                    <span class="bs-value fw-semibold">${selectedBrand}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-person-vcard me-1"></i>User ID</span>
                    <span class="bs-value fw-semibold" id="bsUserId">${userId}</span>
                </div>
                ${priceHtml}
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-wallet2 me-1"></i>Sisa Saldo</span>
                    <span class="bs-value ${saldo >= (isOpen ? 0 : product.sell_price) ? '' : 'text-danger fw-semibold'}">${formatRupiah(saldo)}</span>
                </div>
            </div>
            <div id="bsAlert"></div>
            <button class="btn btn-primary w-100 btn-lg fw-semibold" id="bsSubmitBtn" onclick="submitOrder(${product.id})">
                <i class="bi bi-bag-check"></i> Proses Pembelian
            </button>
        `;

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
        const userId = document.getElementById('bsUserId').textContent;
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

        if (userId.length < 4) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">User ID tidak valid.</div>';
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
                body: JSON.stringify({ destination: userId, qty: qty })
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
</script>
@endpush
