/**
 * Prowave ERP — Forms behaviour
 * Auto-hide validation errors, password toggles, select2 + flatpickr init, loading buttons.
 */
import $ from 'jquery';
import Select2 from 'select2';
import flatpickr from 'flatpickr';

export function initForms() {
    initPasswordToggles();
    initLoadingButtons();
    initAutoDismissErrors();
    initSelect2();
    initFlatpickr();
    initModalSelect2();
}

function initModalSelect2() {
    const modal = document.getElementById('meetingModal');
    if (!modal) return;

    modal.addEventListener('shown.bs.modal', () => {
        $(modal).find('.meeting-select2').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: $(this).data('placeholder') || 'Select an option',
                    dropdownParent: $(modal),
                });
            }
        });
    });
}

function initPasswordToggles() {
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-toggle-password]');
        if (!toggle) {
            return;
        }

        const input = document.getElementById(toggle.dataset.togglePassword);
        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }
    });
}

function initLoadingButtons() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('[data-loading]')) {
            return;
        }

        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.classList.add('btn-loading');

            const original = button.dataset.originalHtml;
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm me-2';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-hidden', 'true');

            if (!original) {
                button.dataset.originalHtml = button.innerHTML;
            }

            button.innerHTML = '';
            button.append(spinner, button.dataset.loadingText || 'Processing...');

            // Defer disabling until after the submit event so the form
            // actually fires — disabling synchronously can cancel submission.
            setTimeout(() => { button.disabled = true; }, 0);
        });
    });
}

function initAutoDismissErrors() {
    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!input.matches('.is-invalid')) {
            return;
        }

        input.classList.remove('is-invalid');
        const feedback = input.parentElement?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    });
}

function initSelect2() {
    $(document).find('select.select2').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            placeholder: $(this).data('placeholder') || 'Select an option',
        });
    });
}

function initFlatpickr() {
    document.querySelectorAll('[data-flatpickr]').forEach((element) => {
        flatpickr(element, {
            dateFormat: element.dataset.flatpickrFormat || 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            allowInput: true,
            defaultDate: element.dataset.flatpickrDefault || undefined,
            minDate: element.dataset.flatpickrMin || undefined,
            maxDate: element.dataset.flatpickrMax || undefined,
            enableTime: element.dataset.flatpickrTime === 'true',
        });
    });
}
