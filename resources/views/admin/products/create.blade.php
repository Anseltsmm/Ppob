@extends('layouts.admin')

@section('title', 'Tambah Produk')

@section('content')
<h4 class="fw-bold mb-4">Tambah Produk</h4>

<div class="card">
    <div class="card-body p-4">
        @include('admin.products._form', ['product' => null])
    </div>
</div>
@endsection
