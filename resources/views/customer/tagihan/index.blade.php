@extends('layouts.app')

@section('title', 'Tagihan & Pascabayar')

@push('styles')
<style>
    /* ===== Tile jenis layanan ===== */
    .bill-type {
        display: flex; flex-direction: column; align-items: center; gap: .4rem;
        padding: .9rem .5rem;
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 1rem;
        cursor: pointer;
        transition: all .15s;
        height: 100%;
    }
    .bill-type:hover { border-color: #c7d7fe; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(99,102,241,.12); }
    .bill-type.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .bill-type-icon {
        width: 46px; height: 46px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0,0,0,.14);
    }
    .bill-type-name { font-weight: 700; font-size: .78rem; text-align: center; line-height: 1.2; }
    .bill-type-count { font-size: .66rem; color: #94a3b8; }

    /* ===== Step header ===== */
    .step-label {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: #2563eb; background: #eff6ff;
        border-radius: 999px; padding: .25rem .65rem;
        white-space: nowrap;
    }
    .step-label.done { background: #f0fdf4; color: #16a34a; }
    .step-label.disabled { background: #f1f5f9; color: #94a3b8; }

    /* ===== Daftar biller ===== */
    .biller-group-title {
        font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
        color: #64748b; margin: 1rem 0 .4rem;
    }
    .biller-item {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .7rem .85rem;
        background: #fff;
        border: 1px solid #eef2f7; border-radius: .8rem;
        cursor: pointer; transition: all .15s;
    }
    .biller-item:hover { border-color: #c7d7fe; box-shadow: 0 4px 10px rgba(99,102,241,.1); }
    .biller-item.active { border-color: #2563eb; background: #eff6ff; }

    /* ===== Hasil inquiry ===== */
    .inquiry-result { border: 1.5px dashed #60a5fa; background: linear-gradient(180deg, #f0f7ff, #fff); border-radius: 1rem; }
    .inquiry-nominal { font-size: 1.5rem; font-weight: 800; color: #1d4ed8; }

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
    .bs-desc { font-size: .85rem; color: #475569; background: #f0f9ff; border: 1px solid #e0f2fe; border-radius: .6rem; padding: .6rem .75rem; }
    .bs-detail { background: #f8fafc; border: 1px solid #eef2f7; border-radius: .9rem; padding: .2rem .9rem; }
    .bs-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem 0; gap: .5rem; }
    .bs-row + .bs-row { border-top: 1px dashed #e2e8f0; }
    .bs-label { font-size: .85rem; color: #64748b; }
    .bs-value { font-size: .9rem; }
</style>
@endpush

@section('content')
{{-- ============ STEP 1: PILIH JENIS LAYANAN ============ --}}
<div class="card border-0 shadow-sm mb-3 mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-1 overflow-auto">
            <span class="step-label"><i class="bi bi-1-circle"></i> Jenis</span>
            <span class="step-label disabled" id="step2Label"><i class="bi bi-2-circle"></i> Biller</span>
            <span class="step-label disabled" id="step3Label"><i class="bi bi-3-circle"></i> ID</span>
            <span class="step-label disabled" id="step4Label"><i class="bi bi-4-circle"></i> Bayar</span>
        </div>
        <h6 class="fw-bold mb-1"><i class="bi bi-receipt text-primary"></i> Tagihan & Pascabayar</h6>
        <p class="small text-muted mb-3">Bayar tagihan PLN, PDAM, PBB, TV, BPJS, dan lainnya. Cek tagihan gratis.</p>

        @if(! empty($types))
            <div class="row g-2" id="typeGrid">
                @foreach($types as $type => $groups)
                <div class="col-4 col-md-3 col-lg-2">
                    <button type="button" class="bill-type w-100" data-type="{{ $type }}" onclick="selectType('{{ $type }}')">
                        <span class="bill-type-icon" style="background: {{ \App\Support\BillTypes::color($type) }};">
                            <i class="bi {{ \App\Support\BillTypes::icon($type) }}"></i>
                        </span>
                        <span class="bill-type-name">{{ $type }}</span>
                        <span class="bill-type-count">{{ collect($groups)->flatten(1)->count() }} layanan</span>
                    </button>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                Belum ada produk tagihan tersedia.
            </div>
        @endif
    </div>
</div>

{{-- ============ STEP 2: PILIH BILLER ============ --}}
<div class="card border-0 shadow-sm mb-3 d-none" id="billerCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label" id="billerStepLabel"><i class="bi bi-2-circle"></i> Pilih Biller</span>
            <span class="small text-muted">Layanan <strong id="billerTypeLabel"></strong></span>
        </div>
        <div class="input-group mb-2">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="billerSearch" class="form-control" placeholder="Cari biller (mis. PDAM Gresik, PLN)..." oninput="filterBillers()">
        </div>
        <div id="billerList"></div>
    </div>
</div>

{{-- ============ STEP 3: ID PELANGGAN + CEK TAGIHAN ============ --}}
<div class="card border-0 shadow-sm mb-3 d-none" id="idCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label" id="idStepLabel"><i class="bi bi-3-circle"></i> ID Pelanggan</span>
            <span class="small text-muted"><strong id="idBillerLabel"></strong></span>
        </div>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white"><i class="bi bi-person-vcard"></i></span>
            <input type="text" id="customerId" class="form-control py-2 px-3" placeholder="Masukkan ID pelanggan / nomor meter" autocomplete="off" oninput="onIdInput()">
        </div>
        <div class="small text-muted mt-1" id="idHint"></div>
        <button type="button" class="btn btn-primary w-100 fw-semibold mt-3" id="btnInquiry" onclick="cekTagihan()">
            <i class="bi bi-search"></i> Cek Tagihan
        </button>
    </div>
</div>

{{-- ============ STEP 4: HASIL + BAYAR ============ --}}
<div class="card border-0 shadow-sm mb-3 d-none" id="resultCard">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="step-label" id="resultStepLabel"><i class="bi bi-4-circle"></i> Konfirmasi Tagihan</span>
        </div>
        <div class="inquiry-result p-3 mb-3" id="inquiryResult"></div>
        <button type="button" class="btn btn-success w-100 btn-lg fw-semibold" id="btnPay" onclick="openPaySheet()">
            <i class="bi bi-bag-check"></i> Bayar Sekarang
        </button>
    </div>
</div>

{{-- Info penting --}}
<div class="alert alert-warning small py-2 mb-2 d-none" id="infoStrip">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Pastikan ID pelanggan sudah benar. Tagihan yang sudah dibayar tidak dapat dikembalikan.
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
    const TYPES = @json(array_keys($types));
    const BILLERS = @json($billers);

    // Hint ID per jenis layanan
    const ID_HINTS = {
        'PLN': { placeholder: 'Nomor meter / ID pelanggan PLN (11-12 digit)', hint: 'Masukkan nomor meter / ID pelanggan PLN' },
        'PDAM': { placeholder: 'No. langganan / ID pelanggan PDAM', hint: 'Masukkan nomor langganan PDAM' },
        'PBB': { placeholder: 'NOP / Nomor Objek Pajak', hint: 'Masukkan NOP (Nomor Objek Pajak)' },
        'TV & Internet': { placeholder: 'Nomor pelanggan / ID', hint: 'Masukkan nomor pelanggan' },
        'BPJS & Asuransi': { placeholder: 'Nomor peserta', hint: 'Masukkan nomor peserta' },
        'Finance': { placeholder: 'Nomor kontrak / kartu', hint: 'Masukkan nomor kontrak / kartu' },
        'Gas & Energi': { placeholder: 'Nomor pelanggan', hint: 'Masukkan nomor pelanggan' },
        'Telepon': { placeholder: 'Nomor telepon', hint: 'Masukkan nomor telepon' },
        'Lainnya': { placeholder: 'ID pelanggan', hint: 'Masukkan ID pelanggan' },
    };

    let selectedType = null;
    let selectedBiller = null;
    let inquiryData = null;

    // ============ PILIH JENIS ============
    function selectType(type) {
        selectedType = type;

        document.querySelectorAll('.bill-type').forEach(function (t) {
            t.classList.toggle('active', t.dataset.type === type);
        });

        document.getElementById('step2Label').classList.remove('disabled');
        document.getElementById('billerTypeLabel').textContent = type;
        document.getElementById('billerCard').classList.remove('d-none');
        document.getElementById('infoStrip').classList.remove('d-none');

        renderBillers(type, '');
        document.getElementById('billerSearch').value = '';
        document.getElementById('billerSearch').focus();
    }

    function renderBillers(type, query) {
        const list = document.getElementById('billerList');
        const q = query.toLowerCase().trim();
        const items = (BILLERS[type] || []).filter(function (b) {
            return !q || (b.name + ' ' + b.operator).toLowerCase().includes(q);
        });

        if (items.length === 0) {
            list.innerHTML = '<div class="text-center py-4 text-muted">Tidak ada biller yang cocok.</div>';
            return;
        }

        const grouped = {};
        items.forEach(function (b) { (grouped[b.operator] = grouped[b.operator] || []).push(b); });

        let html = '';
        Object.keys(grouped).forEach(function (op) {
            html += '<div class="biller-group-title"><i class="bi bi-collection me-1"></i>' + op + '</div>';
            grouped[op].forEach(function (b) {
                html += '<div class="biller-item mb-1" data-id="' + b.id + '" onclick="selectBiller(' + b.id + ')">' +
                    '<span class="fw-semibold small">' + b.name + '</span>' +
                    '<i class="bi bi-chevron-right text-muted"></i></div>';
            });
        });
        list.innerHTML = html;
    }

    function filterBillers() {
        if (selectedType) renderBillers(selectedType, document.getElementById('billerSearch').value);
    }

    // ============ PILIH BILLER ============
    function selectBiller(id) {
        selectedBiller = BILLERS[selectedType].find(function (b) { return b.id === id; });
        if (!selectedBiller) return;

        document.querySelectorAll('.biller-item').forEach(function (el) {
            el.classList.toggle('active', Number(el.dataset.id) === id);
        });

        const hint = ID_HINTS[selectedType] || ID_HINTS['Lainnya'];
        document.getElementById('customerId').placeholder = hint.placeholder;
        document.getElementById('idHint').textContent = hint.hint;

        document.getElementById('idBillerLabel').textContent = selectedBiller.name;
        document.getElementById('idCard').classList.remove('d-none');
        document.getElementById('step3Label').classList.remove('disabled');
        document.getElementById('customerId').value = '';
        document.getElementById('customerId').focus();
    }

    // ============ INPUT ID ============
    function onIdInput() {
        const btn = document.getElementById('btnInquiry');
        btn.disabled = document.getElementById('customerId').value.trim().length < 4;
    }

    // ============ CEK TAGIHAN ============
    async function cekTagihan() {
        const destination = document.getElementById('customerId').value.trim();
        const btn = document.getElementById('btnInquiry');
        const hint = document.getElementById('idHint');

        if (!selectedBiller || destination.length < 4) {
            hint.textContent = 'ID pelanggan belum lengkap (min 4 karakter).';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengecek tagihan...';
        hint.textContent = '';

        try {
            const res = await fetch('{{ route('customer.tagihan.inquiry') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ product_id: selectedBiller.id, destination: destination })
            });
            const data = await res.json();

            if (!res.ok) {
                hint.className = 'small text-danger mt-1';
                hint.textContent = data.error || 'Cek tagihan gagal. Coba lagi.';
                return;
            }

            inquiryData = data;
            renderResult(data);
        } catch (e) {
            hint.className = 'small text-danger mt-1';
            hint.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i> Cek Tagihan';
        }
    }

    function formatRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function renderResult(data) {
        const saldo = {{ auth()->user()->saldo ?? 0 }};
        const el = document.getElementById('inquiryResult');
        el.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">Biller</span>
                <span class="fw-semibold small">${selectedBiller.name}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="small text-muted">ID Pelanggan</span>
                <span class="fw-semibold small">${document.getElementById('customerId').value.trim()}</span>
            </div>
            ${data.customer_name ? `
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="small text-muted">Nama Pelanggan</span>
                <span class="fw-semibold small">${data.customer_name}</span>
            </div>` : ''}
            <hr class="my-2">
            <div class="text-center">
                <div class="small text-muted">Nominal Tagihan</div>
                <div class="inquiry-nominal">${formatRupiah(data.nominal)}</div>
                <div class="small text-muted mt-2">Biaya Admin: ${formatRupiah(data.admin_fee)}</div>
                <div class="fw-bold mt-1">Total Bayar: <span class="text-primary">${formatRupiah(data.total)}</span></div>
                ${saldo < data.total ? '<div class="small text-danger mt-1"><i class="bi bi-exclamation-circle"></i> Saldo tidak mencukupi. Silakan topup terlebih dahulu.</div>' : ''}
            </div>
        `;

        document.getElementById('resultCard').classList.remove('d-none');
        document.getElementById('step4Label').classList.remove('disabled');
        document.getElementById('step3Label').classList.add('done');
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ============ BAYAR ============
    function openPaySheet() {
        const saldo = {{ auth()->user()->saldo ?? 0 }};
        const destination = document.getElementById('customerId').value.trim();

        if (saldo < inquiryData.total) {
            alert('Saldo tidak mencukupi. Silakan topup saldo terlebih dahulu.');
            return;
        }

        const hasDesc = selectedBiller.description && selectedBiller.description.trim() && selectedBiller.description !== selectedBiller.name;

        document.getElementById('bsBody').innerHTML = `
            <div class="bs-title mb-3">
                <div class="fw-bold fs-5"><i class="bi bi-receipt me-1 text-primary"></i>Konfirmasi Pembayaran</div>
                ${hasDesc ? `<div class="bs-desc mt-2"><i class="bi bi-info-circle me-1 text-primary"></i>${selectedBiller.description}</div>` : ''}
            </div>
            <div class="bs-detail mb-3">
                <div class="bs-row">
                    <span class="bs-label">Biller</span>
                    <span class="bs-value fw-semibold">${selectedBiller.name}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label">ID Pelanggan</span>
                    <span class="bs-value fw-semibold">${destination}</span>
                </div>
                ${inquiryData.customer_name ? `<div class="bs-row">
                    <span class="bs-label">Nama Pelanggan</span>
                    <span class="bs-value fw-semibold">${inquiryData.customer_name}</span>
                </div>` : ''}
                <div class="bs-row">
                    <span class="bs-label">Nominal Tagihan</span>
                    <span class="bs-value">${formatRupiah(inquiryData.nominal)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label">Biaya Admin</span>
                    <span class="bs-value">${formatRupiah(inquiryData.admin_fee)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label fw-semibold">Total Bayar</span>
                    <span class="bs-value fw-bold text-primary">${formatRupiah(inquiryData.total)}</span>
                </div>
                <div class="bs-row">
                    <span class="bs-label">Sisa Saldo</span>
                    <span class="bs-value">${formatRupiah(saldo)}</span>
                </div>
            </div>
            <div id="bsAlert"></div>
            <button class="btn btn-success w-100 btn-lg fw-semibold" id="bsSubmitBtn" onclick="submitPayment()">
                <i class="bi bi-check2-circle"></i> Ya, Bayar ${formatRupiah(inquiryData.total)}
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

    async function submitPayment() {
        const btn = document.getElementById('bsSubmitBtn');
        const alertBox = document.getElementById('bsAlert');
        const destination = document.getElementById('customerId').value.trim();

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
        alertBox.innerHTML = '';

        try {
            const res = await fetch('/order/' + inquiryData.product_id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ destination: destination, qty: inquiryData.nominal })
            });
            const data = await res.json();

            if (!res.ok) {
                alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">' + (data.error || 'Gagal membuat pembayaran.') + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle"></i> Ya, Bayar ' + formatRupiah(inquiryData.total);
                return;
            }

            alertBox.innerHTML = '<div class="alert alert-success py-2 small mb-2">' + data.message + '</div>';
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Pembayaran dibuat';

            setTimeout(() => {
                closeSheet();
                document.getElementById('resultCard').innerHTML =
                    '<div class="text-center py-5">' +
                    '<div class="text-success fs-1"><i class="bi bi-check-circle"></i></div>' +
                    '<div class="fw-semibold mt-2">' + data.message + '</div>' +
                    '<a href="/orders" class="btn btn-outline-primary btn-sm mt-3">Lihat Riwayat</a>' +
                    '</div>';
            }, 1200);
        } catch (e) {
            alertBox.innerHTML = '<div class="alert alert-danger py-2 small mb-2">Terjadi kesalahan. Silakan coba lagi.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Ya, Bayar ' + formatRupiah(inquiryData.total);
        }
    }
</script>
@endpush
