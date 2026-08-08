/**
 * Prowave ERP — Theme system (light / dark / system)
 */
export function getStoredTheme() {
    const saved = localStorage.getItem('pw-theme');
    if (saved) {
        return saved;
    }

    const userTheme = document.body.dataset.userTheme;
    return userTheme || 'system';
}

export function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function getActiveTheme() {
    const theme = getStoredTheme();
    return theme === 'system' ? getSystemTheme() : theme;
}

export function applyTheme(theme = getActiveTheme()) {
    const root = document.documentElement;
    const resolved = theme === 'system' ? getSystemTheme() : theme;

    root.setAttribute('data-theme', resolved);

    document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
        const target = el.dataset.themeToggle;
        if (target === 'dark') {
            el.classList.toggle('active', resolved === 'dark');
            el.setAttribute('aria-pressed', String(resolved === 'dark'));
        }
    });

    window.dispatchEvent(new CustomEvent('pw:theme-changed', { detail: { theme: resolved } }));
}

export function setTheme(theme) {
    localStorage.setItem('pw-theme', theme);
    applyTheme(theme);

    fetch(window.Prowave.themeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.Prowave.csrfToken,
        },
        body: JSON.stringify({ theme }),
    }).catch(() => {
        // Persisting theme is best-effort; the local preference still applies.
    });
}

export function toggleDarkMode() {
    setTheme(getActiveTheme() === 'dark' ? 'light' : 'dark');
}

export function initTheme() {
    if (!window.Prowave.themeEnabled) {
        applyTheme('light');
        return;
    }

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

        if (toggler.dataset.themeToggle === 'dark') {
            toggleDarkMode();
        } else if (toggler.dataset.themeToggle === 'system') {
            setTheme('system');
        }
    });

    applyTheme();
}
