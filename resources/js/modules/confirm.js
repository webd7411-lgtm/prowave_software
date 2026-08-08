/**
 * Prowave ERP — Confirm dialogs (SweetAlert2 wrapper)
 */
import Swal from 'sweetalert2';

let swalInstance = null;

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

export function showConfirm({
    title = 'Are you sure?',
    text = 'This action cannot be undone.',
    icon = 'warning',
    confirmText = 'Yes, continue',
    cancelText = 'Cancel',
    danger = true,
}) {
    return Swal.fire({
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

export function initConfirm() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger) {
            return;
        }

        event.preventDefault();

        const form = trigger.closest('form');
        const isForm = Boolean(form);

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

            if (isForm) {
                form.submit();
            } else if (trigger.dataset.confirmAction) {
                window.location.href = trigger.dataset.confirmAction;
            }
        });
    });
}
