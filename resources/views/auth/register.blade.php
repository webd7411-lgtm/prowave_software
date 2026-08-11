@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <div class="auth-brand">
        <img src="{{ asset('images/logo.jpg') }}" alt="{{ setting('company.name', 'Prowave') }}" style="height: 36px;">
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Create your account</h2>
    <p class="text-muted mb-4">Join {{ setting('company.name', 'Prowave') }} in a few seconds.</p>

    <form method="POST" action="{{ route('register') }}" data-loading autocomplete="off">
        @csrf

        <x-form.input
            label="Full Name"
            name="name"
            icon="bi-person"
            placeholder="John Doe"
            value="{{ old('name') }}"
            :required="true"
            :autofocus="true"
        />

        <x-form.input
            label="Email Address"
            name="email"
            type="email"
            icon="bi-envelope"
            placeholder="you@company.com"
            value="{{ old('email') }}"
            :required="true"
        />

        <x-form.input
            label="Password"
            name="password"
            type="password"
            icon="bi-shield-lock"
            placeholder="Minimum 8 characters"
            :required="true"
        />

        <x-form.input
            label="Confirm Password"
            name="password_confirmation"
            type="password"
            icon="bi-shield-check"
            placeholder="Repeat your password"
            :required="true"
        />

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="terms" value="1" id="terms" @checked(old('terms'))>
            <label class="form-check-label fs-sm" for="terms">
                I agree to the <a href="javascript:void(0)" class="fw-600">Terms of Service</a> and
                <a href="javascript:void(0)" class="fw-600">Privacy Policy</a>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100" data-loading-text="Creating account...">
            <i class="bi bi-person-plus me-1"></i>
            Create Account
        </button>
    </form>

    <p class="text-center text-muted fs-sm mt-4 mb-0">
        Already have an account?
        <a href="{{ route('login') }}" class="fw-600">Sign in</a>
    </p>
@endsection
