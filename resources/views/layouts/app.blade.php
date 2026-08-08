<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ app_name() }}</title>
    <meta name="description" content="@yield('meta_description', config('app.description', app_name().' — Enterprise Management System'))">

    @vite(['resources/js/app.js'])

    <script>
        window.Prowave = {
            csrfToken: '{{ csrf_token() }}',
            themeEnabled: true,
            themeUrl: '{{ route('profile.preferences') }}',
            url: '{{ url('/') }}',
        };
    </script>

    @stack('head')
</head>
<body data-user-theme="{{ auth()->user()?->theme ?? 'system' }}">

    <div class="app-shell">
        @include('partials.sidebar')

        <div class="sidebar-backdrop"></div>

        <main class="app-main">
            @include('partials.navbar')

            <div class="app-content">
                @include('partials.flash')

                <div class="fade-in">
                    @yield('content')
                </div>
            </div>

            @include('partials.footer')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
