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
/**
 * Prowave ERP — Forms behaviour
 * Auto-hide validation errors, password toggles, select2 + flatpickr init, loading buttons.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let delegatedListenersBound = false;

    function initForms() {
        initPasswordToggles();
        initLoadingButtons();
        initAutoDismissErrors();
        initSelect2();
        initFlatpickr();
        initModalSelect2();
        initParticipantCheckboxes();
        initDepartmentDesignationFilter();
        delegatedListenersBound = true;
    }

    function initModalSelect2() {
        const $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.select2) {
            return;
        }

        if (!delegatedListenersBound) {
            // Prevent Bootstrap modal from trapping focus away from Select2 search inputs
            document.addEventListener('focusin', (e) => {
                if (e.target.closest && e.target.closest('.select2-container')) {
                    e.stopPropagation();
                }
            }, true);
        }

        document.querySelectorAll('.modal').forEach((modal) => {
            if (modal.dataset.pwSelect2ModalBound) {
                return;
            }
            modal.dataset.pwSelect2ModalBound = '1';

            modal.addEventListener('shown.bs.modal', () => {
                $(modal).find('.select2, .meeting-select2').each(function () {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        const isMultiple = $(this).prop('multiple');
                        $(this).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            allowClear: !isMultiple,
                            placeholder: $(this).data('placeholder') || 'Select an option',
                            dropdownParent: $(modal),
                            closeOnSelect: !isMultiple,
                            minimumResultsForSearch: 0,
                        });
                    }
                });
            });
        });
    }

    function initPasswordToggles() {
        if (delegatedListenersBound) {
            return;
        }

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
        if (delegatedListenersBound) {
            return;
        }

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

                setTimeout(() => { button.disabled = true; }, 0);
            });
        });
    }

    function initAutoDismissErrors() {
        if (delegatedListenersBound) {
            return;
        }

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
        const $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.select2) {
            return;
        }

        if (!delegatedListenersBound) {
            // Prevent Enter key in Select2 search input from submitting the form unexpectedly
            $(document).on('keydown', '.select2-search__field', function (e) {
                if (e.which === 13) {
                    e.stopPropagation();
                }
            });
        }

        $(document).find('select.select2:not(.select2-hidden-accessible)').each(function () {
            if ($(this).closest('.modal').length) {
                return;
            }
            const isMultiple = $(this).prop('multiple');
            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: !isMultiple,
                placeholder: $(this).data('placeholder') || 'Select an option',
                closeOnSelect: !isMultiple,
                minimumResultsForSearch: 0,
            });
        });
    }

    function initFlatpickr() {
        if (typeof window.flatpickr !== 'function') {
            return;
        }

        document.querySelectorAll('[data-flatpickr]').forEach((element) => {
            if (element._flatpickr) {
                return;
            }
            window.flatpickr(element, {
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

    function initParticipantCheckboxes() {
        if (delegatedListenersBound) {
            return;
        }

        document.addEventListener('input', (e) => {
            if (e.target.matches('.participant-search-input')) {
                const query = e.target.value.toLowerCase().trim();
                const container = document.querySelector(e.target.dataset.target);
                if (!container) return;

                container.querySelectorAll('.participant-item').forEach((item) => {
                    const name = item.dataset.name || '';
                    item.style.display = name.includes(query) ? 'flex' : 'none';
                });
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.matches('.participant-checkbox')) {
                const counterSel = e.target.dataset.counter;
                if (!counterSel) return;
                const counterEl = document.querySelector(counterSel);
                if (!counterEl) return;

                const container = e.target.closest('.card');
                const checkedCount = container ? container.querySelectorAll('.participant-checkbox:checked').length : 0;
                counterEl.textContent = `${checkedCount} selected`;
            }
        });
    }

    function initDepartmentDesignationFilter() {
        const $ = window.jQuery;
        if (!$) {
            return;
        }

        const $departmentSelect = $('#department_id');
        const $designationSelect = $('#designation');

        if ($departmentSelect.length && $designationSelect.length && !$departmentSelect.data('pw-filter-bound')) {
            $departmentSelect.data('pw-filter-bound', true);

            // Keep a copy of all original designation options
            const originalOptions = $designationSelect.find('option').clone();

            function filterDesignations() {
                const deptId = $departmentSelect.val();

                // Destroy Select2 before modifying options
                if ($designationSelect.hasClass('select2-hidden-accessible')) {
                    $designationSelect.select2('destroy');
                }

                // Clear designation select
                $designationSelect.empty();

                // Re-append options
                originalOptions.each(function () {
                    const optionDeptId = $(this).attr('data-department-id');
                    // Always append the empty option/placeholder
                    if (!$(this).val()) {
                        $designationSelect.append($(this).clone());
                    } else if (!deptId || optionDeptId == deptId) {
                        $designationSelect.append($(this).clone());
                    }
                });

                // Re-initialize Select2
                if ($.fn.select2) {
                    $designationSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: $designationSelect.data('placeholder') || 'Select designation',
                        allowClear: true,
                        minimumResultsForSearch: 0,
                    });
                }
            }

            // Run filter on load to handle pre-selected values
            filterDesignations();

            // Listen for changes on department select
            $departmentSelect.on('change', function () {
                filterDesignations();
                $designationSelect.val('').trigger('change.select2');
            });
        }
    }

    window.Prowave.initForms = initForms;
})(window, document);
/**
 * Prowave ERP — Global initialisation
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let fullscreenBound = false;

    function initFullscreen() {
        if (fullscreenBound) {
            return;
        }
        fullscreenBound = true;

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

    function init() {
        const pw = window.Prowave;

        pw.initTheme?.();
        pw.initSidebar?.();
        pw.initToast?.();
        pw.initConfirm?.();
        pw.initForms?.();
        pw.initDataTables?.();
        pw.initCharts?.();
        initCounters();
        initFullscreen();
        pw.initCalendar?.();
        pw.initSearch?.();
        pw.initRealtimeNotifications?.();

        document.addEventListener('page-content-loaded', () => {
            pw.initForms?.();
            pw.initDataTables?.();
            pw.initCharts?.();
            initCounters();
        });
    }

    window.Prowave.init = init;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
