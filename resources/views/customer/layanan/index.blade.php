@extends('layouts.app')

@php
    $scope = $scope ?? 'pulsa';
    $pageTitle = match ($scope) {
        'voucher' => 'Voucher Data',
        'pln' => 'Token PLN',
        default => $category?->name ?? 'Pulsa',
    };
    $pageIcon = match ($scope) {
        'voucher' => 'bi-wifi',
        'pln' => 'bi-lightning-charge',
        default => 'bi-phone',
    };
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
    .operator-prefix-hint { font-size: .72rem; color: #64748b; }

    /* Kartu produk pulsa - grid minimalis */
    .pulsa-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
    @media (min-width: 768px) { .pulsa-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .pulsa-grid { grid-template-columns: repeat(4, 1fr); } }
    .pulsa-item {
        display: flex; flex-direction: column; justify-content: space-between; gap: .5rem;
        padding: .9rem .95rem;
        border: 1px solid #eef2f7; border-radius: .85rem;
        background: #fff;
        text-decoration: none; color: inherit;
        transition: all .15s;
        cursor: pointer;
    }
    .pulsa-item:hover { border-color: #c7d7fe; box-shadow: 0 6px 14px rgba(99,102,241,.12); transform: translateY(-2px); }
    .pulsa-operator { font-size: .7rem; color: #64748b; display: inline-flex; align-items: center; gap: .3rem; }
    .op-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .brand-mini {
        width: 16px; height: 16px; flex-shrink: 0;
        border-radius: 5px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .7rem; overflow: hidden;
    }
    .brand-mini img { width: 100%; height: 100%; object-fit: contain; }
    .pulsa-name {
        font-size: .72rem; color: #475569; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .pulsa-price { font-weight: 700; color: #3b82f6; display: block; }

    /* Bottom sheet */
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
    .bs-desc {
        font-size: .85rem; color: #475569;
        background: #f0f9ff; border: 1px solid #e0f2fe;
        border-radius: .6rem; padding: .6rem .75rem;
    }
    .bs-detail {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: .9rem;
        padding: .2rem .9rem;
    }
    .bs-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: .6rem 0; gap: .5rem;
    }
    .bs-row + .bs-row { border-top: 1px dashed #e2e8f0; }
    .bs-label { font-size: .85rem; color: #64748b; }
    .bs-value { font-size: .9rem; }

    /* Kontak */
    .contact-avatar {
        width: 40px; height: 40px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 50%;
    }

    /* Tab filter produk */
    .type-tab { flex: 1 1 auto; white-space: nowrap; }
    @media (max-width: 575.98px) { .type-tab { font-size: .72rem; padding: .25rem .4rem; } }

    /* Skeleton / shimmer saat produk dimuat */
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    .skeleton-box {
        position: relative; overflow: hidden;
        background: #eef2f7; border-radius: .6rem;
    }
    .skeleton-box::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.65), transparent);
        background-size: 400px 100%;
        animation: shimmer 1.3s infinite;
    }

    /* ===== Chips sub-brand paket data (Cuanku, Bebas Puas, Freedom, dll.) ===== */
    .sub-brand-chip {
        border: 1.5px solid #e2e8f0; background: #fff;
        border-radius: 999px;
        padding: .3rem .85rem;
        font-weight: 600; font-size: .78rem; color: #475569;
        transition: all .15s;
    }
    .sub-brand-chip:hover { border-color: #93c5fd; color: #1d4ed8; }
    .sub-brand-chip.active { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body py-2 px-3">
        <h6 class="fw-bold mb-2"><i class="bi {{ $pageIcon }} text-{{ $scope === 'pln' ? 'warning' : 'primary' }}"></i> {{ $pageTitle }}</h6>

        <div class="row g-1 align-items-center">
            <div class="col-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white"><i class="bi {{ $scope === 'pln' ? 'bi-lightning-charge text-warning' : 'bi-phone' }}"></i></span>
                    <input type="text" id="phoneNumber" class="form-control py-2 px-3"
                           placeholder="{{ $scope === 'pln' ? 'Nomor meter PLN (cth: 12345678901)' : '08xxxxxxxxxx' }}" inputmode="numeric" autocomplete="off"
                           oninput="onNumberInput()">
                    @if($scope === 'pln')
                    <button type="button" class="btn btn-outline-warning" id="btnCekId"
                            onclick="cekIdPln()" title="Cek nama pemilik ID pelanggan">
                        <i class="bi bi-search"></i>
                    </button>
                    @else
                    <button type="button" class="btn btn-outline-primary" id="btnOpenContacts"
                            onclick="openContacts()" title="Pilih dari kontak">
                        <i class="bi bi-person-lines-fill"></i>
                    </button>
                    @endif
                </div>
                <div class="operator-prefix-hint mt-2" id="numberHint"></div>
            </div>
            {{-- Indikator operator terdeteksi --}}
            <div class="col-12" id="detectedBar" style="display:none;">
                <div class="alert alert-{{ $scope === 'pln' ? 'warning' : 'success' }} alert-sm d-flex align-items-center gap-2 mb-0 mt-1 py-1 px-2">
                    <i class="bi {{ $scope === 'pln' ? 'bi-lightning-charge' : 'bi-broadcast' }}"></i>
                    <div>
                        <div class="small text-muted">{{ $scope === 'pln' ? 'ID Pelanggan' : 'Nomor' }} <strong id="detectedNumber"></strong></div>
                        <div class="small fw-bold">{{ $scope === 'pln' ? 'Layanan' : 'Operator' }}: <span id="detectedOperator"></span></div>
                    </div>
                </div>
            </div>
            {{-- Hasil cek ID pelanggan PLN --}}
            <div class="col-12" id="cekIdResult" style="display:none;"></div>

            @if($scope === 'pln')
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 mt-2 py-1 px-2" style="font-size:.78rem;">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span>Pastikan nomor meter (ID pelanggan) sudah benar. Token yang sudah terkirim tidak dapat dikembalikan.</span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- List produk --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        {{-- Tab filter Pulsa / SMS & Telepon / Masa Aktif / Paket Data (deteksi otomatis, tersembunyi awal) --}}
        <div class="d-flex flex-wrap gap-1 mb-3 w-100 d-none" id="typeTabs">
            <button type="button" class="btn btn-sm btn-primary active fw-semibold type-tab" data-kind="Pulsa" onclick="setFilter('Pulsa')"><i class="bi bi-phone"></i> Pulsa Reguler</button>
            <button type="button" class="btn btn-sm btn-outline-primary type-tab" data-kind="SMS & Telepon" onclick="setFilter('SMS & Telepon')"><i class="bi bi-chat-dots"></i> SMS & Telepon</button>
            <button type="button" class="btn btn-sm btn-outline-primary type-tab" data-kind="Masa Aktif" onclick="setFilter('Masa Aktif')"><i class="bi bi-hourglass-split"></i> Masa Aktif</button>
            <button type="button" class="btn btn-sm btn-outline-primary type-tab" data-kind="Paket Data" onclick="setFilter('Paket Data')"><i class="bi bi-wifi"></i> Paket Data</button>
        </div>

        {{-- Chips sub-brand paket data (scope 'voucher', mis. Axis Cuanku / XL Bebas Puas) --}}
        <div class="d-flex flex-wrap gap-1 mb-3 w-100 d-none" id="subBrandChips"></div>

        <div id="productList" class="row g-2">
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>
                Ketik nomor HP di atas untuk melihat produk.
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

{{-- Bottom sheet detail produk --}}
<div class="bottom-sheet-overlay" id="bsOverlay" onclick="closeSheet()"></div>
<div class="bottom-sheet" id="bsSheet">
    <div class="bs-handle"></div>
    <div id="bsBody"></div>
</div>
@endsection

@push('scripts')
<script>
    let debounceTimer = null;
    const PAGE_SCOPE = '{{ $scope }}'; // 'pulsa' | 'voucher' | 'pln'
    const PAGE_TITLE = '{{ $pageTitle }}';
    const EMPTY_HINT = PAGE_SCOPE === 'pln'
        ? 'Ketik nomor meter PLN di atas untuk melihat produk.'
        : 'Ketik nomor HP di atas untuk melihat produk.';

    function normalizeNumber() {
        let num = document.getElementById('phoneNumber').value.replace(/[^0-9]/g, '');
        if (num.startsWith('62')) num = '0' + num.slice(2);
        return num;
    }

    // Dipanggil setiap kali mengetik (with debounce)
    function onNumberInput() {
        clearTimeout(debounceTimer);
        hideTabs(); // tab baru muncul lagi setelah produk terdeteksi
        debounceTimer = setTimeout(async () => {
            const number = normalizeNumber();
            const hint = document.getElementById('numberHint');

            if (number.length < 8) {
                if (number.length === 0) {
                    // Input dikosongkan → kembali ke keadaan awal
                    hint.textContent = '';
                    clearFilterUI();
                    hideDetected();
                    document.getElementById('cekIdResult').style.display = 'none';
                    resetList(EMPTY_HINT);
                    return;
                }

                hint.textContent = PAGE_SCOPE === 'pln'
                    ? 'Nomor meter belum lengkap (' + number.length + ' digit — umumnya 11–12 digit).'
                    : 'Nomor belum lengkap (min 8 digit).';
                hideDetected();
                document.getElementById('productList').innerHTML =
                    '<div class="col-12 text-center py-4 text-muted">Melengkapi nomor...</div>';
                return;
            }

            hint.textContent = '';
            document.getElementById('detectedBar').style.display = 'none';

            // Token PLN: tampilkan jumlah digit meter sebagai pengingat
            if (PAGE_SCOPE === 'pln') {
                hint.textContent = number.length + ' digit' + (number.length < 11 ? ' — umumnya 11–12 digit' : '');
            }

            // Token PLN: tanpa deteksi operator, langsung tampilkan produk
            if (PAGE_SCOPE === 'pln') {
                showDetected(number, 'Token PLN');
                await loadProducts(number, 'PLN');
                return;
            }

            // Deteksi operator SENDIRI di client (tanpa request tambahan)
            const op = detectOperator(number);

            if (op) {
                hint.textContent = '';
                showDetected(number, op);
                await loadProducts(number, op);
            } else {
                hint.textContent = 'Operator nomor ini tidak dikenali.';
                hideTabs();
                document.getElementById('productList').innerHTML =
                    '<div class="col-12 text-center py-4 text-muted">Operator nomor ini tidak dikenali.</div>';
            }
        }, 400); // debounce 400ms agar tidak spam request saat mengetik
    }

    // Map prefix -> operator (sama dgn backend)
    const OPERATOR_PREFIXES = {
        'Telkomsel': ['0811','0812','0813','0821','0822','0823','0851','0852','0853'],
        'Indosat':   ['0814','0815','0816','0855','0856','0857','0858'],
        'XL':        ['0817','0818','0819','0859','0877','0878'],
        'AXIS':      ['0831','0832','0833','0837','0838'],
        'Three':     ['0895','0896','0897','0898','0899'],
        'Smartfren': ['0881','0882','0883','0884','0885','0886','0887','0888','0889'],
    };
    function detectOperator(num) {
        const p4 = num.slice(0, 4);
        for (const [op, prefixes] of Object.entries(OPERATOR_PREFIXES)) {
            if (prefixes.includes(p4)) return op;
        }
        return null;
    }

    // ============ CEK ID PELANGGAN PLN ============
    let cekIdPollingTimer = null;

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    async function cekIdPln() {
        console.log('[CekID] cekIdPln called');
        const number = normalizeNumber();
        const resultBox = document.getElementById('cekIdResult');
        const btn = document.getElementById('btnCekId');
        console.log('[CekID] number:', number, 'resultBox:', !!resultBox, 'btn:', !!btn);

        if (number.length < 8) {
            console.log('[CekID] Number too short:', number.length);
            resultBox.style.display = 'block';
            resultBox.innerHTML = '<div class="alert alert-warning py-2 small mb-0 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Masukkan nomor meter minimal 8 digit.</div>';
            return;
        }

        // Stop polling sebelumnya jika ada
        if (cekIdPollingTimer) { clearInterval(cekIdPollingTimer); cekIdPollingTimer = null; }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        resultBox.style.display = 'block';
        resultBox.innerHTML = '<div class="alert alert-info py-2 small mb-0 mt-1"><i class="bi bi-hourglass-split me-1"></i>Sedang mengecek nama pelanggan...</div>';

        try {
            const res = await fetch('{{ route('customer.token-pln.cek-id') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({ destination: number })
            });
            const data = await res.json();

            if (data.error) {
                resultBox.innerHTML = '<div class="alert alert-danger py-2 small mb-0 mt-1"><i class="bi bi-x-circle me-1"></i>' + data.error + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-search"></i>';
                return;
            }

            // Langsung success
            if (data.success && data.customer_name) {
                showCekIdResult(resultBox, data.customer_name, number, data.daya);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-search"></i>';
                return;
            }

            // Pending → mulai polling
            if (data.pending && data.ref_id) {
                startCekIdPolling(data.ref_id, number, resultBox, btn);
                return;
            }

            // Fallback
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i>';
        } catch (e) {
            console.error('[CekID] Error:', e);
            resultBox.innerHTML = '<div class="alert alert-danger py-2 small mb-0 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Gagal terhubung ke server. Coba lagi.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i>';
        }
    }

    function startCekIdPolling(refId, number, resultBox, btn) {
        let attempts = 0;
        const maxAttempts = 15; // max 15 x 2 detik = 30 detik

        cekIdPollingTimer = setInterval(async () => {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(cekIdPollingTimer);
                cekIdPollingTimer = null;
                resultBox.innerHTML = '<div class="alert alert-warning py-2 small mb-0 mt-1"><i class="bi bi-clock me-1"></i>Timeout — server belum merespon. Silakan coba lagi.</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-search"></i>';
                return;
            }

            try {
                const res = await fetch('{{ route('customer.token-pln.cek-id-result') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({ ref_id: refId })
                });
                const data = await res.json();

                if (data.success && data.customer_name) {
                    clearInterval(cekIdPollingTimer);
                    cekIdPollingTimer = null;
                    showCekIdResult(resultBox, data.customer_name, number, data.daya);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-search"></i>';
                } else if (data.error) {
                    clearInterval(cekIdPollingTimer);
                    cekIdPollingTimer = null;
                    resultBox.innerHTML = '<div class="alert alert-danger py-2 small mb-0 mt-1"><i class="bi bi-x-circle me-1"></i>' + data.error + '</div>';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-search"></i>';
                }
                // else: masih pending, lanjut polling
            } catch (e) {
                // Abaikan error jaringan, lanjut polling
            }
        }, 2000); // poll setiap 2 detik
    }

    function showCekIdResult(resultBox, name, number, daya) {
        const dayaHtml = daya ? '<div class="text-muted">Daya: ' + daya + '</div>' : '';
        resultBox.innerHTML = '<div class="alert alert-success py-2 small mb-0 mt-1 d-flex align-items-center gap-2">'
            + '<i class="bi bi-check-circle-fill text-success fs-5"></i>'
            + '<div><div class="fw-semibold">' + name + '</div>'
            + '<div class="text-muted">ID Pelanggan: ' + number + '</div>' + dayaHtml + '</div>'
            + '</div>';
    }

    // Simpan nomor terakhir yang diminta utk hindari hasil ganda (race)
    let requestedNumber = null;

    async function loadProducts(number, operator) {
        requestedNumber = number;
        currentOperator = PAGE_SCOPE === 'pln' ? 'Token PLN' : operator;
        hideTabs(); // tab tampil lagi setelah produk selesai dimuat
        const list = document.getElementById('productList');
        // Tampilkan skeleton shimmer saat memuat
        const grid = document.createElement('div');
        grid.className = 'pulsa-grid';
        for (let i = 0; i < 8; i++) {
            const card = document.createElement('div');
            card.className = 'pulsa-item'; // reuse kartu produk biar ukurannya sama
            card.innerHTML =
                '<div class="skeleton-box" style="height:14px;width:70%"></div>' +
                '<div class="skeleton-box" style="height:16px;width:45%"></div>';
            grid.appendChild(card);
        }
        list.innerHTML = '';
        list.appendChild(grid);

        try {
            const res = await fetch('{{ route('customer.pulsa.products') }}' + '?scope=' + PAGE_SCOPE + '&number=' + encodeURIComponent(number) + '&operator=' + encodeURIComponent(operator), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            // Abaikan jika user sudah mengetik nomor lain
            if (requestedNumber !== number) return;

            if (!data.products || data.products.length === 0) {
                clearFilterUI();
                list.innerHTML = '<div class="col-12 text-center py-4 text-muted">' +
                    (PAGE_SCOPE === 'pln' ? 'Tidak ada produk '+PAGE_TITLE+' tersedia.' : 'Tidak ada produk untuk operator ini.') +
                    '</div>';
                return;
            }

            // Simpan produk utk keperluan filter tab
            currentProducts = data.products;
            currentSubBrands = data.sub_brands || [];
            currentSubBrand = 'all';
            renderProducts();
        } catch (e) {
            if (requestedNumber === number) {
                list.innerHTML = '<div class="col-12 text-center py-4 text-muted">Gagal memuat produk.</div>';
            }
        }
    }

    // State untuk tab filter (Pulsa / Paket Data)
    let currentProducts = [];
    let currentFilter = PAGE_SCOPE === 'pulsa' ? 'Pulsa' : ''; // default tampilkan Pulsa dulu
    let currentOperator = PAGE_SCOPE === 'pln' ? 'Token PLN' : ''; // operator untuk label kartu

    // State sub-brand paket data (scope 'voucher'): chips di atas grid produk
    let currentSubBrands = [];
    let currentSubBrand = 'all';

    // Label tampilan utk tipe layanan (nilai internal tetap 'Pulsa')
    function typeLabel(kind) {
        if (kind === 'Pulsa') return 'Pulsa Reguler';
        if (PAGE_SCOPE === 'voucher') return 'Voucher Data';
        return kind || '';
    }

    // Urutan & ikon tab di halaman Pulsa
    const TAB_KINDS = ['Pulsa', 'SMS & Telepon', 'Masa Aktif', 'Paket Data'];
    const TAB_LABELS = { 'Pulsa': 'Pulsa Reguler', 'SMS & Telepon': 'SMS & Telepon', 'Masa Aktif': 'Masa Aktif', 'Paket Data': 'Paket Data' };
    const TAB_ICONS = { 'Pulsa': 'bi-phone', 'SMS & Telepon': 'bi-chat-dots', 'Masa Aktif': 'bi-hourglass-split', 'Paket Data': 'bi-wifi' };

    function productKind(p) {
        return p.kind || p.type_label || 'Pulsa';
    }

    // HTML icon brand (gambar/font) utk kartu produk. Fallback ke titik warna.
    function brandMiniHtml(p) {
        if (p.brand_image) {
            return '<span class="brand-mini"><img src="' + p.brand_image + '" alt=""></span>';
        }
        if (p.brand_icon) {
            const color = p.brand_color || '#3b82f6';
            return '<span class="brand-mini" style="background:' + color + ';color:#fff;"><i class="bi bi-' + p.brand_icon + '"></i></span>';
        }
        return '<span class="op-dot" style="background:' + (p.operator_color || '#3b82f6') + '"></span>';
    }

    function renderProducts() {
        const list = document.getElementById('productList');
        list.innerHTML = '';

        // Tab Pulsa/Paket Data hanya untuk halaman Pulsa; halaman lain satu kategori
        const tabs = document.getElementById('typeTabs');
        if (PAGE_SCOPE === 'pulsa') {
            tabs.classList.toggle('d-none', currentProducts.length === 0);
        } else {
            tabs.classList.add('d-none');
        }

        // Hitung jumlah produk per jenis untuk label tab
        const counts = { 'Pulsa': 0, 'SMS & Telepon': 0, 'Masa Aktif': 0, 'Paket Data': 0 };
        currentProducts.forEach(p => {
            const k = productKind(p);
            counts[k] = (counts[k] || 0) + 1;
        });

        if (PAGE_SCOPE === 'pulsa') {
            TAB_KINDS.forEach(kind => {
                const btn = tabs.querySelector('[data-kind="' + kind + '"]');
                if (btn) {
                    btn.innerHTML = '<i class="bi ' + (TAB_ICONS[kind] || 'bi-box') + '"></i> ' +
                        (TAB_LABELS[kind] || kind) + ' (' + (counts[kind] || 0) + ')';
                }
            });

            // Auto-switch: jika tab aktif kosong, pindah ke tab pertama yang punya produk
            if (!counts[currentFilter]) {
                const next = TAB_KINDS.find(k => counts[k] > 0);
                if (next) currentFilter = next;
            }
            syncTabButtons();
        }

        const shown = currentFilter
            ? currentProducts.filter(p => productKind(p) === currentFilter)
            : currentProducts;

        // Chips sub-brand — halaman Voucher Data, atau tab Paket Data di menu Pulsa.
        // Selalu sembunyikan saat tidak aktif (mis. pindah tab lain) supaya tidak tertinggal.
        const subBrandsEl = document.getElementById('subBrandChips');
        const showSubBrandChips = PAGE_SCOPE === 'voucher' || PAGE_SCOPE === 'pln' || (PAGE_SCOPE === 'pulsa' && currentFilter === 'Paket Data');
        if (subBrandsEl) {
            if (showSubBrandChips && currentSubBrands.length > 1) {
                subBrandsEl.classList.remove('d-none');
                subBrandsEl.innerHTML = [
                    '<button type="button" class="btn btn-sm sub-brand-chip ' + (currentSubBrand === 'all' ? 'active' : '') + '" data-sub="all" onclick="setSubBrand(\'all\')">Semua</button>',
                    ...currentSubBrands.map(b =>
                        '<button type="button" class="btn btn-sm sub-brand-chip ' + (currentSubBrand === b ? 'active' : '') + '" data-sub="' + b + '" onclick="setSubBrand(\'' + b.replace(/'/g, "\\'") + '\')">' + b + '</button>'
                    )
                ].join('');
            } else {
                subBrandsEl.classList.add('d-none');
            }
        }

        // Filter sub-brand aktif (klien-side; produk sudah diambil semua per operator)
        let shownSub = shown;
        if ((PAGE_SCOPE === 'voucher' || PAGE_SCOPE === 'pulsa' || PAGE_SCOPE === 'pln') && currentSubBrand !== 'all') {
            shownSub = shown.filter(p => p.sub_brand === currentSubBrand);
        }

        if (shownSub.length === 0) {
            const label = PAGE_SCOPE === 'pulsa' ? typeLabel(currentFilter) : PAGE_TITLE;
            list.innerHTML = '<div class="col-12 text-center py-4 text-muted">Tidak ada produk '+label+' untuk operator ini.</div>';
            return;
        }

        // Kelompokkan sesuai tab aktif: kalau filter aktif, satu group; kalau semua, beberapa group
        const groups = {};
        shownSub.forEach(p => {
            const key = productKind(p);
            (groups[key] = groups[key] || []).push(p);
        });

        // Judul section disembunyikan saat tab filter tampil (hindari dobel dengan tab)
        const hideSectionTitle = PAGE_SCOPE === 'pulsa' && currentProducts.length > 0;

        Object.keys(groups).forEach(kind => {
            const section = document.createElement('div');
            section.className = 'mb-4';
            if (! hideSectionTitle) {
                const title = document.createElement('div');
                title.className = 'fw-bold small text-uppercase text-muted d-flex align-items-center gap-2 mb-2';
                title.innerHTML = `<i class="bi ${kind === 'Paket Data' ? 'bi-wifi' : 'bi-phone'}"></i> ${typeLabel(kind)}`;
                section.appendChild(title);
            }

            const grid = document.createElement('div');
            grid.className = 'pulsa-grid';
            groups[kind].forEach(p => {
                const el = document.createElement('div');
                el.className = 'pulsa-item';
                el.onclick = () => openSheet(p);
                el.innerHTML = `
                    <span class="pulsa-name">${p.name}</span>
                    <span class="pulsa-operator">${brandMiniHtml(p)}${currentOperator}</span>
                    <span class="pulsa-price">Rp ${Number(p.sell_price).toLocaleString('id-ID')}</span>
                `;
                grid.appendChild(el);
            });
            section.appendChild(grid);
            list.appendChild(section);
        });
    }

    // Sinkronkan gaya tombol tab dengan tab yang aktif
    function syncTabButtons() {
        document.querySelectorAll('#typeTabs .btn').forEach(b => {
            const on = b.dataset.kind === currentFilter;
            b.classList.toggle('btn-primary', on);
            b.classList.toggle('active', on);
            b.classList.toggle('btn-outline-primary', !on);
        });
    }

    // Sembunyikan tab filter (muncul kembali setelah produk terdeteksi)
    function hideTabs() {
        document.getElementById('typeTabs').classList.add('d-none');
    }

    function setFilter(kind) {
        currentFilter = kind;
        // Ganti tab → kembalikan filter sub-brand ke "Semua"
        if (PAGE_SCOPE === 'pulsa') currentSubBrand = 'all';
        syncTabButtons();
        renderProducts();
    }

    // Filter sub-brand paket data (scope 'voucher')
    function setSubBrand(brand) {
        currentSubBrand = brand;
        renderProducts();
    }

    function clearFilterUI() {
        currentProducts = [];
        currentSubBrands = [];
        currentSubBrand = 'all';
        const chips = document.getElementById('subBrandChips');
        if (chips) chips.classList.add('d-none');
        currentFilter = PAGE_SCOPE === 'pulsa' ? 'Pulsa' : '';
        hideTabs();
        syncTabButtons();
    }

    function showDetected(number, operator) {
        document.getElementById('detectedNumber').textContent = number;
        document.getElementById('detectedOperator').textContent = operator;
        document.getElementById('detectedBar').style.display = 'block';
    }

    function hideDetected() {
        document.getElementById('detectedBar').style.display = 'none';
    }

    function resetList(msg) {
        document.getElementById('productList').innerHTML =
            '<div class="col-12 text-center py-5 text-muted">' +
            '<i class="bi bi-hand-index-thumb fs-1 d-block mb-2"></i>' + msg + '</div>';
    }

    // ============ BOTTOM SHEET ============
    function formatRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function openSheet(product) {
        const number = normalizeNumber();
        const operator = PAGE_SCOPE === 'pln' ? 'Token PLN' : (detectOperator(number) || product.operator || '-');
        const saldo = {{ auth()->user()->saldo ?? 0 }};

        // Tampilkan deskripsi asli OkeConnect (hanya jika berbeda dari judul)
        const hasDesc = product.description && product.description.trim() && product.description !== product.name;

        const body = document.getElementById('bsBody');
        body.innerHTML = `
            <div class="bs-title mb-3">
                <div class="fw-bold fs-5">${product.name}</div>
                ${hasDesc
                    ? `<div class="bs-desc mt-2"><i class="bi bi-info-circle me-1 text-primary"></i>${product.description}</div>`
                    : ''}
            </div>

            <div class="bs-detail mb-3">
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-phone me-1"></i>${PAGE_SCOPE === 'pln' ? 'ID Pelanggan' : 'Nomor Tujuan'}</span>
                    <span class="bs-value fw-semibold" id="bsNumber">${number}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label"><i class="bi bi-broadcast me-1"></i>${PAGE_SCOPE === 'pln' ? 'Layanan' : 'Operator'}</span>
                    <span class="bs-value">${operator}</span>
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

    async function submitOrder(productId, number) {
        const btn = document.getElementById('bsSubmitBtn');
        const alertBox = document.getElementById('bsAlert');

        if (!number || number.length < 8) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Nomor tujuan tidak valid. Cek kembali nomor di atas.</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
        alertBox.innerHTML = '';

        try {
            const res = await fetch('/order/' + productId,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ destination: number })
                }
            );
            const data = await res.json();

            if (!res.ok) {
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">' + (data.error || 'Gagal membuat order.') + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bag-check"></i> Proses Pembelian';
                return;
            }

            // Sukses → tampilkan hasil & tutup sheet
            alertBox.innerHTML = '<div class="alert alert-success py-2 small mb-2">' + data.message + '</div>';
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Order dibuat';

            setTimeout(() => {
                closeSheet();
                const list = document.getElementById('productList');
                list.innerHTML = '<div class="col-12 text-center py-5">' +
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
        // Tampilkan kontak tersimpan lebih dulu
        renderSavedContacts();
        var modal = new bootstrap.Modal(document.getElementById('contactsModal'));
        modal.show();
    }

    function renderSavedContacts() {
        const list = document.getElementById('contactsList');
        const saved = getSavedContacts();

        let html = '<div class="small fw-semibold text-muted mb-2">Kontak Tersimpan (' + saved.length + ')</div>';
        if (saved.length === 0) {
            html += '<div class="text-center py-4 text-muted">' +
                '<i class="bi bi-person-plus fs-1 d-block mb-2"></i>' +
                'Belum ada kontak tersimpan. Tekan "Kontak Tersimpan" untuk menambah.' +
                '</div>';
        } else {
            saved.forEach((c, i) => {
                html += `<button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 w-100 text-start border rounded mb-2 p-2" onclick="pickContact('${c.number}')">
                    <span class="contact-avatar">${(c.name||'?').charAt(0).toUpperCase()}</span>
                    <span class="flex-grow-1">
                        <span class="d-block fw-semibold">${(c.name||'Tanpa Nama').replaceAll('<','&lt;')}</span>
                        <span class="small text-muted">${c.number}</span>
                    </span>
                    <span class="text-muted"><i class="bi bi-chevron-right"></i></span>
                </button>`;
            });
        }
        list.innerHTML = html;
        window._contactsModalData = { type: 'saved' };
    }

    function renderAddContact() {
        const list = document.getElementById('contactsList');
        list.innerHTML = `
            <div class="small fw-semibold text-muted mb-2">Tambah Kontak Baru</div>
            <div class="mb-2">
                <label class="form-label small">Nama</label>
                <input type="text" class="form-control" id="newContactName" placeholder="Nama kontak">
            </div>
            <div class="mb-3">
                <label class="form-label small">Nomor HP</label>
                <input type="text" class="form-control" id="newContactNumber" placeholder="08xxxxxxxxxx" inputmode="numeric">
            </div>
            <button type="button" class="btn btn-success w-100" onclick="saveContact()">
                <i class="bi bi-check-lg"></i> Simpan Kontak
            </button>
        `;
        window._contactsModalData = { type: 'add' };
    }

    function showAddContact() {
        renderAddContact();
    }

    function saveContact() {
        const name = document.getElementById('newContactName').value.trim();
        let number = document.getElementById('newContactNumber').value.replace(/[^0-9]/g, '');
        if (number.startsWith('62')) number = '0' + number.slice(2);

        if (number.length < 8) {
            alert('Nomor HP tidak valid.');
            return;
        }

        const contacts = getSavedContacts();
        contacts.push({ name: name || 'Tanpa Nama', number: number });
        localStorage.setItem(CONTACTS_KEY, JSON.stringify(contacts));
        renderSavedContacts();
    }

    async function loadDeviceContacts() {
        const list = document.getElementById('contactsList');

        // Web Contacts API (didukung Android Chrome, secure context)
        if (navigator.contacts && navigator.contacts.select) {
            try {
                const props = ['name', 'tel'];
                const contacts = await navigator.contacts.select(props, { multiple: true });
                renderDeviceContacts(contacts);
                return;
            } catch (e) {
                // user menolak izin / batal
            }
        }

        // Fallback: tampilkan kontak tersimpan
        renderSavedContacts();
        alert('Perangkat/browser tidak mendukung akses kontak. Gunakan daftar Kontak Tersimpan di bawah.');
    }

    function renderDeviceContacts(contacts) {
        const list = document.getElementById('contactsList');
        let html = '<div class="small fw-semibold text-muted mb-2">Kontak Perangkat</div>';
        const withTel = contacts.filter(c => c.tel && c.tel.length);
        if (withTel.length === 0) {
            html += '<div class="text-center py-4 text-muted">Tidak ada kontak dengan nomor telepon.</div>';
        } else {
            withTel.forEach(c => {
                const tel = (c.tel[0] || '').replace(/[^0-9]/g, '');
                if (!tel) return;
                const n = tel.startsWith('62') ? '0' + tel.slice(2) : tel;
                html += `<button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 w-100 text-start border rounded mb-2 p-2" onclick="pickContact('${n}')">
                    <span class="contact-avatar">${(c.name||'?').charAt(0).toUpperCase()}</span>
                    <span class="flex-grow-1">
                        <span class="d-block fw-semibold">${(c.name||'Tanpa Nama').replaceAll('<','&lt;')}</span>
                        <span class="small text-muted">${n}</span>
                    </span>
                    <span class="text-muted"><i class="bi bi-chevron-right"></i></span>
                </button>`;
            });
        }
        list.innerHTML = html;
        window._contactsModalData = { type: 'device' };
    }

    function pickContact(number) {
        // Isi input & tutup modal
        const input = document.getElementById('phoneNumber');
        input.value = number;
        input.focus();
        onNumberInput();

        var modal = bootstrap.Modal.getInstance(document.getElementById('contactsModal'));
        if (modal) modal.hide();
    }

    // Load awal (page load) — kosong
    resetList(EMPTY_HINT);
</script>
@endpush