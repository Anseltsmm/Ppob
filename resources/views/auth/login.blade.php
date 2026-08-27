<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a8a, #2563eb); min-height: 100vh; }
        .auth-card { max-width: 420px; border-radius: 1rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="card auth-card w-100 mx-3 shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold"><i class="bi bi-lightning-charge-fill text-primary"></i> {{ config('app.name') }}</h3>
                <p class="text-muted">Masuk ke akun Anda</p>
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

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
            </p>
            <p class="text-center mt-2 mb-0 small">
                <a href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Kembali ke beranda</a>
            </p>
        </div>
    </div>
</body>
</html>
