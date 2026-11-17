@php
    $assetVer = '1.0.8';
    $includeAppAssets = $includeAppAssets ?? true;
@endphp

<link rel="stylesheet" href="{{ asset('assets/bundles/' . ($includeAppAssets ? 'vendor-app.min.css' : 'vendor-auth.min.css')) }}?v={{ $assetVer }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ $assetVer }}">

{{-- PWA Manifest & App Icons --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#2563eb">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Prowave">
<link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icons/icon-512x512.png') }}">
