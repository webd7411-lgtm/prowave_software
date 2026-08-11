/**
 * Prowave ERP — DataTables configuration
 * Search, sort, pagination, column visibility, export (CSV/PDF/Excel), print.
 */
import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

window.jQuery = window.$ = $;
window.DataTable = DataTable;
$.fn.DataTable = DataTable;
$.fn.dataTable = DataTable;
$.fn.dataTable.ext.errMode = 'none';

// pdfmake 0.2.x wraps fonts as { pdfMake: { vfs } }; 0.3.x exports vfs directly
if (pdfFonts?.pdfMake?.vfs) {
    pdfMake.vfs = pdfFonts.pdfMake.vfs;
} else if (typeof pdfMake.addVirtualFileSystem === 'function') {
    pdfMake.addVirtualFileSystem(pdfFonts);
} else {
    pdfMake.vfs = pdfFonts;
}

DataTable.Buttons.jszip(JSZip);
DataTable.Buttons.pdfMake(pdfMake);

export function initDataTables() {
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
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader = {
                            fillColor: '#2563EB',
                            color: '#ffffff',
                            bold: true,
                            fontSize: 8,
                        };
                        doc.defaultStyle = { ...doc.defaultStyle, fontSize: 8 };
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
        window.Prowave.dataTables.push({ instance: dt, table });
    });
}
