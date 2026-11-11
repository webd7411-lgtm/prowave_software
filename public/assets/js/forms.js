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
