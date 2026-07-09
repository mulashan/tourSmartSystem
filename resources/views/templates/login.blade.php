<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TourSmart Login</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/niceadmin-local.css') }}">
</head>
<body class="nice-auth-body">
    <div class="demo-bar">
        <div class="demo-brand"><span class="demo-mark">B</span> BOOTSTRAP<span>MADE</span></div>
        <div class="demo-title">TourSmart</div>
        <a href="#" class="demo-download">DOWNLOAD</a>
    </div>

    <main class="nice-auth-main">
        <div class="nice-login-brand">
            <span class="nice-logo-mark">N</span>
            <span>TourSmart</span>
        </div>

        <section class="nice-login-card">
            <div class="secure-badge"><i class="bi bi-shield-check"></i> Secure Access</div>
            <h1>Welcome back</h1>
            <p class="login-muted">Sign in to continue to your NiceAdmin workspace.</p>

            @if(session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <form action="{{ url('validate') }}" method="post">
                @csrf
                <input type="hidden" id="redirect" name="redirect" value="{{ request('redirect') }}">

                <div class="nice-field">
                    <label for="username">Email address</label>
                    <input type="email" id="username" name="username" value="demo@example.com" placeholder="name@example.com" autocomplete="off" required>
                </div>

                <div class="nice-field">
                    <div class="field-row">
                        <label for="password">Password</label>
                        <a href="#">Forgot password?</a>
                    </div>
                    <div class="password-wrap">
                        <input type="password" id="password" name="pswd" value="password" placeholder="Enter your password" autocomplete="off" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <div class="login-options">
                    <label class="check-line"><input type="checkbox" name="remember"> <span>Remember me</span></label>
                    <a href="#">Use lock screen</a>
                </div>

                <button type="submit" class="nice-submit">Sign In</button>
            </form>

            <div class="divider"><span>or continue with</span></div>
            <div class="social-row">
                <a href="#" class="social-btn"><i class="bi bi-google"></i> Google</a>
                <a href="#" class="social-btn"><i class="bi bi-github"></i> GitHub</a>
            </div>
            <p class="create-account">Don't have an account? <a href="{{ url('register') }}">Create one</a></p>
        </section>

        <footer class="auth-footer">
            <div>&copy; 2026 <strong>TourSmart.</strong> All Rights Reserved.</div>
            <div>Privacy • Terms • Help</div>
            <div>Designed by <a href="#">TourSmart</a></div>
        </footer>
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/niceadmin-local.js') }}"></script>
</body>
</html>
