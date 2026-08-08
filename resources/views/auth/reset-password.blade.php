@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="auth-brand">
        <span class="logo-fallback"><i class="bi bi-water"></i></span>
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Set a new password</h2>
    <p class="text-muted mb-4">Choose a strong password you haven't used before.</p>

    <form method="POST" action="{{ route('password.store') }}" data-loading>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.input
            label="Email Address"
            name="email"
            type="email"
            icon="bi-envelope"
            placeholder="you@company.com"
            value="{{ $request->email ?? old('email') }}"
            :required="true"
        />

        <x-form.input
            label="New Password"
            name="password"
            type="password"
            icon="bi-shield-lock"
            placeholder="Minimum 8 characters"
            :required="true"
        />

        <x-form.input
            label="Confirm New Password"
            name="password_confirmation"
            type="password"
            icon="bi-shield-check"
            placeholder="Repeat your password"
            :required="true"
        />

        <button type="submit" class="btn btn-primary w-100" data-loading-text="Resetting password...">
            <i class="bi bi-shield-check me-1"></i>
            Reset Password
        </button>
    </form>

    <p class="text-center text-muted fs-sm mt-4 mb-0">
        <a href="{{ route('login') }}" class="fw-600">
            <i class="bi bi-arrow-left me-1"></i>Back to sign in
        </a>
    </p>
@endsection
