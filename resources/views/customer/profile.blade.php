@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
<style>
    .profile-avatar {
        width: 84px; height: 84px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .3);
    }
</style>
@endpush

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body text-center p-4 p-md-5">
                <div class="profile-avatar mb-3">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <h5 class="fw-bold mb-0">{{ auth()->user()->name }}</h5>
                <div class="text-muted small mb-3">{{ auth()->user()->email }}</div>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark rounded-pill px-3 py-2"><i class="bi bi-receipt text-primary"></i> {{ $stats['total_order'] }} Order</span>
                    <span class="badge bg-light text-dark rounded-pill px-3 py-2"><i class="bi bi-check-circle text-success"></i> {{ $stats['order_success'] }} Sukses</span>
                </div>

                <div class="text-start small">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted"><i class="bi bi-phone"></i> No. HP</span>
                        <span class="fw-semibold">{{ auth()->user()->phone ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted"><i class="bi bi-cash-coin"></i> Saldo</span>
                        <span class="fw-semibold">Rp {{ number_format(auth()->user()->saldo, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted"><i class="bi bi-calendar-check"></i> Bergabung</span>
                        <span class="fw-semibold">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted"><i class="bi bi-shield-check"></i> Status</span>
                        <span class="badge bg-{{ auth()->user()->status ? 'success' : 'secondary' }}">{{ auth()->user()->status ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-bold"><i class="bi bi-pencil-square text-primary"></i> Edit Profil</div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                        </div>
                        <div class="form-text">Email tidak dapat diubah.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-muted">(opsional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="bi bi-box-arrow-right text-danger"></i> Keluar dari aplikasi</h6>
                        <p class="text-muted small mb-0">Anda akan dialihkan ke halaman login.</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
