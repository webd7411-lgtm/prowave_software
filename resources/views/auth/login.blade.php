@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="auth-brand">
        <span class="logo-fallback"><i class="bi bi-water"></i></span>
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Welcome back</h2>
    <p class="text-muted mb-4">Sign in to your account to continue.</p>

    <form method="POST" action="{{ route('login') }}" data-loading autocomplete="off">
        @csrf

        <x-form.input
            label="Email Address"
            name="email"
            type="email"
            icon="bi-envelope"
            placeholder="you@company.com"
            value="{{ old('email') }}"
            :required="true"
            :autofocus="true"
        />

        <div class="position-relative">
            <x-form.input
                label="Password"
                name="password"
                type="password"
                icon="bi-shield-lock"
                placeholder="Enter your password"
                :required="true"
            />
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="fs-sm fw-500">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2.5" data-loading-text="Signing in...">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Sign In
        </button>
    </form>

    @if (setting('general.allow_registration', false))
        <p class="text-center text-muted fs-sm mt-4 mb-0">
            Don't have an account?
            <a href="{{ route('register') }}" class="fw-600">Create one</a>
        </p>
    @endif
@endsection
