@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
                </ol>
            </nav>
            <h1 class="page-title">Audit Logs</h1>
            <p class="page-subtitle">Every significant action, recorded for accountability.</p>
        </div>
        <div>
            <a href="{{ route('audit.export', request()->query()) }}" class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('audit.index') }}" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Module</label>
                <select name="log_name" class="form-select">
                    <option value="">All modules</option>
                    @foreach ($logNames as $name)
                        <option value="{{ $name }}" {{ ($filters['log_name'] ?? '') === $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Event</label>
                <select name="event" class="form-select">
                    <option value="">All events</option>
                    @foreach (['created', 'updated', 'deleted', 'restored', 'approved', 'rejected', 'login'] as $event)
                        <option value="{{ $event }}" {{ ($filters['event'] ?? '') === $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search action descriptions..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('audit.index') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <x-card>
        @if ($logs->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Event</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ format_datetime($log->created_at->toDateTimeString()) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ user_avatar($log->causer) }}" alt="" class="avatar avatar-xs">
                                    <span class="fw-medium">{{ $log->causer?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td>{{ $log->description }}</td>
                            <td><span class="badge bg-soft-dark-subtle text-dark">{{ $log->log_name }}</span></td>
                            <td>
                                @php
                                    $tone = match ($log->event) {
                                        'created' => 'bg-success-subtle text-success-emphasis',
                                        'updated' => 'bg-info-subtle text-info-emphasis',
                                        'deleted' => 'bg-danger-subtle text-danger-emphasis',
                                        default => 'bg-secondary-subtle text-secondary-emphasis',
                                    };
                                @endphp
                                <span class="badge {{ $tone }}">{{ ucfirst($log->event ?? 'unknown') }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $logs->links() }}</div>
        @else
            <x-empty-state icon="bi-journal-check" title="No audit entries" subtitle="Filters may be too narrow, or no activity has been recorded yet." />
        @endif
    </x-card>
@endsection
