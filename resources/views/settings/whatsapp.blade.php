@extends('layouts.app')

@section('title', 'WhatsApp Notifications')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">WhatsApp</li>
                </ol>
            </nav>
            <h1 class="page-title">WhatsApp Notifications</h1>
            <p class="page-subtitle">Send meeting reminders and alerts to your sales team on WhatsApp.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="WhatsApp Gateway" subtitle="Connect any WhatsApp API gateway (token-based)" icon="bi-whatsapp">
                <form method="POST" action="{{ route('settings.whatsapp.update') }}" data-loading>
                    @csrf
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">API Endpoint URL</label>
                            <input type="url" name="api_url" class="form-control" value="{{ $wa['api_url'] ?? '' }}" placeholder="https://api.yourgateway.com/send-message">
                            <div class="form-text">The gateway receives <code>token</code>, <code>phone</code> and <code>message</code> as POST fields.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">API Token / Key</label>
                            <input type="password" name="api_token" class="form-control" value="{{ $wa['api_token'] ?? '' }}" placeholder="Gateway secret token">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default WhatsApp Number</label>
                            <input type="text" name="default_number" class="form-control" value="{{ $wa['default_number'] ?? '' }}" placeholder="+92 300 0000000">
                            <div class="form-text">Fallback number when a user has no WhatsApp set.</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Settings</button>
                        </div>
                    </div>
                </form>
            </x-card>

            <x-card title="Test Connection" subtitle="Send a test message to a phone number" icon="bi-send">
                <form method="POST" action="{{ route('settings.whatsapp.test') }}" class="row g-2 align-items-end" data-loading>
                    @csrf
                    <div class="col-12 col-md-6">
                        <label class="form-label">Test WhatsApp Number</label>
                        <input type="text" name="test_wa_number" class="form-control" placeholder="+92 300 1234567" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-soft-success w-100"><i class="bi bi-whatsapp me-1"></i>Send Test</button>
                    </div>
                </form>
                <div class="form-text mt-2">Reminders go to your users' WhatsApp numbers (or phone) automatically when this gateway is configured.</div>
            </x-card>
        </div>
    </div>
@endsection