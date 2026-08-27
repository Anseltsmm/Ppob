@extends('layouts.admin')

@section('title', 'Edit Produk')

@section('content')
<h4 class="fw-bold mb-4">Edit Produk</h4>

<div class="card">
    <div class="card-body p-4">
        @include('admin.products._form', ['product' => $product])
    </div>
</div>
@endsection
