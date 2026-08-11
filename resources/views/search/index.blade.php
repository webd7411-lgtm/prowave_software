@extends('layouts.app')

@section('title', 'Search')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search</li>
                </ol>
            </nav>
            <h1 class="page-title">Search</h1>
            <p class="page-subtitle">Search across staff, clients, leads, projects, tasks and financial records.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card search-page-card">
                <div class="card-body p-4">
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                        <input type="search" id="searchPageInput" class="form-control border-start-0"
                               placeholder="Type at least 2 characters..."
                               value="{{ $term }}" autofocus autocomplete="off"
                               data-search-url="{{ route('search.data') }}">
                    </div>
                    <div id="searchPageResults">
                        <div class="text-center text-muted py-5" id="searchPageEmpty">
                            <i class="bi bi-search fs-2 d-block mb-2"></i>
                            <span class="fs-sm">Start typing to search across the entire system.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('searchPageInput');
            const results = document.getElementById('searchPageResults');
            const empty = document.getElementById('searchPageEmpty');
            let timer = null;
            let controller = null;

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = String(value ?? '');
                return div.innerHTML;
            };

            const render = (groups) => {
                results.innerHTML = '';
                if (!groups.length) {
                    results.appendChild(empty);
                    empty.classList.remove('d-none');
                    return;
                }
                groups.forEach((group) => {
                    const section = document.createElement('div');
                    section.className = 'search-group mb-3';
                    section.innerHTML = `<div class="search-group-head"><i class="bi ${group.icon} ${group.color} me-2"></i>${group.label}
                        <span class="badge badge-soft-secondary rounded-pill ms-auto">${group.count}</span></div>`;
                    group.results.forEach((item) => {
                        const row = document.createElement('a');
                        row.href = item.url || '#';
                        row.className = 'search-result';
                        const avatar = item.avatar
                            ? `<img src="${item.avatar}" class="search-avatar" alt="">`
                            : `<span class="search-avatar search-avatar-fallback">${(item.title || '?').slice(0, 2).toUpperCase()}</span>`;
                        row.innerHTML = `${avatar}
                            <span class="min-w-0">
                                <span class="d-block text-truncate fw-500 text-body">${escapeHtml(item.title)}</span>
                                <span class="d-block text-truncate fs-xs text-muted">${escapeHtml(item.subtitle || '')}</span>
                            </span>
                            <i class="bi bi-arrow-right-short text-muted"></i>`;
                        section.appendChild(row);
                    });
                    results.appendChild(section);
                });
            };

            const search = (term) => {
                controller?.abort();
                controller = new AbortController();
                fetch(`${input.dataset.searchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                })
                    .then((res) => res.json())
                    .then((data) => render(data.groups || []))
                    .catch(() => {});
            };

            input.addEventListener('input', () => {
                const term = input.value.trim();
                clearTimeout(timer);
                if (term.length < 2) {
                    results.innerHTML = '';
                    results.appendChild(empty);
                    empty.classList.remove('d-none');
                    return;
                }
                timer = setTimeout(() => search(term), 250);
            });

            if (input.value.trim().length >= 2) {
                search(input.value.trim());
            }
        })();
    </script>
@endpush
