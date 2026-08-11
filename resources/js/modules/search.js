/**
 * Prowave ERP — Global search (grouped results, keyboard shortcut)
 */
import * as bootstrap from 'bootstrap';

let debounceTimer = null;

export function initSearch() {
    const modalEl = document.getElementById('globalSearchModal');
    if (!modalEl) {
        return;
    }

    const input = modalEl.querySelector('input[type="search"]');
    const results = modalEl.querySelector('[data-search-results]');
    const empty = modalEl.querySelector('[data-search-empty]');
    const modal = new bootstrap.Modal(modalEl);
    let controller = null;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-search-open]');
        if (trigger) {
            event.preventDefault();
            modal.show();
            setTimeout(() => input?.focus(), 250);
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            modal.show();
            setTimeout(() => input?.focus(), 250);
        }
        if (event.key === 'Escape') {
            modal.hide();
        }
    });

    if (!input) {
        return;
    }

    const render = (groups) => {
        results.innerHTML = '';
        empty.classList.add('d-none');

        if (!groups.length) {
            empty.classList.remove('d-none');
            return;
        }

        groups.forEach((group) => {
            const section = document.createElement('div');
            section.className = 'search-group';

            const head = document.createElement('div');
            head.className = 'search-group-head';
            head.innerHTML = `<i class="bi ${group.icon} ${group.color} me-2"></i>${group.label}
                <span class="badge badge-soft-secondary rounded-pill ms-auto">${group.count}</span>`;
            section.appendChild(head);

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

        fetch(`${modalEl.dataset.searchUrl}?q=${encodeURIComponent(term)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then((res) => res.json())
            .then((data) => render(data.groups || []))
            .catch(() => {
                if (controller.signal.aborted) return;
            });
    };

    input.addEventListener('input', () => {
        const term = input.value.trim();
        clearTimeout(debounceTimer);

        if (term.length < 2) {
            results.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }

        debounceTimer = setTimeout(() => search(term), 250);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const first = results.querySelector('.search-result');
            if (first) {
                window.location.href = first.href;
            }
        }
    });
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
}
