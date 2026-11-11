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
