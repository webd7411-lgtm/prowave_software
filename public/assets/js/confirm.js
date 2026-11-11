/**
 * Prowave ERP — Confirm dialogs (SweetAlert2 wrapper)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    function baseOptions() {
        return {
            customClass: {
                popup: 'swal-popup',
                confirmButton: 'btn btn-primary swal-confirm-btn',
                cancelButton: 'btn btn-outline swal-cancel-btn',
            },
            buttonsStyling: false,
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            allowEscapeKey: false,
            reverseButtons: true,
        };
    }

    function showConfirm({
        title = 'Are you sure?',
        text = 'This action cannot be undone.',
        icon = 'warning',
        confirmText = 'Yes, continue',
        cancelText = 'Cancel',
        danger = true,
    } = {}) {
        if (!window.Swal) {
            return Promise.resolve({ isConfirmed: window.confirm(text || title) });
        }
        return window.Swal.fire({
            ...baseOptions(),
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            customClass: {
                ...baseOptions().customClass,
                confirmButton: danger ? 'btn btn-danger swal-confirm-btn' : 'btn btn-primary swal-confirm-btn',
            },
        });
    }

    function initConfirm() {
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-confirm]');
            if (trigger) {
                event.preventDefault();

                const form = trigger.closest('form');

                showConfirm({
                    title: trigger.dataset.confirmTitle || 'Are you sure?',
                    text: trigger.dataset.confirmText || 'This action cannot be undone.',
                    icon: trigger.dataset.confirmIcon || 'warning',
                    confirmText: trigger.dataset.confirmConfirmText || 'Yes, proceed',
                    cancelText: trigger.dataset.confirmCancelText || 'Cancel',
                    danger: trigger.dataset.confirmDanger !== 'false',
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    if (trigger.dataset.formSubmit) {
                        document.querySelector(trigger.dataset.formSubmit)?.submit();
                    } else if (form) {
                        form.submit();
                    } else if (trigger.dataset.confirmAction) {
                        window.location.href = trigger.dataset.confirmAction;
                    }
                });

                return;
            }

            const submitTrigger = event.target.closest('[data-form-submit]');
            if (submitTrigger) {
                event.preventDefault();
                document.querySelector(submitTrigger.dataset.formSubmit)?.submit();
            }
        });
    }

    window.showConfirm = showConfirm;
    window.Prowave.showConfirm = showConfirm;
    window.Prowave.initConfirm = initConfirm;
})(window, document);
