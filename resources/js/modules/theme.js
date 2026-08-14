/**
 * Prowave ERP — Premium Theme System (Light / Dark / System)
 */
export function getStoredTheme() {
    const saved = localStorage.getItem('pw-theme');
    if (saved) {
        return saved;
    }
    const userTheme = document.body?.dataset?.userTheme;
    return userTheme || 'system';
}

export function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function getActiveTheme() {
    const stored = getStoredTheme();
    return stored === 'system' ? getSystemTheme() : stored;
}

export function applyTheme() {
    const root = document.documentElement;
    const stored = getStoredTheme();
    const resolved = stored === 'system' ? getSystemTheme() : stored;

    root.setAttribute('data-theme', resolved);
    root.setAttribute('data-bs-theme', resolved);
    root.setAttribute('data-theme-preference', stored);

    document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
        const target = el.dataset.themeToggle;
        const isActive = target === stored;
        el.classList.toggle('active', isActive);
        el.setAttribute('aria-pressed', String(isActive));
    });

    window.dispatchEvent(new CustomEvent('pw:theme-changed', { detail: { theme: resolved, preference: stored } }));
}

export function setTheme(theme) {
    localStorage.setItem('pw-theme', theme);
    applyTheme();

    if (window.Prowave?.themeUrl) {
        fetch(window.Prowave.themeUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.Prowave.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ theme }),
        }).catch(() => {
            // Best-effort server sync
        });
    }
}

export function toggleDarkMode() {
    const active = getActiveTheme();
    setTheme(active === 'dark' ? 'light' : 'dark');
}

export function initTheme() {
    window.matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', () => {
            if (getStoredTheme() === 'system') {
                applyTheme();
            }
        });

    document.addEventListener('click', (event) => {
        const toggler = event.target.closest('[data-theme-toggle]');
        if (!toggler) {
            return;
        }

        const mode = toggler.dataset.themeToggle;
        if (mode === 'light' || mode === 'dark' || mode === 'system') {
            setTheme(mode);
        } else if (mode === 'toggle') {
            toggleDarkMode();
        }
    });

    applyTheme();
}

