@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="auth-brand">
        <img src="{{ asset('images/logo.jpg') }}" alt="{{ setting('company.name', 'Prowave') }}" style="height: 36px;">
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Reset your password</h2>
    <p class="text-muted mb-4">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    @if (session('status'))
        <x-alert type="success">
            {{ session('status') }}
        </x-alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-loading>
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

        <button type="submit" class="btn btn-primary w-100" data-loading-text="Sending link...">
            <i class="bi bi-envelope-arrow-up me-1"></i>
            Send Reset Link
        </button>
    </form>

    <p class="text-center text-muted fs-sm mt-4 mb-0">
        <a href="{{ route('login') }}" class="fw-600">
            <i class="bi bi-arrow-left me-1"></i>Back to sign in
        </a>
    </p>
@endsection
