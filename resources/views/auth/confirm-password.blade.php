@extends('layouts.auth')

@section('title', 'Confirm Password')

@section('content')
    <div class="auth-brand">
        <span class="logo-fallback"><i class="bi bi-water"></i></span>
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Confirm your password</h2>
    <p class="text-muted mb-4">
        This is a secure area of the application. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" data-loading>
        @csrf

        <x-form.input
            label="Password"
            name="password"
            type="password"
            icon="bi-shield-lock"
            placeholder="Enter your password"
            :required="true"
            :autofocus="true"
        />

        <button type="submit" class="btn btn-primary w-100" data-loading-text="Confirming...">
            <i class="bi bi-shield-check me-1"></i>
            Confirm
        </button>
    </form>
@endsection
