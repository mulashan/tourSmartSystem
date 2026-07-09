<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TourSmart Register</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/niceadmin-local.css') }}">
</head>
<body class="nice-auth-body register-auth-body">
    <main class="nice-auth-main register-auth-main">
        <a href="{{ url('login') }}" class="nice-login-brand">
            <span class="nice-logo-mark">N</span>
            <span>TourSmart</span>
        </a>

        <section class="nice-login-card register-card">
            <div class="secure-badge"><i class="bi bi-stars"></i> Get Started</div>
            <h1>Create account</h1>
            <p class="login-muted">Set up your workspace and invite your team in minutes.</p>

            <form action="{{ url('validate') }}" method="post">
                @csrf
                <input type="hidden" name="redirect" value="{{ url('dashboard') }}">

                <div class="register-name-grid">
                    <div class="nice-field">
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="John" required>
                    </div>
                    <div class="nice-field">
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                    </div>
                </div>

                <div class="nice-field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="username" placeholder="name@example.com" required>
                </div>

                <div class="nice-field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="pswd" placeholder="Create a password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password"><i class="bi bi-eye"></i></button>
                    </div>
                    <small class="field-help">Use at least 8 characters with letters and numbers.</small>
                </div>

                <label class="check-line terms-line">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.</span>
                </label>

                <button type="submit" class="nice-submit">Create Account</button>
            </form>

            <p class="create-account">Already have an account? <a href="{{ url('login') }}">Sign in</a></p>
        </section>

        <footer class="auth-footer">
            <div>&copy; 2026 <strong>NiceAdmin.</strong> All Rights Reserved.</div>
            <div>Privacy &bull; Terms &bull; Help</div>
            <div>Designed by <a href="#">BootstrapMade</a></div>
        </footer>
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/niceadmin-local.js') }}"></script>
</body>
</html>
