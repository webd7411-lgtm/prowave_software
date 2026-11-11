/**
 * Prowave ERP — Toast notifications (Toastr wrapper)
 * jQuery and Toastr are already loaded on window before this file.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    function configureToastr() {
        if (!window.toastr) {
            return;
        }
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
            extendedTimeOut: 1500,
            newestOnTop: true,
            showDuration: 200,
            hideDuration: 200,
        };
    }

    function showToast(type = 'success', message = '', title = '') {
        if (!window.toastr) {
            return;
        }
        if (type === 'success') {
            window.toastr.success(message, title);
        } else if (type === 'error') {
            window.toastr.error(message, title);
        } else if (type === 'warning') {
            window.toastr.warning(message, title);
        } else if (type === 'info') {
            window.toastr.info(message, title);
        }
    }

    function showFlashToast() {
        const flash = document.querySelector('[data-flash-toast]');
        if (!flash) {
            return;
        }
        showToast(flash.dataset.flashType, flash.dataset.flashMessage);
    }

    function initToast() {
        configureToastr();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showFlashToast);
        } else {
            showFlashToast();
        }
    }

    configureToastr();

    window.showToast = showToast;
    window.Prowave.configureToastr = configureToastr;
    window.Prowave.showToast = showToast;
    window.Prowave.initToast = initToast;
})(window, document);
