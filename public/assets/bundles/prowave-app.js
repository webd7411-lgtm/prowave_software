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
/**
 * Prowave ERP — DataTables configuration
 * Search, sort, pagination, column visibility, export (CSV/PDF/Excel), print.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    const $ = window.jQuery || window.$;
    const DataTable = window.DataTable || ($ && $.fn && $.fn.DataTable);

    if ($ && DataTable) {
        window.DataTable = DataTable;
        $.fn.DataTable = DataTable;
        $.fn.dataTable = DataTable;
        if ($.fn.dataTable.ext) {
            $.fn.dataTable.ext.errMode = 'none';
        }
    }

    if (window.pdfMake && typeof window.vfs !== 'undefined') {
        if (typeof window.pdfMake.addVirtualFileSystem === 'function') {
            window.pdfMake.addVirtualFileSystem(window.vfs);
        } else if (!window.pdfMake.vfs) {
            window.pdfMake.vfs = window.vfs;
        }
    }

    if (DataTable && DataTable.Buttons) {
        if (window.JSZip && typeof DataTable.Buttons.jszip === 'function') {
            DataTable.Buttons.jszip(window.JSZip);
        }
        if (window.pdfMake && typeof DataTable.Buttons.pdfMake === 'function') {
            DataTable.Buttons.pdfMake(window.pdfMake);
        }
    }

    function initDataTables() {
        if (!$ || !$.fn || !$.fn.DataTable) {
            return;
        }

        $(document).find('table.data-table').each(function () {
            const table = $(this);

            if (table.data('dt-initialized')) {
                return;
            }
            table.data('dt-initialized', true);

            const options = table.data('dt-options') || {};

            const baseOptions = {
                responsive: true,
                autoWidth: false,
                order: [[table.data('dt-order-col') || 0, table.data('dt-order-dir') || 'asc']],
                pageLength: table.data('dt-length') || 15,
                lengthMenu: [10, 15, 25, 50, 100],
                language: {
                    search: '',
                    searchPlaceholder: 'Search records...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    infoEmpty: 'No records found',
                    infoFiltered: '(filtered from _MAX_ total records)',
                    zeroRecords: 'No matching records found',
                    paginate: {
                        first: 'First',
                        previous: 'Prev',
                        next: 'Next',
                        last: 'Last',
                    },
                },
                dom: "<'dt-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3'B>" +
                     "<'d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3'" +
                     "<'d-flex align-items-center gap-2'l><'d-flex align-items-center'f>>" +
                     "<'table-responsive't>" +
                     "<'d-flex flex-wrap justify-content-between align-items-center mt-3'i p>",
                buttons: [
                    {
                        text: '<i class="bi bi-funnel"></i>',
                        extend: 'colvis',
                        className: 'btn btn-soft-primary btn-sm',
                    },
                    {
                        text: '<i class="bi bi-file-earmark-excel"></i>',
                        extend: 'excelHtml5',
                        className: 'btn btn-soft-success btn-sm',
                        exportOptions: { columns: ':visible' },
                    },
                    {
                        text: '<i class="bi bi-file-earmark-pdf"></i>',
                        extend: 'pdfHtml5',
                        className: 'btn btn-soft-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: ':visible' },
                        customize(doc) {
                            doc.defaultStyle.fontSize = 9;
                            doc.styles.tableHeader = {
                                fillColor: '#2563EB',
                                color: '#ffffff',
                                bold: true,
                                fontSize: 10,
                                alignment: 'left',
                                margin: [4, 4, 4, 4],
                            };
                            if (doc.content[0] && doc.content[0].text) {
                                doc.content[0].alignment = 'center';
                                doc.content[0].fontSize = 16;
                                doc.content[0].bold = true;
                                doc.content[0].margin = [0, 0, 0, 20];
                            }
                            if (doc.content[1] && doc.content[1].table) {
                                const colCount = doc.content[1].table.body[0].length;
                                doc.content[1].table.widths = Array(colCount).fill('*');

                                doc.content[1].table.body.forEach((row) => {
                                    row.forEach((cell) => {
                                        if (typeof cell === 'object' && cell !== null) {
                                            cell.margin = [4, 4, 4, 4];
                                        }
                                    });
                                });
                            }

                            doc.styles.tableBodyOdd = {
                                fillColor: '#f8f9fa',
                            };
                            doc.styles.tableBodyEven = {
                                fillColor: '#ffffff',
                            };
                        },
                    },
                    {
                        text: '<i class="bi bi-printer"></i>',
                        extend: 'print',
                        className: 'btn btn-outline btn-sm',
                        exportOptions: { columns: ':visible' },
                    },
                ],
            };

            const dt = table.DataTable($.extend(true, baseOptions, options));

            if (table.data('dt-bulk-target')) {
                table.on('change', 'input.dt-checkbox', function () {
                    const checked = table.$('input.dt-checkbox:checked').length;
                    const btn = $(table.data('dt-bulk-target'));
                    if (checked > 0) {
                        btn.removeClass('d-none');
                        btn.find('[data-bulk-count]').text(checked);
                    } else {
                        btn.addClass('d-none');
                    }
                });
            }

            window.Prowave.dataTables = window.Prowave.dataTables || [];
            window.Prowave.dataTables.push({ instance: dt, table: table });
        });
    }

    window.Prowave.initDataTables = initDataTables;
})(window, document);
/**
 * Prowave ERP — Charts (Chart.js)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let charts = [];
    let themeObserverAttached = false;

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    function destroyChart(canvasId) {
        charts = charts.filter(({ id, chart }) => {
            if (id === canvasId) {
                chart.destroy();
                return false;
            }
            return true;
        });
    }

    function createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart === 'undefined') {
            return null;
        }

        destroyChart(canvasId);

        const opts = config.options || {};
        const merged = {
            responsive: true,
            maintainAspectRatio: false,
            ...config,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...opts,
                plugins: {
                    ...(opts.plugins || {}),
                    legend: {
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 16,
                            color: isDarkTheme() ? '#94a3b8' : '#64748b',
                            font: { size: 12, family: 'Inter, system-ui, sans-serif' },
                        },
                        ...(opts.plugins?.legend || {}),
                    },
                    tooltip: {
                        backgroundColor: isDarkTheme() ? '#1a2740' : '#0f172a',
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                        ...(opts.plugins?.tooltip || {}),
                    },
                },
                scales: {
                    ...(opts.scales || {}),
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: isDarkTheme() ? '#64748b' : '#94a3b8',
                            font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                        },
                        border: { display: false },
                        ...(opts.scales?.x || {}),
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: isDarkTheme() ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.05)',
                        },
                        ticks: {
                            color: isDarkTheme() ? '#64748b' : '#94a3b8',
                            font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                            callback: (value) => value.toLocaleString(),
                        },
                        border: { display: false },
                        ...(opts.scales?.y || {}),
                    },
                },
                animation: {
                    duration: 900,
                    easing: 'easeInOutQuart',
                    ...(opts.animation || {}),
                },
            },
            data: config.data,
        };

        const chart = new window.Chart(canvas, merged);
        charts.push({ id: canvasId, chart });

        return chart;
    }

    function applyThemeToCharts() {
        charts.forEach(({ chart }) => {
            const dark = isDarkTheme();
            const legendColor = dark ? '#94a3b8' : '#64748b';
            const tickColor = dark ? '#64748b' : '#94a3b8';
            const gridColor = dark ? 'rgba(148,163,184,0.08)' : 'rgba(15,23,42,0.05)';

            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = legendColor;
            }
            if (chart.options.plugins?.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = dark ? '#1a2740' : '#0f172a';
            }
            if (chart.options.scales?.x?.ticks) {
                chart.options.scales.x.ticks.color = tickColor;
            }
            if (chart.options.scales?.y?.ticks) {
                chart.options.scales.y.ticks.color = tickColor;
            }
            if (chart.options.scales?.y?.grid) {
                chart.options.scales.y.grid.color = gridColor;
            }
            chart.update();
        });
    }

    function initCharts() {
        document.querySelectorAll('[data-chart]').forEach((element) => {
            const type = element.dataset.chart;
            const labels = JSON.parse(element.dataset.chartLabels || '[]');
            const datasets = JSON.parse(element.dataset.chartDatasets || '[]');

            createChart(element.id, {
                type,
                data: { labels, datasets },
            });
        });

        if (!themeObserverAttached) {
            themeObserverAttached = true;
            const observer = new MutationObserver(() => applyThemeToCharts());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
            window.addEventListener('pw:theme-changed', applyThemeToCharts);
        }
    }

    window.createChart = createChart;
    window.destroyChart = destroyChart;
    window.Prowave.createChart = createChart;
    window.Prowave.destroyChart = destroyChart;
    window.Prowave.initCharts = initCharts;
})(window, document);
/**
 * Prowave ERP — Global calendar (month / week / day / agenda)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    function initCalendar() {
        const root = document.querySelector('[data-calendar]');
        if (!root) {
            return;
        }

        const state = {
            view: root.dataset.calendarView || 'month',
            current: new Date(),
            events: [],
        };

        const elViewBtns = document.querySelectorAll('[data-calendar-view-btn]');
        const elTitle = document.getElementById('calendar-title');
        const elGrid = document.getElementById('calendar-grid');
        const elSidebar = document.getElementById('calendar-upcoming');

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function fmtDate(d) {
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        function startOfRange() {
            const d = new Date(state.current);
            if (state.view === 'month') {
                d.setDate(1);
                d.setDate(1 - d.getDay());
            } else if (state.view === 'week') {
                d.setDate(d.getDate() - d.getDay());
            } else {
                d.setHours(0, 0, 0, 0);
            }
            return d;
        }

        function endOfRange() {
            const start = startOfRange();
            const d = new Date(start);
            if (state.view === 'month') {
                d.setDate(d.getDate() + 41);
            } else if (state.view === 'week') {
                d.setDate(d.getDate() + 6);
            } else {
                d.setHours(23, 59, 59, 999);
            }
            return d;
        }

        async function load() {
            if (!elGrid) return;
            elGrid.innerHTML = `
                <div class="p-5 text-center">
                    <div class="spinner-border text-primary" role="status" aria-label="Loading calendar"></div>
                </div>`;

            try {
                const start = fmtDate(startOfRange());
                const end = fmtDate(endOfRange());
                const res = await fetch(root.dataset.calendarUrl + `?start=${start}&end=${end}`, {
                    headers: { Accept: 'application/json' },
                });
                if (res.ok) {
                    state.events = await res.json();
                } else {
                    state.events = [];
                }
            } catch (e) {
                console.error('Calendar fetch error:', e);
                state.events = [];
            }
            render();
        }

        function eventsFor(dateStr, includeTime = false) {
            const dateKey = dateStr.slice(0, 10);
            return state.events.filter((e) => {
                const start = String(e.start || '').slice(0, 10);
                const end = String(e.end || '').slice(0, 10);
                if (includeTime && !e.allDay) {
                    return start === dateKey;
                }
                return start <= dateKey && (!end || end >= dateKey);
            });
        }

        function eventChip(e) {
            const link = document.createElement('a');
            link.className = 'cal-chip';
            link.style.background = e.color || '#2563EB';
            link.style.color = '#ffffff';
            link.textContent = `${e.type ? `[${e.type}] ` : ''}${e.title}`;
            link.title = `${e.title}${e.project ? ` — ${e.project}` : ''}`;
            link.href = e.url ? e.url : 'javascript:void(0)';
            return link;
        }

        function renderMonth() {
            const start = startOfRange();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const header = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
                .map((d) => `<div class="cal-col-head">${d}</div>`)
                .join('');

            let cells = '';
            for (let i = 0; i < 42; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const key = fmtDate(d);
                const inMonth = d.getMonth() === state.current.getMonth();
                const isToday = fmtDate(today) === key;
                const events = eventsFor(key).slice(0, 4);

                cells += `
                    <div class="cal-cell ${inMonth ? '' : 'cal-cell-muted'} ${isToday ? 'cal-cell-today' : ''}">
                        <div class="cal-cell-date">${d.getDate()}</div>
                        <div class="cal-cell-events">
                            ${events.map((e) => eventChip(e).outerHTML).join('')}
                            ${eventsFor(key).length > 4 ? `<div class="cal-more">+${eventsFor(key).length - 4} more</div>` : ''}
                        </div>
                    </div>`;
            }

            elGrid.innerHTML = `<div class="cal-grid cal-grid-month">${header}${cells}</div>`;
        }

        function renderWeek() {
            const start = startOfRange();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const header = [];
            const bodies = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const key = fmtDate(d);
                const isToday = fmtDate(today) === key;
                const events = eventsFor(key);

                header.push(`
                    <div class="cal-col-head ${isToday ? 'cal-head-today' : ''}">
                        <div>${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][d.getDay()]}</div>
                        <div class="cal-head-date">${d.getDate()}</div>
                    </div>`);
                bodies.push(`
                    <div class="cal-week-cell ${isToday ? 'cal-cell-today' : ''}">
                        ${events.map((e) => eventChip(e).outerHTML).join('') || '<span class="cal-empty-cell text-muted fs-xs">No events</span>'}
                    </div>`);
            }

            elGrid.innerHTML = `
                <div class="cal-grid cal-grid-week">${header.join('')}</div>
                <div class="cal-grid cal-grid-week">${bodies.join('')}</div>`;
        }

        function renderDay() {
            const key = fmtDate(state.current);
            const events = eventsFor(key, true).sort((a, b) => String(a.start).localeCompare(String(b.start)));

            elGrid.innerHTML = `
                <div class="cal-day-list p-3">
                    ${events.length ? events.map((e) => `
                        <div class="cal-day-item p-2 mb-2 rounded bg-body-tertiary" style="border-left: 4px solid ${e.color || '#2563EB'}">
                            <div class="cal-day-time fw-500 fs-xs text-muted mb-1">${e.allDay ? 'All day' : String(e.start || '').slice(11, 16) || '—'}</div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="${e.url || 'javascript:void(0)'}" class="fw-600 text-body text-decoration-none">${e.title}</a>
                                <div class="fs-xs text-muted">${e.type || 'Event'}${e.project ? ` · ${e.project}` : ''}</div>
                            </div>
                        </div>`).join('') : `
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                            <span class="text-muted fs-sm">No events scheduled for this day</span>
                        </div>`}
                </div>`;
        }

        function renderAgenda() {
            const sorted = [...state.events].sort((a, b) => String(a.start).localeCompare(String(b.start)));

            elGrid.innerHTML = `
                <div class="cal-day-list p-3">
                    ${sorted.length ? sorted.map((e) => `
                        <div class="cal-day-item p-2 mb-2 rounded bg-body-tertiary d-flex align-items-center gap-3" style="border-left: 4px solid ${e.color || '#2563EB'}">
                            <div class="cal-day-time fw-600 fs-xs text-primary" style="min-width: 85px;">${String(e.start || '').slice(0, 10)}</div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="${e.url || 'javascript:void(0)'}" class="fw-600 text-body text-decoration-none d-block text-truncate">${e.title}</a>
                                <div class="fs-xs text-muted">${e.type || 'Event'}${e.project ? ` · ${e.project}` : ''}</div>
                            </div>
                        </div>`).join('') : `
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                            <span class="text-muted fs-sm">No events in this range</span>
                        </div>`}
                </div>`;
        }

        function render() {
            const options = { month: 'long', year: 'numeric' };
            const weekYear = new Date(startOfRange());
            if (elTitle) {
                elTitle.textContent = state.view === 'day'
                    ? state.current.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
                    : state.view === 'week'
                        ? `Week of ${weekYear.toLocaleDateString(undefined, options)}`
                        : state.current.toLocaleDateString(undefined, options);
            }

            if (state.view === 'month') renderMonth();
            else if (state.view === 'week') renderWeek();
            else if (state.view === 'day') renderDay();
            else renderAgenda();

            elViewBtns.forEach((btn) => {
                const isSel = btn.dataset.calendarViewBtn === state.view;
                btn.classList.toggle('btn-primary', isSel);
                btn.classList.toggle('btn-outline-secondary', !isSel);
            });

            if (elSidebar) {
                const sorted = [...state.events].sort((a, b) => String(a.start).localeCompare(String(b.start)));
                elSidebar.innerHTML = sorted.slice(0, 10).map((e) => `
                    <a href="${e.url || 'javascript:void(0)'}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2">
                        <span class="cal-dot rounded-circle flex-shrink-0" style="width:10px;height:10px;background:${e.color || '#2563EB'}"></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block text-truncate fw-500 fs-sm text-body">${e.title}</span>
                            <span class="d-block fs-xs text-muted">${String(e.start || '').slice(0, 10)}${e.project ? ` · ${e.project}` : ''}</span>
                        </span>
                    </a>`).join('') || '<div class="p-3 text-muted fs-sm text-center">No upcoming events</div>';
            }
        }

        elViewBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                state.view = btn.dataset.calendarViewBtn;
                load();
            });
        });

        document.querySelectorAll('[data-calendar-nav]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = parseInt(btn.dataset.calendarNav, 10);
                const d = new Date(state.current);
                if (state.view === 'month') d.setMonth(d.getMonth() + dir);
                else if (state.view === 'week') d.setDate(d.getDate() + dir * 7);
                else d.setDate(d.getDate() + dir);
                state.current = d;
                load();
            });
        });

        document.querySelectorAll('[data-calendar-today]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.current = new Date();
                load();
            });
        });

        load();
    }

    window.Prowave.initCalendar = initCalendar;
})(window, document);
/**
 * Prowave ERP — Global search (grouped results, keyboard shortcut)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let debounceTimer = null;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value ?? '');
        return div.innerHTML;
    }

    function initSearch() {
        const modalEl = document.getElementById('globalSearchModal');
        if (!modalEl || typeof window.bootstrap === 'undefined') {
            return;
        }

        const input = modalEl.querySelector('input[type="search"]');
        const results = modalEl.querySelector('[data-search-results]');
        const empty = modalEl.querySelector('[data-search-empty]');
        const modal = new window.bootstrap.Modal(modalEl);
        let controller = null;

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-search-open]');
            if (trigger) {
                event.preventDefault();
                modal.show();
                setTimeout(() => input?.focus(), 250);
            }
        });

        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                modal.show();
                setTimeout(() => input?.focus(), 250);
            }
            if (event.key === 'Escape') {
                modal.hide();
            }
        });

        if (!input) {
            return;
        }

        const render = (groups) => {
            results.innerHTML = '';
            empty.classList.add('d-none');

            if (!groups.length) {
                empty.classList.remove('d-none');
                return;
            }

            groups.forEach((group) => {
                const section = document.createElement('div');
                section.className = 'search-group';

                const head = document.createElement('div');
                head.className = 'search-group-head';
                head.innerHTML = `<i class="bi ${group.icon} ${group.color} me-2"></i>${group.label}
                    <span class="badge badge-soft-secondary rounded-pill ms-auto">${group.count}</span>`;
                section.appendChild(head);

                group.results.forEach((item) => {
                    const row = document.createElement('a');
                    row.href = item.url || '#';
                    row.className = 'search-result';

                    const avatar = item.avatar
                        ? `<img src="${item.avatar}" class="search-avatar" alt="">`
                        : `<span class="search-avatar search-avatar-fallback">${(item.title || '?').slice(0, 2).toUpperCase()}</span>`;

                    row.innerHTML = `${avatar}
                        <span class="min-w-0">
                            <span class="d-block text-truncate fw-500 text-body">${escapeHtml(item.title)}</span>
                            <span class="d-block text-truncate fs-xs text-muted">${escapeHtml(item.subtitle || '')}</span>
                        </span>
                        <i class="bi bi-arrow-right-short text-muted"></i>`;
                    section.appendChild(row);
                });

                results.appendChild(section);
            });
        };

        const search = (term) => {
            controller?.abort();
            controller = new AbortController();

            fetch(`${modalEl.dataset.searchUrl}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then((res) => res.json())
                .then((data) => render(data.groups || []))
                .catch(() => {
                    if (controller.signal.aborted) return;
                });
        };

        input.addEventListener('input', () => {
            const term = input.value.trim();
            clearTimeout(debounceTimer);

            if (term.length < 2) {
                results.innerHTML = '';
                empty.classList.remove('d-none');
                return;
            }

            debounceTimer = setTimeout(() => search(term), 250);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                const first = results.querySelector('.search-result');
                if (first) {
                    window.location.href = first.href;
                }
            }
        });
    }

    window.Prowave.initSearch = initSearch;
})(window, document);
/**
 * Prowave ERP — Real-time notifications
 * Polls for new notifications, plays sound, shows toast + browser alert.
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    let pollTimer = null;
    let soundEnabled = true;
    let browserNotifPerm = 'default';
    let audioCtx = null;

    const seenNotifIds = new Set();
    let initialized = false;
    const POLL_INTERVAL = 45000; // 45s

    function playSound() {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const ctx = audioCtx;
            if (ctx.state === 'suspended') ctx.resume();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.35);
        } catch (e) {
            // sound is best-effort
        }
    }

    function showNotifToast(n) {
        if (!window.toastr) return;
        const html = `<div style="min-width:240px">
            <div style="display:flex;align-items:center;gap:.55rem;margin-bottom:.25rem">
                <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(78,139,255,.15);color:#7db3ff">
                    <i class="bi ${n.icon || 'bi-bell'}"></i>
                </span>
                <strong style="font-size:.9rem">${n.title}</strong>
            </div>
            ${n.message ? `<div style="font-size:.82rem;color:#94a3b8;margin-left:2.1rem">${n.message}</div>` : ''}
        </div>`;
        const t = window.toastr.info(html, '', {
            timeOut: 6000,
            extendedTimeOut: 2000,
            closeButton: true,
            positionClass: 'toast-top-right',
        });
        if (t && window.jQuery) {
            t.css('background-color', '#141e3a').css('color', '#eaeeff');
            if (n.url) {
                window.jQuery(t).css('cursor', 'pointer').on('click', () => {
                    window.location.href = n.url;
                });
            }
        }
    }

    function showBrowserNotif(n) {
        if (browserNotifPerm !== 'granted' || document.visibilityState === 'visible') return;
        try {
            const bn = new Notification(n.title, {
                body: n.message || '',
                icon: '/images/logo.png',
                tag: n.id,
            });
            bn.onclick = () => {
                window.focus();
                if (n.url) window.location.href = n.url;
                bn.close();
            };
        } catch (e) {
            // browser notification best-effort
        }
    }

    function updateBadge(count) {
        const badge = document.querySelector('.notif-badge');
        if (count > 0) {
            if (!badge) {
                const btn = document.querySelector('[data-bs-toggle="dropdown"] .bi-bell, .icon-btn .bi-bell')?.closest('.icon-btn');
                if (btn) {
                    const span = document.createElement('span');
                    span.className = 'notif-badge';
                    span.textContent = count > 99 ? '99+' : count;
                    btn.appendChild(span);
                }
            } else {
                badge.textContent = count > 99 ? '99+' : count;
            }
        } else if (badge) {
            badge.remove();
        }
    }

    async function poll() {
        try {
            const url = window.Prowave?.url ? `${window.Prowave.url}/notifications/recent` : '/notifications/recent';
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();

            updateBadge(data.unread);

            const list = data.notifications || [];

            if (!initialized) {
                list.forEach((n) => seenNotifIds.add(n.id));
                initialized = true;
                return;
            }

            const fresh = list.filter((n) => !seenNotifIds.has(n.id) && !n.read);
            list.forEach((n) => seenNotifIds.add(n.id));

            if (fresh.length > 0) {
                fresh.reverse().forEach((n) => {
                    playSound();
                    showNotifToast(n);
                    showBrowserNotif(n);
                });
            }
        } catch (e) {
            // polling is best-effort
        }
    }

    function requestBrowserPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted') {
            browserNotifPerm = 'granted';
            return;
        }
        Notification.requestPermission().then((perm) => {
            browserNotifPerm = perm;
        });
    }

    function toggleNotifSound(enabled) {
        soundEnabled = enabled;
        localStorage.setItem('pw-notif-sound', enabled ? '1' : '0');
    }

    function isNotifSoundEnabled() {
        return soundEnabled;
    }

    function initRealtimeNotifications() {
        soundEnabled = localStorage.getItem('pw-notif-sound') !== '0';

        // Sound toggle button
        const soundToggle = document.getElementById('notifSoundToggle');
        if (soundToggle) {
            const soundIcon = soundToggle.querySelector('i');
            const renderSoundBtn = () => {
                if (soundEnabled) {
                    if (soundIcon) soundIcon.className = 'bi bi-volume-up';
                    soundToggle.classList.remove('muted');
                    soundToggle.title = 'Sound on — click to mute';
                } else {
                    if (soundIcon) soundIcon.className = 'bi bi-volume-mute';
                    soundToggle.classList.add('muted');
                    soundToggle.title = 'Sound off — click to enable';
                }
            };
            renderSoundBtn();
            soundToggle.addEventListener('click', () => {
                soundEnabled = !soundEnabled;
                localStorage.setItem('pw-notif-sound', soundEnabled ? '1' : '0');
                renderSoundBtn();
                if (soundEnabled) playSound();
            });
        }

        if ('Notification' in window) {
            browserNotifPerm = Notification.permission;
        }

        const bellBtn = document.querySelector('.icon-btn .bi-bell')?.closest('.icon-btn');
        if (bellBtn && 'Notification' in window && Notification.permission === 'default') {
            bellBtn.addEventListener('click', () => {
                if (Notification.permission === 'default') {
                    Notification.requestPermission().then((perm) => {
                        browserNotifPerm = perm;
                    });
                }
            }, { once: true });
        }

        // Initial notifications are already rendered server-side in navbar; poll in background only
        pollTimer = setInterval(poll, POLL_INTERVAL);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                poll();
            }
        });
    }

    function stopRealtimeNotifications() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    window.Prowave.requestBrowserPermission = requestBrowserPermission;
    window.Prowave.toggleNotifSound = toggleNotifSound;
    window.Prowave.isNotifSoundEnabled = isNotifSoundEnabled;
    window.Prowave.initRealtimeNotifications = initRealtimeNotifications;
    window.Prowave.stopRealtimeNotifications = stopRealtimeNotifications;
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
