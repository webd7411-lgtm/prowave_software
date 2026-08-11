@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="auth-brand">
        <img src="{{ asset('images/logo.jpg') }}" alt="{{ setting('company.name', 'Prowave') }}" style="height: 36px;">
        <div>
            <div class="brand-name">{{ setting('company.name', 'Prowave') }}</div>
            <div class="text-muted fs-xs">Management System</div>
        </div>
    </div>

    <h2 class="h3 fw-700 mb-1">Verify your email</h2>
    <p class="text-muted mb-4">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent you.
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success">
            A new verification link has been sent to your email address.
        </x-alert>
    @endif

    <div class="d-flex flex-column gap-2">
        <form method="POST" action="{{ route('verification.send') }}" data-loading>
            @csrf
            <button type="submit" class="btn btn-primary w-100" data-loading-text="Resending...">
                <i class="bi bi-envelope-arrow-up me-1"></i>
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline w-100">
                <i class="bi bi-box-arrow-right me-1"></i>
                Sign Out
            </button>
        </form>
    </div>
@endsection
