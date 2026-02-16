<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login | Logistics Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 2rem;
            background: #fff;
        }
        .login-card h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .login-card .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .login-card .btn-primary {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        .login-logo h1 {
            font-size: 2rem;
            color: #0d6efd;
            font-weight: 700;
        }
        .login-footer {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="login-card text-center">

                <div class="login-logo mb-4">
        <img src="{{ asset('dashboard-assets/images/logo3.png') }}" alt="Airborn Logo" style="height: 50px;">
    </div>

                <h2>Welcome Back</h2>
                <p class="text-muted mb-4">Please login to your account</p>

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" class="text-start">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">Login <i class="bi bi-box-arrow-in-right ms-1"></i></button>
                    </div>
                </form>

                <div class="login-footer mt-4">
                    &copy; {{ date('Y') }} ABC Express Logistics. All rights reserved.
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
