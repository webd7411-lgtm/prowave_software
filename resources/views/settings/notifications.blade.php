@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
            <h1 class="page-title">Notification Settings</h1>
            <p class="page-subtitle">Choose which events should generate notifications in the notification center.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="Notification Categories" subtitle="Toggle which system events create in-app notifications" icon="bi-bell">
                <form method="POST" action="{{ route('settings.notifications.update') }}" data-loading>
                    @csrf
                    <div class="row g-3">
                        @foreach ($categories as $key => $label)
                            @php
                                $enabled = $defaults[$key] ?? true;
                            @endphp
                            <div class="col-md-6">
                                <div class="form-check form-switch settings-toggle d-flex align-items-center justify-content-between border rounded p-3">
                                    <div>
                                        <label class="form-check-label fw-medium" for="notify_{{ $key }}">{{ $label }}</label>
                                        <div class="fs-xs text-muted">Generate notifications for {{ strtolower($label) }} events.</div>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" role="switch"
                                           id="notify_{{ $key }}" name="notify_{{ $key }}" value="1" {{ $enabled ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12">
                            <div class="alert alert-soft-info border-0 mb-0 fs-sm d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle flex-shrink-0 mt-1"></i>
                                <div>Individual users can override these defaults from their own profile under <strong>My Preferences</strong>.</div>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Notification Preferences</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
