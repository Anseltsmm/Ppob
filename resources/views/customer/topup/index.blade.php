@extends('layouts.app')

@section('title', 'Topup Saldo')

@section('content')
<h4 class="fw-bold mb-3">Topup Saldo</h4>

@if(empty($channels))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Metode pembayaran belum tersedia saat ini. Hubungi admin.
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Pilih Nominal</h5>
                <div class="row g-2 mb-3">
                    @foreach([25000, 50000, 100000, 200000, 500000, 1000000] as $nominal)
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 nominal-btn" data-nominal="{{ $nominal }}">
                                {{ number_format($nominal, 0, ',', '.') }}
                            </button>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('customer.topup.store') }}" id="topupForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nominal Topup (min Rp 10.000)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="amount" id="amountInput" class="form-control form-control-lg"
                                   min="10000" max="10000000" step="1000" value="{{ old('amount', 50000) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Metode Pembayaran</label>
                        @if(!empty($channels))
                            <div class="list-group" style="max-height: 320px; overflow-y: auto;">
                                @foreach($channels as $channel)
                                    @if($channel['active'] ?? false)
                                    <label class="list-group-item d-flex align-items-center gap-3 channel-item">
                                        <input class="form-check-input mt-0" type="radio" name="method"
                                               value="{{ $channel['code'] }}" {{ $loop->first ? 'checked' : '' }} required>
                                        <img src="{{ $channel['icon_url'] ?? '' }}" alt="" width="32" height="32" class="rounded"
                                             onerror="this.style.display='none'">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">{{ $channel['name'] }}</div>
                                            <div class="text-muted" style="font-size:.75rem;">{{ $channel['group'] }}</div>
                                        </div>
                                        <span class="text-muted small">
                                            @if(($channel['total_fee']['percent'] ?? 0) > 0)
                                                {{ number_format($channel['total_fee']['percent'], 1) }}% + {{ number_format($channel['total_fee']['flat'] ?? 0, 0, ',', '.') }}
                                            @else
                                                {{ number_format($channel['total_fee']['flat'] ?? 0, 0, ',', '.') }}
                                            @endif
                                        </span>
                                    </label>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <select name="method" class="form-select" disabled>
                                <option>-</option>
                            </select>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" {{ empty($channels) ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-right-circle"></i> Lanjutkan ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-bold">Riwayat Topup Terakhir</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Nominal</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th class="text-end">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(auth()->user()->deposits()->latest()->limit(10)->get() as $deposit)
                        <tr>
                            <td><a href="{{ route('customer.topup.pay', $deposit) }}">{{ $deposit->invoice }}</a></td>
                            <td>{{ number_format($deposit->amount, 0, ',', '.') }}</td>
                            <td class="small">{{ $deposit->payment_name ?: $deposit->payment_method }}</td>
                            <td><span class="badge bg-{{ $deposit->statusBadge() }}">{{ $deposit->statusLabel() }}</span></td>
                            <td class="text-end small text-muted">{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada topup.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <a href="{{ route('customer.deposits.index') }}" class="small">Lihat semua riwayat topup <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.nominal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('amountInput').value = this.dataset.nominal;
        });
    });
</script>
@endpush
@endsection
