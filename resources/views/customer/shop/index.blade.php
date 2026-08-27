@extends('layouts.app')

@section('title', 'Beli Produk')

@section('content')
<h4 class="fw-bold mb-3">Beli Produk</h4>

<div class="row g-2 mb-4">
    <div class="col-auto">
        <a href="{{ route('shop.index') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Semua</a>
    </div>
    @foreach($categories as $cat)
        <div class="col-auto">
            <a href="{{ route('shop.category', $cat) }}" class="btn btn-sm {{ request('category') == $cat->id || (isset($category) && $category->id == $cat->id) ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">
                @if($cat->icon)<i class="bi bi-{{ $cat->icon }}"></i>@endif {{ $cat->name }}
            </a>
        </div>
    @endforeach
    <div class="col-auto ms-auto">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari produk...">
            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

@if(isset($category))
    <p class="text-muted">{{ $category->description }}</p>
@endif

@if($products->count())
<div class="row g-3">
    @foreach($products as $product)
    <div class="col-6 col-md-4 col-lg-3">
        <a href="{{ route('shop.show', $product) }}" class="card product-card h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                    @if($product->type === 'opendenom')
                        <span class="badge bg-info">Nominal Bebas</span>
                    @endif
                </div>
                <h6 class="fw-bold mb-1">{{ $product->name }}</h6>
                @if($product->operator)<div class="text-muted small mb-1">{{ $product->operator }}</div>@endif
                <div class="text-primary fw-bold">
                    @if($product->type === 'opendenom')
                        {{ number_format($product->admin_fee, 0, ',', '.') }} / transaksi
                    @else
                        {{ number_format($product->sell_price, 0, ',', '.') }}
                    @endif
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $products->links() }}</div>
@else
<div class="text-center py-5 text-muted">
    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
    Belum ada produk tersedia.
</div>
@endif
@endsection
