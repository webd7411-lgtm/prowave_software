@extends('layouts.app')

@section('title', 'Email Settings')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Email Settings</li>
                </ol>
            </nav>
            <h1 class="page-title">Email Settings</h1>
            <p class="page-subtitle">Configure SMTP to send system emails. Credentials are stored encrypted.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="SMTP Configuration" subtitle="Outgoing mail server details" icon="bi-envelope-at">
                <form method="POST" action="{{ route('settings.email.update') }}" data-loading>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $smtp['host'] ?? '') }}" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="mail_port" class="form-control" value="{{ old('mail_port', $smtp['port'] ?? 587) }}" min="1" max="65535">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="mail_username" class="form-control" value="{{ old('mail_username', $smtp['username'] ?? '') }}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="smtp-password" name="mail_password" class="form-control" value="{{ old('mail_password') }}" placeholder="••••••••••••" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="smtp-password" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Encryption</label>
                            <select name="mail_encryption" class="form-select">
                                @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($smtp['encryption'] ?? 'tls') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-soft-info border-0 mb-0 d-flex align-items-start gap-2">
                                <i class="bi bi-shield-lock flex-shrink-0 mt-1"></i>
                                <div class="fs-sm">SMTP credentials are encrypted before storage and are never shown in plain text again.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $smtp['from_name'] ?? '') }}" placeholder="Prowave ERP">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Email</label>
                            <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $smtp['from_address'] ?? '') }}" placeholder="no-reply@prowave.com">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Email Settings</button>
                        </div>
                    </div>
                </form>
            </x-card>

            <x-card title="Send Test Email" subtitle="Verify your SMTP configuration works" icon="bi-send" class="mt-3">
                <form method="POST" action="{{ route('settings.email.test') }}" data-loading>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Send test email to</label>
                            <input type="email" name="test_email_to" class="form-control" value="{{ old('test_email_to', auth()->user()->email) }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-send me-1"></i>Send Test</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
