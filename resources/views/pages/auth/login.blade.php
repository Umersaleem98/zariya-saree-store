<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Saree Store Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #c2185b;
            --primary-light: #fce4ec;
            --secondary: #d4a017;
            --dark: #1a1d29;
            --body-bg: #f4f6f9;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(135deg, #1a1d29 0%, #2d3142 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, #880e4f 100%);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0; right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .login-header h3 {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            margin: 0;
        }

        .login-body {
            padding: 40px 35px 35px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating > .form-control {
            border-radius: 12px;
            border: 2px solid #eef0f5;
            height: 56px;
            padding-left: 45px;
            font-size: 0.95rem;
        }

        .form-floating > .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a3bd;
            font-size: 1.1rem;
            z-index: 10;
        }

        .form-floating > label {
            padding-left: 45px;
            font-size: 0.9rem;
            color: #a0a3bd;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            font-size: 0.85rem;
            color: #6c757d;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, #880e4f 100%);
            border: none;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(194,24,89,0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #a0a3bd;
            font-size: 0.8rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #eef0f5;
        }

        .divider span {
            padding: 0 15px;
        }

        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: 2px solid #eef0f5;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .login-footer {
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-body {
                padding: 30px 25px 25px;
            }
            .login-header {
                padding: 30px 25px 25px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <!-- Header -->
            <div class="login-header">
                <div class="brand-logo">
                    <i class="bi bi-gem"></i>
                </div>
                <h3>Welcome Back</h3>
                <p>Sign in to your Saree Store account</p>
            </div>

            <!-- Body -->
            <div class="login-body">

                <!-- Error Alert -->
                @if (session('error'))
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Success Alert -->
                @if (session('success'))
                    <div class="alert alert-success alert-custom">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="form-floating position-relative">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               placeholder="name@example.com" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus>
                        <label for="email">Email address</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-floating position-relative">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               placeholder="Password" 
                               required>
                        <label for="password">Password</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>

                <!-- Social Login -->
                <div class="social-login">
                    <a href="#" class="social-btn">
                        <i class="bi bi-google text-danger"></i>
                        Google
                    </a>
                    <a href="#" class="social-btn">
                        <i class="bi bi-facebook text-primary"></i>
                        Facebook
                    </a>
                </div>

                <!-- Footer -->
                <div class="login-footer">
                    Don't have an account? 
                    <a href="{{ route('register') }}">Create Account</a>
                </div>

            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-white text-decoration-none" style="font-size: 0.9rem; opacity: 0.8;">
                <i class="bi bi-arrow-left me-1"></i> Back to Store
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>