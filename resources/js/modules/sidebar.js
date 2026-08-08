/**
 * Prowave ERP — Sidebar behaviour
 * Collapsible, persistent state, mobile drawer, submenu toggles, search filter.
 */
const COLLAPSE_KEY = 'pw-sidebar-collapsed';
const OPEN_MENUS_KEY = 'pw-sidebar-open';

function getBreakpoint() {
    return window.matchMedia('(max-width: 991.98px)');
}

export function isMobile() {
    return getBreakpoint().matches;
}

export function toggleSidebar() {
    if (isMobile()) {
        document.body.classList.toggle('sidebar-mobile-open');
        return;
    }

    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem(COLLAPSE_KEY, document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
}

export function openSidebar() {
    document.body.classList.add('sidebar-mobile-open');
}

export function closeSidebar() {
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

export function initSidebar() {
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
            if (isMobile() || !document.body.classList.contains('sidebar-collapsed')) {
                event.preventDefault();
                link.parentElement.classList.toggle('open');
                persistOpenMenus();
            }
        });
    });

    getBreakpoint().addEventListener('change', () => {
        if (!isMobile()) {
            closeSidebar();
        }
    });

    const searchInput = document.querySelector('[data-sidebar-search]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();

            document.querySelectorAll('.sidebar-nav .nav-link').forEach((link) => {
                const text = link.textContent.trim().toLowerCase();
                const parentItem = link.closest('.nav-item');

                if (!query) {
                    if (parentItem) {
                        parentItem.style.display = '';
                        parentItem.classList.remove('open');
                    }
                    return;
                }

                if (parentItem) {
                    const match = text.includes(query);
                    parentItem.style.display = match ? '' : 'none';
                    if (match) {
                        parentItem.classList.add('open');
                    }
                }
            });

            document.querySelectorAll('.sidebar-nav .nav-section').forEach((section) => {
                const hasVisible = [...section.nextElementSibling?.querySelectorAll('.nav-item') ?? []]
                    .some((item) => item.style.display !== 'none');
                section.style.display = hasVisible ? '' : 'none';
            });
        });
    }

    if (!isMobile() && localStorage.getItem(COLLAPSE_KEY) === '1') {
        document.body.classList.add('sidebar-collapsed');
    }

    restoreOpenMenus();
}
