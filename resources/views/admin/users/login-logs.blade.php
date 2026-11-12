@extends('layouts.app')

@section('title', 'Login History — '.$user->name)

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Login History</li>
                </ol>
            </nav>
            <h1 class="page-title">Login History</h1>
            <p class="page-subtitle">All sign-in attempts for {{ $user->name }}.</p>
        </div>
    </div>

    <x-card title="Sign-in Records" subtitle="IP, browser, device and status for each attempt" icon="bi-shield-check">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>Platform</th>
                        <th>Device</th>
                        <th>Remember</th>
                        <th>Status</th>
                        <th>Logout</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td data-label="Date & Time">{{ format_datetime($log->login_at) }}</td>
                            <td data-label="IP Address" class="font-monospace">{{ $log->ip_address ?? '—' }}</td>
                            <td data-label="Browser">{{ $log->browser ?? '—' }}</td>
                            <td data-label="Platform">{{ $log->platform ?? '—' }}</td>
                            <td data-label="Device">{{ $log->device ?? '—' }}</td>
                            <td data-label="Remember">{{ $log->remember ? 'Yes' : 'No' }}</td>
                            <td data-label="Status">
                                <span class="badge badge-soft-{{ $log->status === 'success' ? 'success' : ($log->status === 'locked' ? 'warning' : ($log->status === 'logged_out' ? 'secondary' : 'danger')) }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td data-label="Logout">{{ $log->logout_at ? format_datetime($log->logout_at) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No login records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </x-card>
@endsection
