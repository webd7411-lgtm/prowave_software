@extends('layouts.app')

@section('title', 'Branding')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Branding</li>
                </ol>
            </nav>
            <h1 class="page-title">Branding</h1>
            <p class="page-subtitle">Keep every touchpoint on-brand — login, invoices and emails.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="Brand Assets" subtitle="Logos and icons used across the product" icon="bi-palette">
                <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data" data-loading>
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label d-block">Login Logo</label>
                            <input type="file" name="login_logo" class="form-control" accept="image/*">
                            <div class="form-text">Shown on the login page — max 2 MB.</div>
                            @if (setting('branding.login_logo'))
                                <img src="{{ asset(setting('branding.login_logo')) }}" alt="Login logo" class="mt-2 brand-preview" style="max-height: 64px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Invoice Logo</label>
                            <input type="file" name="invoice_logo" class="form-control" accept="image/*">
                            <div class="form-text">Used on invoices and quotations — max 2 MB.</div>
                            @if (setting('branding.invoice_logo'))
                                <img src="{{ asset(setting('branding.invoice_logo')) }}" alt="Invoice logo" class="mt-2 brand-preview" style="max-height: 64px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*">
                            <div class="form-text">Browser tab icon — PNG/ICO/SVG, max 1 MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primary Brand Color</label>
                            <div class="input-group">
                                <input type="color" name="primary_color" class="form-control form-control-color" value="{{ setting('branding.primary_color', '#2563EB') }}">
                                <input type="text" class="form-control" value="{{ setting('branding.primary_color', '#2563EB') }}" readonly aria-label="Primary color value">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ setting('branding.secondary_color', '#6366F1') }}">
                                <input type="text" class="form-control" value="{{ setting('branding.secondary_color', '#6366F1') }}" readonly aria-label="Secondary color value">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Footer / Signature</label>
                            <textarea name="email_footer" class="form-control" rows="3" maxlength="1000" placeholder="Best regards,&#10;Prowave Software Technology">{{ setting('branding.email_footer', '') }}</textarea>
                            <div class="form-text">Appended to outgoing system emails (plain text).</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Branding</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
