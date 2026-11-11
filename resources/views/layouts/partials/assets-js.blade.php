@php
    $assetVer = '1.0.0';
    $includeAppAssets = $includeAppAssets ?? true;
@endphp

{{-- Vendor JS (jQuery must load first because Toastr, Select2, and DataTables depend on it) --}}
<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/vendor/toastr/toastr.min.js') }}?v={{ $assetVer }}"></script>

@if ($includeAppAssets)
    <script src="{{ asset('assets/vendor/select2/select2.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/jszip/jszip.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/pdfmake.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/pdfmake/vfs_fonts.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.responsive.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/responsive.bootstrap5.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.buttons.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.bootstrap5.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.colVis.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.html5.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.print.min.js') }}?v={{ $assetVer }}"></script>
@endif

{{-- Prowave ERP Modules --}}
<script src="{{ asset('assets/js/theme.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/js/toast.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/js/confirm.js') }}?v={{ $assetVer }}"></script>
<script src="{{ asset('assets/js/forms.js') }}?v={{ $assetVer }}"></script>

@if ($includeAppAssets)
    <script src="{{ asset('assets/js/sidebar.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/js/datatable.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/js/charts.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/js/calendar.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/js/search.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/js/notifications.js') }}?v={{ $assetVer }}"></script>
@endif

<script src="{{ asset('assets/js/init.js') }}?v={{ $assetVer }}"></script>
