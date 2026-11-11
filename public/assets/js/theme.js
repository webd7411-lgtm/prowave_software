/**
 * Prowave ERP — Premium Theme System (Light / Dark / System)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    function getStoredTheme() {
        const saved = localStorage.getItem('pw-theme');
        if (saved) {
            return saved;
        }
        const userTheme = document.body && document.body.dataset ? document.body.dataset.userTheme : undefined;
        return userTheme || 'system';
    }

    function getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function getActiveTheme() {
        const stored = getStoredTheme();
        return stored === 'system' ? getSystemTheme() : stored;
    }

    function applyTheme() {
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

        window.dispatchEvent(new CustomEvent('pw:theme-changed', {
            detail: { theme: resolved, preference: stored },
        }));
    }

    function setTheme(theme) {
        localStorage.setItem('pw-theme', theme);
        applyTheme();

        if (window.Prowave && window.Prowave.themeUrl) {
            fetch(window.Prowave.themeUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.Prowave.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ theme: theme }),
            }).catch(() => {
                // Best-effort server sync
            });
        }
    }

    function toggleDarkMode() {
        const active = getActiveTheme();
        setTheme(active === 'dark' ? 'light' : 'dark');
    }

    function initTheme() {
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

    window.Prowave.getStoredTheme = getStoredTheme;
    window.Prowave.getSystemTheme = getSystemTheme;
    window.Prowave.getActiveTheme = getActiveTheme;
    window.Prowave.applyTheme = applyTheme;
    window.Prowave.setTheme = setTheme;
    window.Prowave.toggleDarkMode = toggleDarkMode;
    window.Prowave.initTheme = initTheme;
})(window, document);
