<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TourSmart Login - Select Branch</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/niceadmin-local.css') }}">
</head>
<body class="nice-auth-body">
    <div class="demo-bar">
        <div class="demo-brand"><span class="demo-mark">l</span> LeopardTours<span>Tours</span></div>
        <div class="demo-title">TourSmart</div>
        <a href="#" class="demo-download"></a>
    </div>

    <main class="nice-auth-main">
        <div class="nice-login-brand">
            <span class="nice-logo-mark">N</span>
            <span>TourSmart</span>
        </div>

        <section class="nice-login-card">
            <div class="secure-badge"><i class="bi bi-building-check"></i> Branch Selection</div>
            <h1>Hi, {{ $userName }}</h1>
            <p class="login-muted">Choose which Branch you'd like to work in.</p>

            @if(session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login.select-branch.submit') }}" method="post">
                @csrf

                <div class="nice-field">
                    <label for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" class="form-select" required>
                        <option value="">Select a branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->Branch_ID }}">{{ $branch->Branch_Name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="nice-submit">Continue</button>
            </form>

            <p class="create-account">
                <a href="{{ route('login') }}">&larr; Back to login</a>
            </p>
        </section>

        <footer class="auth-footer">
            <div>&copy; 2026 <strong>TourSmart.</strong> All Rights Reserved.</div>
        </footer>
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/niceadmin-local.js') }}"></script>
</body>
</html>