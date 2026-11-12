@php
    $assetVer = '1.0.8';
    $includeAppAssets = $includeAppAssets ?? true;
@endphp

<link rel="stylesheet" href="{{ asset('assets/bundles/' . ($includeAppAssets ? 'vendor-app.min.css' : 'vendor-auth.min.css')) }}?v={{ $assetVer }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ $assetVer }}">
