/**
 * Prowave ERP — Toast notifications (Toastr wrapper)
 */
import toastr from 'toastr';

export function configureToastr() {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 4000,
        extendedTimeOut: 1500,
        newestOnTop: true,
        showDuration: 200,
        hideDuration: 200,
        progressBar: true,
    };
}

export function showToast(type = 'success', message = '', title = '') {
    if (type === 'success') {
        toastr.success(message, title);
    } else if (type === 'error') {
        toastr.error(message, title);
    } else if (type === 'warning') {
        toastr.warning(message, title);
    } else if (type === 'info') {
        toastr.info(message, title);
    }
}

export function initToast() {
    configureToastr();

    document.addEventListener('DOMContentLoaded', () => {
        const flash = document.querySelector('[data-flash-toast]');
        if (!flash) {
            return;
        }

        showToast(flash.dataset.flashType, flash.dataset.flashMessage);
    });
}
