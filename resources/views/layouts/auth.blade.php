<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') — {{ app_name() }}</title>

    @include('layouts.partials.assets-css', ['includeAppAssets' => false])

    <script>
        window.Prowave = {
            csrfToken: '{{ csrf_token() }}',
            themeEnabled: true,
            themeUrl: '{{ route('profile.preferences') }}',
        };
    </script>

    @stack('head')
</head>
<body class="auth-page" data-user-theme="system">

    <div class="auth-panel">
        <div class="auth-card scale-in">
            @yield('content')
        </div>
    </div>

    <div class="auth-illustration">
        <div class="illustration-content">
            <h2>Run your entire company from one place</h2>
            <p>
                {{ app_name() }} brings projects, people, finance, and operations together in a single,
                beautiful platform built for growing software companies.
            </p>

            <div class="auth-features">
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Projects & Tasks</span>
                </div>
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Employee Management</span>
                </div>
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Finance & Invoicing</span>
                </div>
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Analytics & Reports</span>
                </div>
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Role-based Security</span>
                </div>
                <div class="feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.assets-js', ['includeAppAssets' => false])
    @stack('scripts')
</body>
</html>
