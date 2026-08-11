@extends('layouts.app')

@section('title', 'Active Sessions')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sessions</li>
                </ol>
            </nav>
            <h1 class="page-title">Active Sessions</h1>
            <p class="page-subtitle">Devices where your account is currently signed in.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if (! $sessions->count())
                <x-card title="Active Sessions" icon="bi-devices">
                    <div class="alert alert-soft-info border-0 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Session management requires the <code>database</code> session driver. Enable it in <code>.env</code> (<code>SESSION_DRIVER=database</code>) and run <code>php artisan session:table</code>.
                    </div>
                </x-card>
            @else
                <x-card title="Active Sessions" subtitle="Sign out any device you don't recognize" icon="bi-devices">
                    <div class="list-group list-group-flush mb-3">
                        @foreach ($sessions as $session)
                            <div class="list-group-item px-0 d-flex align-items-center gap-3">
                                <div class="session-icon {{ $session['is_current'] ? 'bg-primary-subtle text-primary' : 'bg-soft-dark-subtle text-dark' }}">
                                    <i class="bi {{ $session['is_current'] ? 'bi-laptop' : 'bi-laptop-outline' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">
                                        {{ $session['browser'] }}
                                        @if ($session['is_current']) <span class="badge bg-success-subtle text-success-emphasis ms-1">Current device</span> @endif
                                    </div>
                                    <div class="fs-xs text-muted">
                                        {{ $session['ip'] }} · Last active {{ $session['last_activity']->diffForHumans() }}
                                    </div>
                                </div>
                                @if (! $session['is_current'])
                                    <form action="{{ route('profile.sessions.revoke', $session['id']) }}" method="POST" data-confirm="Sign out this device?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Sign Out</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <form action="{{ route('profile.sessions.revoke-others') }}" method="POST" data-confirm="Sign out all other devices?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-soft-danger"><i class="bi bi-shield-exclamation me-1"></i>Sign Out All Other Devices</button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
@endsection
