<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme ?? 'light' }}" data-bs-theme="{{ auth()->user()?->theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ app_name() }}</title>
    <meta name="description" content="@yield('meta_description', config('app.description', app_name().' — Enterprise Management System'))">

    @vite(['resources/js/app.js'])

    {{-- No-flash theme: apply the saved theme before CSS paints --}}
    <script>
        (function () {
            var stored = localStorage.getItem('pw-theme');
            var theme = stored || '{{ auth()->user()?->theme ?? 'system' }}';
            if (theme === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            var root = document.documentElement;
            root.setAttribute('data-theme', theme);
            root.setAttribute('data-bs-theme', theme);
        })();
    </script>

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

    {{-- Print Header — visible only when printing --}}
    <div class="print-header d-none">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ app_name() }}" class="logo-img" onerror="this.style.display='none'">
                <div>
                    <div class="company-name">{{ setting('company.name', app_name()) }}</div>
                    <div class="company-sub">Management System</div>
                </div>
            </div>
            <div class="report-meta">
                <div><strong>@yield('title', 'Report')</strong></div>
                <div>{{ now()->format('d M, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- Print Footer — visible only when printing --}}
    <div class="print-footer d-none">
        &copy; {{ date('Y') }} {{ setting('company.name', app_name()) }} — All rights reserved. | Printed {{ now()->format('d M, Y h:i A') }}
    </div>

    <div class="app-shell">
        <div class="d-print-none">
            @include('partials.sidebar')
        </div>

        <div class="sidebar-backdrop d-print-none"></div>

        <main class="app-main">
            <div class="d-print-none">
                @include('partials.navbar')
            </div>

            <div class="app-content">
                @include('partials.flash')

                <div class="fade-in">
                    @yield('content')
                </div>
            </div>

            <div class="d-print-none">
                @include('partials.footer')
            </div>
        </main>
    </div>

    <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true"
         data-search-url="{{ route('search.data') }}">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content search-modal">
                <div class="modal-header py-2 px-3">
                    <i class="bi bi-search text-muted me-2"></i>
                    <input type="search" class="form-control border-0 shadow-none global-search-input"
                           placeholder="Search staff, clients, leads, projects, tasks, invoices, documents, meetings..."
                           aria-label="Search everything" autocomplete="off">
                    <kbd class="search-kbd ms-2 d-none d-sm-block">ESC</kbd>
                </div>
                <div class="modal-body p-0" data-search-results>
                    <div class="p-4 text-center text-muted" data-search-empty>
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        <span class="fs-sm">Type at least 2 characters to search across the entire system.</span>
                    </div>
                </div>
                <div class="modal-footer py-2 justify-content-start fs-xs text-muted">
                    <span><kbd class="search-kbd">Ctrl</kbd> + <kbd class="search-kbd">K</kbd> to open search</span>
                    <span class="ms-3"><i class="bi bi-arrow-up-short"></i><i class="bi bi-arrow-down-short"></i> navigate</span>
                </div>
            </div>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    <script>
        function printUrlInHiddenFrame(url) {
            let iframe = document.getElementById('global_print_iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'global_print_iframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.opacity = '0';
                iframe.style.pointerEvents = 'none';
                document.body.appendChild(iframe);
            }
            iframe.onload = function() {
                setTimeout(function() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        console.error('Print iframe error:', e);
                    }
                }, 300);
            };
            iframe.src = url;
        }
    </script>
</body>
</html>
