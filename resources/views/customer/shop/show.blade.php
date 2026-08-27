@extends('layouts.app')

@section('title', $product->name)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Shop</a></li>
        <li class="breadcrumb-item"><a href="{{ route('shop.category', $product->category) }}">{{ $product->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4 text-center">
                <div class="icon-circle bg-primary-subtle text-primary mx-auto mb-3" style="width:64px;height:64px;font-size:1.8rem;">
                    <i class="bi bi-{{ $product->category->icon ?: 'box' }}"></i>
                </div>
                <h4 class="fw-bold">{{ $product->name }}</h4>
                @if($product->operator)<div class="text-muted">{{ $product->operator }}</div>@endif
                <hr>
                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Kategori</span><span>{{ $product->category->name }}</span></div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Harga</span>
                        <span class="fw-bold text-primary">
                            @if($product->type === 'opendenom')
                                Nominal + {{ number_format($product->admin_fee, 0, ',', '.') }}
                            @else
                                {{ number_format($product->sell_price, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Saldo Anda</span><span class="fw-bold">{{ number_format(auth()->user()->saldo, 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Form Pembelian</h5>

                @if($product->description)
                    <div class="alert alert-light border small">{{ $product->description }}</div>
                @endif

                <form method="POST" action="{{ route('customer.orders.store', $product) }}" id="buyForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">
                            {{ $product->category->slug == 'token-pln' ? 'Nomor Meter / ID Pelanggan' : 'Nomor Tujuan' }}
                        </label>
                        <input type="text" name="destination" class="form-control form-control-lg"
                               placeholder="08xxxxxxxxxx" required autocomplete="off">
                    </div>

                    @if($product->type === 'opendenom')
                        <div class="mb-3">
                            <label class="form-label">Nominal (min {{ number_format($product->min_nominal, 0, ',', '.') }}, maks {{ number_format($product->max_nominal, 0, ',', '.') }})</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="qty" class="form-control" min="{{ $product->min_nominal }}" max="{{ $product->max_nominal }}" step="100" required id="qtyInput">
                            </div>
                            <div class="form-text">Total yang dibayar = nominal + biaya admin {{ number_format($product->admin_fee, 0, ',', '.') }}</div>
                        </div>
                        <div class="alert alert-primary" id="totalBox">
                            Total: <strong>Rp 0</strong>
                        </div>
                        @push('scripts')
                        <script>
                            const qtyInput = document.getElementById('qtyInput');
                            const totalBox = document.getElementById('totalBox');
                            const fee = {{ (int) $product->admin_fee }};
                            qtyInput.addEventListener('input', function() {
                                const qty = parseInt(this.value || 0, 10);
                                const total = qty + fee;
                                totalBox.innerHTML = 'Total: <strong>Rp ' + total.toLocaleString('id-ID') + '</strong>';
                            });
                        </script>
                        @endpush
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold flex-fill">
                            <i class="bi bi-lightning-charge"></i> Beli Sekarang
                        </button>
                        <a href="{{ route('customer.topup.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-plus-lg"></i> Topup
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
