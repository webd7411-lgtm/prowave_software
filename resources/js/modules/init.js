/**
 * Prowave ERP — Global initialisation
 */
import { initTheme } from './theme';
import { initSidebar } from './sidebar';
import { initToast } from './toast';
import { initConfirm } from './confirm';
import { initForms } from './forms';
import { initDataTables } from './datatable';
import { initCharts } from './charts';
import { initCalendar } from './calendar';
import { initSearch } from './search';
import { initRealtimeNotifications } from './notifications';

function initFullscreen() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-fullscreen]');
        if (!trigger) {
            return;
        }

        const icon = trigger.querySelector('i');
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
            if (icon) {
                icon.classList.replace('bi-arrows-fullscreen', 'bi-fullscreen-exit');
            }
        } else {
            document.exitFullscreen?.();
            if (icon) {
                icon.classList.replace('bi-fullscreen-exit', 'bi-arrows-fullscreen');
            }
        }
    });

    document.addEventListener('fullscreenchange', () => {
        document.querySelectorAll('[data-fullscreen] i').forEach((icon) => {
            const isFullscreen = Boolean(document.fullscreenElement);
            icon.classList.toggle('bi-arrows-fullscreen', !isFullscreen);
            icon.classList.toggle('bi-fullscreen-exit', isFullscreen);
        });
    });
}

function initCounters() {
    document.querySelectorAll('[data-counter]').forEach((element) => {
        const target = parseInt(element.dataset.counter, 10) || 0;
        const duration = parseInt(element.dataset.counterDuration, 10) || 800;
        const suffix = element.dataset.counterSuffix || '';
        const prefix = element.dataset.counterPrefix || '';

        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.floor(eased * target);

            element.textContent = prefix + value.toLocaleString() + suffix;

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                element.textContent = prefix + target.toLocaleString() + suffix;
            }
        };

        requestAnimationFrame(tick);
    });
}

export function init() {
    initTheme();
    initSidebar();
    initToast();
    initConfirm();
    initForms();
    initDataTables();
    initCharts();
    initCounters();
    initFullscreen();
    initCalendar();
    initSearch();
    initRealtimeNotifications();

    document.addEventListener('page-content-loaded', () => {
        initForms();
        initDataTables();
        initCharts();
        initCounters();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
