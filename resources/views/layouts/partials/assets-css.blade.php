@php
    $assetVer = '1.0.0';
    $includeAppAssets = $includeAppAssets ?? true;
@endphp

<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-custom.css') }}?v={{ $assetVer }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}?v={{ $assetVer }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}?v={{ $assetVer }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/toastr/toastr.min.css') }}?v={{ $assetVer }}">
@if ($includeAppAssets)
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2.min.css') }}?v={{ $assetVer }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2-bootstrap-5-theme.min.css') }}?v={{ $assetVer }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}?v={{ $assetVer }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') }}?v={{ $assetVer }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/buttons.bootstrap5.min.css') }}?v={{ $assetVer }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/responsive.bootstrap5.min.css') }}?v={{ $assetVer }}">
@endif
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ $assetVer }}">
