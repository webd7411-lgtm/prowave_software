@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">User Profile</h1>
            <p class="page-subtitle">Detailed view of {{ $user->name }}'s account.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('users.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endcan
            @if (! $user->trashed())
                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="{{ $user->status->value === 'active' ? 'inactive' : 'active' }}">
                    @can($user->status->value === 'active' ? 'users.activate' : 'users.activate')
                        <button type="submit" class="btn btn-soft-warning" data-confirm data-confirm-title="{{ $user->status->value === 'active' ? 'Deactivate user?' : 'Activate user?' }}" data-confirm-text="Status will be updated." data-confirm-confirm-text="Yes, update">
                            <i class="bi bi-toggle-{{ $user->status->value === 'active' ? 'off' : 'on' }} me-1"></i>
                            {{ $user->status->value === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    @endcan
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-4">
            <div class="card overflow-hidden">
                <div class="profile-cover"></div>
                <div class="text-center pb-4">
                    <div class="profile-avatar-wrap mb-3">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="profile-avatar" alt="{{ $user->name }}">
                        @else
                            <span class="avatar-fallback xl" style="width: 96px; height: 96px; border: 4px solid var(--pw-surface); box-shadow: var(--pw-shadow); font-size: 1.8rem;">{{ initials($user->name) }}</span>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->designation ?? $user->job_title ?? 'Team Member' }}</p>

                    <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                        @foreach ($user->roles as $role)
                            <span class="badge badge-soft-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>

                    @if ($user->trashed())
                        <span class="badge badge-soft-danger"><i class="bi bi-trash me-1"></i>In Trash</span>
                    @else
                        <span class="badge badge-soft-{{ $user->status->color() }}">{{ $user->status->label() }}</span>
                    @endif

                    <div class="divider mx-4"></div>

                    <div class="text-start px-4">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                            <span class="fw-500 text-truncate ms-2">{{ $user->email }}</span>
                        </div>
                        @if ($user->phone)
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-telephone me-2"></i>Phone</span>
                                <span class="fw-500">{{ $user->phone }}</span>
                            </div>
                        @endif
                        @if ($user->employee_id)
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-person-badge me-2"></i>Employee ID</span>
                                <span class="fw-500">{{ $user->employee_id }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-building me-2"></i>Department</span>
                            <span class="fw-500">{{ $user->department?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Joined</span>
                            <span class="fw-500">{{ format_date($user->created_at) }}</span>
                        </div>
                        @if ($user->last_login_at)
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Last Login</span>
                                <span class="fw-500">{{ diff_for_humans($user->last_login_at) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4 px-3">
                        @can('users.delete')
                            @if ($user->id !== auth()->id() && ! $user->isSuperAdmin())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger" data-confirm data-confirm-title="Delete user?" data-confirm-text="This user will be moved to trash." data-confirm-confirm-text="Yes, delete">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            @endif
                        @endcan
                        @can('users.view', $user)
                            <a href="{{ route('admin.users.login-logs', $user) }}" class="btn btn-outline">
                                <i class="bi bi-shield-check me-1"></i>Login History
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="d-flex flex-column gap-4">
                <x-card title="Account Information" icon="bi-person-vcard">
                    <x-slot name="actions">
                        @can('users.edit')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-soft-primary"><i class="bi bi-pencil"></i> Edit</a>
                        @endcan
                    </x-slot>
                    <div class="row g-3">
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Username</div><div class="fw-500">{{ $user->username ?? '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Status</div><div class="fw-500">{{ $user->status->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Gender</div><div class="fw-500">{{ $user->gender?->label() ?? '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Date of Birth</div><div class="fw-500">{{ $user->dob ? format_date($user->dob) : '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Designation</div><div class="fw-500">{{ $user->designation ?? '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Joining Date</div><div class="fw-500">{{ $user->joining_date ? format_date($user->joining_date) : '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Reporting Manager</div><div class="fw-500">{{ $user->reportingManager?->name ?? '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">CNIC</div><div class="fw-500">{{ $user->cnic ?? '—' }}</div></div>
                    </div>
                    @if ($user->bio)
                        <hr>
                        <div class="fs-xs text-muted text-uppercase mb-1">Bio</div>
                        <p class="mb-0">{{ $user->bio }}</p>
                    @endif
                </x-card>

                <x-card title="Address & Emergency" icon="bi-geo-alt">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="fs-xs text-muted text-uppercase">Address</div>
                            <div class="fw-500">{{ $user->address ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-xs text-muted text-uppercase">City / Province / Country</div>
                            <div class="fw-500">{{ collect([$user->city, $user->province, $user->country])->filter()->implode(', ') ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-xs text-muted text-uppercase">Postal Code</div>
                            <div class="fw-500">{{ $user->postal_code ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-xs text-muted text-uppercase">Emergency Contact</div>
                            <div class="fw-500">{{ $user->emergency_contact ? $user->emergency_contact.($user->emergency_phone ? ' ('.$user->emergency_phone.')' : '') : '—' }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card title="Recent Login History" subtitle="Latest 10 sign-ins" icon="bi-shield-check">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>IP Address</th>
                                    <th>Browser</th>
                                    <th>Device</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loginLogs as $log)
                                    <tr>
                                        <td>{{ format_datetime($log->login_at) }}</td>
                                        <td class="font-monospace">{{ $log->ip_address ?? '—' }}</td>
                                        <td>{{ $log->browser ?? '—' }}</td>
                                        <td>{{ $log->device ?? '—' }} · {{ $log->platform ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-soft-{{ $log->status === 'success' ? 'success' : ($log->status === 'locked' ? 'warning' : 'danger') }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No login records yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('admin.users.login-logs', $user) }}" class="btn btn-sm btn-outline mt-2">View all login history</a>
                </x-card>

                <x-card title="Recent Activity" icon="bi-activity">
                    <div class="list-group list-group-flush">
                        @forelse ($activities as $activity)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <i class="bi bi-dot"></i> {{ $activity->description }}
                                    </div>
                                    <small class="text-muted ms-3 flex-shrink-0">{{ diff_for_humans($activity->created_at) }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No activity recorded yet.</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
