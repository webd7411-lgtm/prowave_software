@php
    $assetVer = '1.0.3';
    $includeAppAssets = $includeAppAssets ?? true;
    $includePdfMake = $includePdfMake ?? request()->routeIs('admin.departments.*', 'admin.roles.*');
@endphp

@if ($includeAppAssets)
    <script src="{{ asset('assets/bundles/vendor-app.min.js') }}?v={{ $assetVer }}"></script>
    @if ($includePdfMake)
        <script src="{{ asset('assets/bundles/vendor-pdfmake.min.js') }}?v={{ $assetVer }}"></script>
    @endif
    <script src="{{ asset('assets/bundles/prowave-app.js') }}?v={{ $assetVer }}"></script>
@else
    <script src="{{ asset('assets/bundles/vendor-auth.min.js') }}?v={{ $assetVer }}"></script>
    <script src="{{ asset('assets/bundles/prowave-auth.js') }}?v={{ $assetVer }}"></script>
@endif
