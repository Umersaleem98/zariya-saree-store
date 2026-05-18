<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Saree Store</title>
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

        .register-wrapper {
            width: 100%;
            max-width: 460px;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary) 0%, #880e4f 100%);
            padding: 35px 30px 30px;
            text-align: center;
            position: relative;
        }

        .register-header::after {
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
            margin: 0 auto 15px;
            font-size: 2rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .register-header h3 {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .register-header p {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            margin: 0;
        }

        .register-body {
            padding: 40px 35px 35px;
        }

        .form-floating {
            margin-bottom: 18px;
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

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            background: #eef0f5;
            overflow: hidden;
            display: none;
        }

        .password-strength.show { display: block; }

        .password-strength .bar {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s;
            width: 0;
        }

        .password-strength .bar.weak { width: 33%; background: #ef5350; }
        .password-strength .bar.medium { width: 66%; background: #ffa726; }
        .password-strength .bar.strong { width: 100%; background: #66bb6a; }

        .strength-text {
            font-size: 0.75rem;
            margin-top: 4px;
            display: none;
        }

        .strength-text.show { display: block; }

        .btn-register {
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

        .btn-register:hover {
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

        .register-footer {
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
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

        .terms-check {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .terms-check a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-check a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-body {
                padding: 30px 25px 25px;
            }
            .register-header {
                padding: 30px 25px 25px;
            }
        }
    </style>
</head>

<body>

    <div class="register-wrapper">
        <div class="register-card">
            
            <!-- Header -->
            <div class="register-header">
                <div class="brand-logo">
                    <i class="bi bi-gem"></i>
                </div>
                <h3>Create Account</h3>
                <p>Join Saree Store today</p>
            </div>

            <!-- Body -->
            <div class="register-body">

                <!-- Error Alert -->
                @if (session('error'))
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-custom">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Full Name -->
                    <div class="form-floating position-relative">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               placeholder="John Doe" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus>
                        <label for="name">Full Name</label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-floating position-relative">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               placeholder="name@example.com" 
                               value="{{ old('email') }}" 
                               required>
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
                               required
                               oninput="checkStrength(this.value)">
                        <label for="password">Password</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Strength -->
                    <div class="password-strength" id="strengthBar">
                        <div class="bar" id="strengthBarInner"></div>
                    </div>
                    <div class="strength-text" id="strengthText">
                        <small class="text-muted">Password strength: <span id="strengthLabel">Weak</span></small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-floating position-relative">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" 
                               name="password_confirmation" 
                               class="form-control" 
                               id="password_confirmation" 
                               placeholder="Confirm Password" 
                               required>
                        <label for="password_confirmation">Confirm Password</label>
                    </div>

                    <!-- Terms -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label terms-check" for="terms">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-register">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>OR SIGN UP WITH</span>
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
                <div class="register-footer">
                    Already have an account? 
                    <a href="{{ route('login') }}">Sign In</a>
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

    <script>
        function checkStrength(password) {
            const bar = document.getElementById('strengthBar');
            const barInner = document.getElementById('strengthBarInner');
            const text = document.getElementById('strengthText');
            const label = document.getElementById('strengthLabel');

            if (password.length === 0) {
                bar.classList.remove('show');
                text.classList.remove('show');
                return;
            }

            bar.classList.add('show');
            text.classList.add('show');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            barInner.className = 'bar';
            
            if (strength <= 1) {
                barInner.classList.add('weak');
                label.textContent = 'Weak';
                label.style.color = '#ef5350';
            } else if (strength === 2) {
                barInner.classList.add('medium');
                label.textContent = 'Medium';
                label.style.color = '#ffa726';
            } else {
                barInner.classList.add('strong');
                label.textContent = 'Strong';
                label.style.color = '#66bb6a';
            }
        }
    </script>
</body>

</html>