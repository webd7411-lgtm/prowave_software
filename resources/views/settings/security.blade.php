@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>
            <h1 class="page-title">Security Settings</h1>
            <p class="page-subtitle">Configure authentication and session security policies.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            <nav class="nav flex-column settings-nav gap-1">
                <a class="nav-link" href="{{ route('settings.general') }}">
                    <i class="bi bi-sliders"></i> General
                </a>
                <a class="nav-link active" href="{{ route('settings.security') }}">
                    <i class="bi bi-shield-lock"></i> Security
                </a>
            </nav>
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="Session & Login Security" subtitle="Protect your users and data" icon="bi-shield-check">
                <x-form :action="route('settings.security.update')" method="POST" cancel-url="{{ route('settings.security') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-form.input
                                label="Session Lifetime (minutes)"
                                name="session_lifetime"
                                type="number"
                                min="15"
                                max="1440"
                                :value="setting('security.session_lifetime', 120)"
                                :required="true"
                                help="How long an idle session remains valid before requiring sign-in."
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Login Lockout Threshold"
                                name="lockout_threshold"
                                type="number"
                                min="1"
                                max="20"
                                :value="setting('security.lockout_threshold', 5)"
                                :required="true"
                                help="Maximum failed login attempts before an account is temporarily locked."
                            />
                        </div>
                    </div>
                </x-form>
            </x-card>
        </div>
    </div>
@endsection
