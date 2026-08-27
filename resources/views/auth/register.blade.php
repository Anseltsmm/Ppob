<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a8a, #2563eb); min-height: 100vh; }
        .auth-card { max-width: 460px; border-radius: 1rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="card auth-card w-100 mx-3 shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold"><i class="bi bi-lightning-charge-fill text-primary"></i> {{ config('app.name') }}</h3>
                <p class="text-muted">Buat akun baru</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP (opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Daftar</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
