@extends('layouts.admin')

@section('title', 'Pengaturan')

@push('styles')
<style>
    /* ===== Header ===== */
    .set-hero {
        background: linear-gradient(135deg, #1e293b 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .set-hero .deco { position: absolute; border-radius: 50%; pointer-events: none; }
    .set-hero .d1 { width: 190px; height: 190px; background: rgba(255,255,255,.06); top: -80px; right: -30px; }
    .set-hero .d2 { width: 110px; height: 110px; background: rgba(255,255,255,.05); bottom: -40px; left: 30%; }

    /* ===== Status chip ===== */
    .chip {
        font-size: .72rem; font-weight: 600; padding: .3rem .65rem;
        border-radius: 999px; display: inline-flex; align-items: center; gap: .35rem;
    }
    .chip-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
    .chip-ok { background: #dcfce7; color: #15803d; }
    .chip-ok .chip-dot { background: #16a34a; }
    .chip-wait { background: #fef3c7; color: #b45309; }
    .chip-wait .chip-dot { background: #f59e0b; }

    /* ===== Tabs ===== */
    .set-tabs .nav-link {
        display: flex; align-items: center; gap: .5rem;
        font-weight: 600; color: #475569; font-size: .85rem;
        border: none; border-bottom: 2.5px solid transparent;
        padding: .75rem .9rem; border-radius: 0;
    }
    .set-tabs .nav-link i { font-size: 1.05rem; }
    .set-tabs .nav-link.active {
        color: #2563eb; background: transparent;
        border-bottom-color: #2563eb;
    }
    .set-tabs .nav-link small { font-weight: 500; }
    .set-tabs .tab-count {
        font-size: .65rem; font-weight: 700;
        background: #e2e8f0; color: #475569;
        border-radius: 999px; padding: .1rem .45rem; min-width: 18px; text-align: center;
    }

    /* ===== Panel card ===== */
    .set-panel {
        background: #fff; border-radius: .9rem;
        border: 1px solid #eef2f7;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .set-panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .set-panel-body { padding: 1.5rem 1.25rem; }

    .provider-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }

    .set-section-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .03em; color: #94a3b8;
        display: flex; align-items: center; gap: .4rem;
        margin-bottom: 1rem;
    }

    /* ===== Info row (callback) ===== */
    .info-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .8rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .info-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .info-row .info-body { flex: 1; min-width: 0; }
    .info-row .info-label { font-size: .78rem; font-weight: 600; color: #1e293b; }
    .info-row .info-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .78rem; color: #475569; word-break: break-all;
    }
    .info-row .info-hint { font-size: .72rem; color: #94a3b8; }

    .copy-btn.compact {
        font-size: .72rem; padding: .3rem .6rem; flex-shrink: 0;
    }
</style>
@endpush

@section('content')
{{-- ===== HEADER ===== --}}
<div class="set-hero">
    <span class="deco d1"></span>
    <span class="deco d2"></span>
    <div class="position-relative d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div style="font-size:.8rem; opacity:.75;"><i class="bi bi-gear"></i> Panel Konfigurasi</div>
            <div class="fw-bold fs-4">Pengaturan Aplikasi</div>
            <div style="font-size:.8rem; opacity:.7; margin-top:2px;">
                Kelola integrasi OkeConnect, payment gateway TriPay, QRIS, dan info layanan.
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="chip {{ $okeconnectConfigured ? 'chip-ok' : 'chip-wait' }}">
                <span class="chip-dot"></span> OkeConnect: {{ $okeconnectConfigured ? 'Terhubung' : 'Belum diatur' }}
            </span>
            <span class="chip {{ $tripayConfigured ? 'chip-ok' : 'chip-wait' }}">
                <span class="chip-dot"></span> TriPay: {{ $tripayConfigured ? 'Terhubung' : 'Belum diatur' }}
            </span>
        </div>
    </div>
</div>

{{-- ===== TABS ===== --}}
<ul class="nav set-tabs mb-3" id="settingsTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-okeconnect" type="button" role="tab">
            <i class="bi bi-plug text-danger"></i> OkeConnect
            <span class="tab-count">{{ $okeconnectConfigured ? '✓' : '!' }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tripay" type="button" role="tab">
            <i class="bi bi-credit-card"></i> TriPay
            <span class="tab-count">{{ $tripayConfigured ? '✓' : '!' }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-qris" type="button" role="tab">
            <i class="bi bi-qr-code-scan text-primary"></i> QRIS &amp; Info
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-callback" type="button" role="tab">
            <i class="bi bi-shuffle text-success"></i> Callback
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- ================= TAB: OKECONNECT ================= --}}
    <div class="tab-pane fade show active" id="tab-okeconnect" role="tabpanel">
        <div class="set-panel">
            <div class="set-panel-header d-flex align-items-center gap-3">
                <div class="provider-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-plug"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold">OkeConnect (H2H)</div>
                    <div class="small text-muted">Provider produk digital — pulsa, paket data, token PLN, pascabayar, e-wallet, game.</div>
                </div>
                <a href="{{ route('admin.settings.check-balance') }}" class="btn btn-sm btn-outline-info"
                   onclick="event.preventDefault(); document.getElementById('checkBalanceForm').submit();">
                    <i class="bi bi-wallet2"></i> Cek Saldo
                </a>
            </div>
            <div class="set-panel-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Base URL</label>
                        <input type="url" name="okeconnect_base_url" class="form-control" value="{{ $settings['okeconnect_base_url'] }}">
                        <div class="form-text">Default: https://h2h.okeconnect.com</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Member ID</label>
                        <input type="text" name="okeconnect_member_id" class="form-control" value="{{ $settings['okeconnect_member_id'] }}" placeholder="OK00123">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">PIN Transaksi</label>
                            <input type="password" name="okeconnect_pin" class="form-control" value="{{ $settings['okeconnect_pin'] }}" placeholder="PIN dari pendaftaran">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Password Transaksi API</label>
                            <input type="password" name="okeconnect_password" class="form-control" value="{{ $settings['okeconnect_password'] }}">
                            <div class="form-text">Password untuk transaksi via IP/API.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
                <form id="checkBalanceForm" method="POST" action="{{ route('admin.settings.check-balance') }}" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    {{-- ================= TAB: TRIPAY ================= --}}
    <div class="tab-pane fade" id="tab-tripay" role="tabpanel">
        <div class="set-panel">
            <div class="set-panel-header d-flex align-items-center gap-3">
                <div class="provider-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-credit-card"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold">TriPay (Payment Gateway)</div>
                    <div class="small text-muted">Payment gateway untuk topup saldo customer (VA, QRIS, e-wallet, retail).</div>
                </div>
                <a href="{{ route('admin.settings.test-tripay') }}" class="btn btn-sm btn-outline-success"
                   onclick="event.preventDefault(); document.getElementById('testTripayForm').submit();">
                    <i class="bi bi-plug"></i> Test Koneksi
                </a>
            </div>
            <div class="set-panel-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mode</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tripay_mode" id="mode_sandbox" value="sandbox" {{ $settings['tripay_mode'] === 'sandbox' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="mode_sandbox"><i class="bi bi-bug"></i> Sandbox (testing)</label>
                            <input type="radio" class="btn-check" name="tripay_mode" id="mode_production" value="production" {{ $settings['tripay_mode'] === 'production' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="mode_production"><i class="bi bi-globe"></i> Production (live)</label>
                        </div>
                        <div class="form-text">Gunakan Sandbox dulu untuk pengujian, lalu pindah ke Production saat siap live.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">API Key</label>
                        <input type="password" name="tripay_api_key" class="form-control" value="{{ $settings['tripay_api_key'] }}">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Private Key</label>
                            <input type="password" name="tripay_private_key" class="form-control" value="{{ $settings['tripay_private_key'] }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Merchant Code</label>
                            <input type="text" name="tripay_merchant_code" class="form-control" value="{{ $settings['tripay_merchant_code'] }}" placeholder="T0001">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
                <form id="testTripayForm" method="POST" action="{{ route('admin.settings.test-tripay') }}" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    {{-- ================= TAB: QRIS & INFO ================= --}}
    <div class="tab-pane fade" id="tab-qris" role="tabpanel">
        <div class="set-panel">
            <div class="set-panel-header d-flex align-items-center gap-3">
                <div class="provider-icon" style="background:#ecfeff; color:#0891b2;"><i class="bi bi-info-circle"></i></div>
                <div>
                    <div class="fw-bold">QRIS &amp; Info Layanan</div>
                    <div class="small text-muted">Payload QRIS statis dan kontak layanan yang ditampilkan ke customer.</div>
                </div>
            </div>
            <div class="set-panel-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">QRIS Payload (string statis)</label>
                        <textarea name="qris_payload" class="form-control font-monospace" rows="3" placeholder="0002010102112660...">{{ $settings['qris_payload'] }}</textarea>
                        <div class="form-text">String QRIS statis (biasanya diawali <code>000201</code>). Kosongkan jika belum punya.</div>
                    </div>

                    <div class="set-section-label"><i class="bi bi-person-lines-fill"></i> Kontak Layanan</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. Telepon</label>
                            <input type="text" name="app_info_phone" class="form-control" value="{{ $settings['app_info_phone'] }}" placeholder="08xxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">WhatsApp</label>
                            <input type="text" name="app_info_whatsapp" class="form-control" value="{{ $settings['app_info_whatsapp'] }}" placeholder="08xxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="app_info_email" class="form-control" value="{{ $settings['app_info_email'] }}" placeholder="cs@contoh.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jam Operasional</label>
                            <input type="text" name="app_info_hours" class="form-control" value="{{ $settings['app_info_hours'] }}" placeholder="Setiap hari, 24 jam">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <input type="text" name="app_info_address" class="form-control" value="{{ $settings['app_info_address'] }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= TAB: CALLBACK ================= --}}
    <div class="tab-pane fade" id="tab-callback" role="tabpanel">
        <div class="set-panel">
            <div class="set-panel-header d-flex align-items-center gap-3">
                <div class="provider-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-shuffle"></i></div>
                <div>
                    <div class="fw-bold">Informasi Callback</div>
                    <div class="small text-muted">URL callback untuk diisi di dashboard provider. Token OkeConnect dipakai untuk autentikasi.</div>
                </div>
            </div>
            <div class="set-panel-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="small fw-bold text-uppercase mb-2" style="color:#94a3b8;">Tripay (isi di dashboard TriPay)</div>
                        <div class="info-row">
                            <div class="info-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-credit-card"></i></div>
                            <div class="info-body">
                                <div class="info-label">Tripay Callback URL</div>
                                <div class="info-code">{{ route('webhook.tripay') }}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn compact"
                                    data-copy="{{ route('webhook.tripay') }}"><i class="bi bi-clipboard"></i> Salin</button>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="small fw-bold text-uppercase mb-2" style="color:#94a3b8;">OkeConnect (isi di dashboard OkeConnect)</div>
                        <div class="info-row">
                            <div class="info-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-plug"></i></div>
                            <div class="info-body">
                                <div class="info-label">OkeConnect Callback URL</div>
                                <div class="info-code">{{ route('webhook.okeconnect') }}?token={{ $settings['okeconnect_callback_token'] }}</div>
                                <div class="info-hint">Token dipakai untuk autentikasi callback.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn compact"
                                    data-copy="{{ route('webhook.okeconnect') }}?token={{ $settings['okeconnect_callback_token'] }}"><i class="bi bi-clipboard"></i> Salin</button>
                        </div>
                        <div class="info-row">
                            <div class="info-icon" style="background:#fffbeb; color:#d97706;"><i class="bi bi-globe2"></i></div>
                            <div class="info-body">
                                <div class="info-label">IP Publik VPS</div>
                                <div class="info-code">{{ $serverIp ?? '—' }}</div>
                                <div class="info-hint">Untuk whitelist IP callback di OkeConnect.</div>
                            </div>
                            @if($serverIp)
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn compact"
                                    data-copy="{{ $serverIp }}"><i class="bi bi-clipboard"></i> Salin</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-4 p-3 rounded-3" style="background:#fffbeb; border:1px solid #fde68a;">
                    <i class="bi bi-arrow-clockwise text-warning fs-5"></i>
                    <div class="flex-grow-1 small">
                        <strong>Ganti token callback OkeConnect?</strong>
                        <span class="text-muted d-block">URL callback lama tidak akan berfungsi lagi.</span>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.regenerate-token') }}"
                          onsubmit="return confirm('Ganti token callback OkeConnect? URL callback lama tidak akan berfungsi lagi.');">
                        @csrf
                        <button class="btn btn-sm btn-warning"><i class="bi bi-arrow-clockwise"></i> Regenerate Token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi ulang copy-btn (jika tab switching membuat konten dinamis)
        initCopyButtons();
    });

    function initCopyButtons() {
        document.querySelectorAll('.copy-btn:not([data-bound])').forEach(function (btn) {
            btn.dataset.bound = '1';
            btn.addEventListener('click', function () {
                const text = btn.dataset.copy || '';
                const label = btn.querySelector('i');
                const done = function () {
                    if (label) label.className = 'bi bi-check-lg';
                    btn.classList.add('btn-success');
                    btn.classList.remove('btn-outline-secondary');
                    setTimeout(function () {
                        if (label) label.className = 'bi bi-clipboard';
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 1500);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(done).catch(function () {
                        fallbackCopy(text, done);
                    });
                } else {
                    fallbackCopy(text, done);
                }
            });
        });
    }

    function fallbackCopy(text, done) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            done();
        } catch (e) {}
        document.body.removeChild(ta);
    }
</script>
@endpush