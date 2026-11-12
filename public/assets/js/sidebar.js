/**
 * Prowave ERP — Sidebar behaviour
 * Collapsible, persistent state, mobile drawer, submenu toggles, search filter.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    const COLLAPSE_KEY = 'pw-sidebar-collapsed';
    const OPEN_MENUS_KEY = 'pw-sidebar-open';

    function getBreakpoint() {
        return window.matchMedia('(max-width: 991.98px)');
    }

    function isMobile() {
        return getBreakpoint().matches;
    }

    function toggleSidebar() {
        if (isMobile()) {
            document.body.classList.toggle('sidebar-mobile-open');
            return;
        }

        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem(COLLAPSE_KEY, document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    }

    function openSidebar() {
        document.body.classList.add('sidebar-mobile-open');
    }

    function closeSidebar() {
        document.body.classList.remove('sidebar-mobile-open');
    }

    function restoreOpenMenus() {
        let openMenus = [];
        try {
            openMenus = JSON.parse(localStorage.getItem(OPEN_MENUS_KEY) || '[]');
        } catch (error) {
            openMenus = [];
        }

        openMenus.forEach((id) => {
            const item = document.getElementById(id);
            if (item) {
                item.classList.add('open');
            }
        });
    }

    function persistOpenMenus() {
        const open = [...document.querySelectorAll('.nav-item.has-submenu.open')].map((el) => el.id);
        localStorage.setItem(OPEN_MENUS_KEY, JSON.stringify(open));
    }

    function initSidebar() {
        const toggleBtn = document.querySelector('[data-sidebar-toggle]');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
                closeSidebar();
            }
        });

        document.querySelectorAll('.nav-item.has-submenu > .nav-link').forEach((link) => {
            link.addEventListener('click', (event) => {
                // Submenu triggers are intentionally non-navigation links. Always
                // toggle them, including when the desktop sidebar is collapsed.
                event.preventDefault();
                link.parentElement.classList.toggle('open');
                persistOpenMenus();
            });
        });

        getBreakpoint().addEventListener('change', () => {
            if (!isMobile()) {
                closeSidebar();
            }
        });

        document.querySelectorAll('.sidebar-nav a.nav-link[href]:not([href="#"])').forEach((link) => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    closeSidebar();
                }
            });
        });

        const searchInput = document.querySelector('[data-sidebar-search]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();

                if (!query) {
                    document.querySelectorAll('.sidebar-nav .nav-item').forEach((item) => {
                        item.style.display = '';
                        item.classList.remove('open');
                    });
                    document.querySelectorAll('.sidebar-nav .nav-section').forEach((section) => {
                        section.style.display = '';
                    });
                    return;
                }

                document.querySelectorAll('.sidebar-nav > .nav.flex-column > .nav-item').forEach((item) => {
                    const link = item.querySelector(':scope > .nav-link');
                    const text = link ? link.textContent.trim().toLowerCase() : '';
                    const match = text.includes(query);
                    item.style.display = match ? '' : 'none';
                    if (match) {
                        item.classList.add('open');
                    }
                });

                document.querySelectorAll('.sidebar-nav .nav-section').forEach((section) => {
                    let hasVisible = false;
                    let sibling = section.nextElementSibling;
                    while (sibling && !sibling.classList.contains('nav-section')) {
                        if (sibling.classList.contains('nav-item') && sibling.style.display !== 'none') {
                            hasVisible = true;
                            break;
                        }
                        sibling = sibling.nextElementSibling;
                    }
                    section.style.display = hasVisible ? '' : 'none';
                });
            });
        }

        if (!isMobile() && localStorage.getItem(COLLAPSE_KEY) === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        restoreOpenMenus();
    }

    window.Prowave.isMobile = isMobile;
    window.Prowave.toggleSidebar = toggleSidebar;
    window.Prowave.openSidebar = openSidebar;
    window.Prowave.closeSidebar = closeSidebar;
    window.Prowave.initSidebar = initSidebar;
})(window, document);
