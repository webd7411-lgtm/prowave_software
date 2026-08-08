@extends('layouts.app')

@section('title', 'My Profile')

@php
    $tab = request('tab', 'overview');
@endphp

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your information, security, and preferences.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline">
            <i class="bi bi-pencil me-1"></i>Account Settings
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-4 p-4">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" class="profile-avatar-lg" alt="{{ $user->name }}">
            @else
                <span class="avatar-fallback xl" style="width: 84px; height: 84px; font-size: 1.6rem;">{{ initials($user->name) }}</span>
            @endif
            <div class="flex-grow-1 min-w-0">
                <h3 class="mb-1">{{ $user->name }}</h3>
                <p class="text-muted mb-2">{{ $user->designation ?? $user->job_title ?? 'Team Member' }} @if ($user->department?->name) · {{ $user->department->name }} @endif</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @foreach ($user->roles as $role)
                        <span class="badge badge-soft-primary">{{ $role->name }}</span>
                    @endforeach
                    <span class="badge badge-soft-{{ $user->status->color() }}">{{ $user->status->label() }}</span>
                </div>
            </div>
            <div class="d-flex gap-4 text-center">
                <div>
                    <div class="stat-value">{{ $user->loginLogs()->where('status', 'success')->count() }}</div>
                    <div class="stat-label">Logins</div>
                </div>
                <div>
                    <div class="stat-value">{{ $user->activities()->count() }}</div>
                    <div class="stat-label">Activities</div>
                </div>
                <div>
                    <div class="stat-value">{{ $user->roles->flatMap(fn ($r) => $r->permissions)->unique('id')->count() }}</div>
                    <div class="stat-label">Permissions</div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs profile-tabs mb-4" role="tablist">
        <li class="nav-item"><a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ route('profile.show') }}"><i class="bi bi-person me-1"></i>Overview</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'personal' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'personal']) }}"><i class="bi bi-person-vcard me-1"></i>Personal</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'work' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'work']) }}"><i class="bi bi-briefcase me-1"></i>Work</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'security' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'security']) }}"><i class="bi bi-shield-lock me-1"></i>Security</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'activity' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'activity']) }}"><i class="bi bi-activity me-1"></i>Activity Logs</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'login-history' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'login-history']) }}"><i class="bi bi-clock-history me-1"></i>Login History</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'permissions' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'permissions']) }}"><i class="bi bi-shield-check me-1"></i>Permissions</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'settings' ? 'active' : '' }}" href="{{ route('profile.show', ['tab' => 'settings']) }}"><i class="bi bi-gear me-1"></i>Settings</a></li>
    </ul>

    @if ($tab === 'overview')
        <div class="row g-3 g-md-4">
            <div class="col-12 col-lg-8">
                <x-card title="About" subtitle="A few words about you" icon="bi-person-vcard">
                    @if ($user->bio)
                        <p class="mb-0">{{ $user->bio }}</p>
                    @else
                        <p class="text-muted mb-0">You haven't added a bio yet.</p>
                    @endif
                </x-card>

                <div class="mt-4">
                    <x-card title="Quick Info" icon="bi-list-check">
                        <div class="row g-3">
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Email</div><div class="fw-500 text-break">{{ $user->email }}</div></div>
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Phone</div><div class="fw-500">{{ $user->phone ?? '—' }}</div></div>
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Employee ID</div><div class="fw-500">{{ $user->employee_id ?? '—' }}</div></div>
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Department</div><div class="fw-500">{{ $user->department?->name ?? '—' }}</div></div>
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Manager</div><div class="fw-500">{{ $user->reportingManager?->name ?? '—' }}</div></div>
                            <div class="col-6 col-md-4"><div class="fs-xs text-muted text-uppercase">Member Since</div><div class="fw-500">{{ format_date($user->created_at) }}</div></div>
                        </div>
                    </x-card>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <x-card title="Account" icon="bi-shield-lock">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Last Login</span>
                        <span class="fw-500">{{ $user->last_login_at ? diff_for_humans($user->last_login_at) : 'Never' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="bi bi-display me-2"></i>Last IP</span>
                        <span class="fw-500 font-monospace">{{ $user->last_login_ip ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="bi bi-palette me-2"></i>Theme</span>
                        <span class="fw-500">{{ ucfirst($user->theme ?? 'system') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="bi bi-globe me-2"></i>Timezone</span>
                        <span class="fw-500">{{ $user->timezone ?? config('app.timezone') }}</span>
                    </div>
                </x-card>
            </div>
        </div>
    @elseif ($tab === 'personal')
        <x-card title="Personal Information" subtitle="Update your contact and personal details" icon="bi-person-vcard" body-class="p-4">
            <x-form :action="route('profile.update')" method="PATCH" cancel-url="{{ route('profile.show', ['tab' => 'personal']) }}">
                <input type="hidden" name="tab" value="personal">
                <div class="row g-3">
                    <div class="col-md-6"><x-form.input label="Full Name" name="name" :value="$user->name" :required="true" /></div>
                    <div class="col-md-6"><x-form.input label="Email Address" name="email" type="email" :value="$user->email" :required="true" /></div>
                    <div class="col-md-6"><x-form.input label="Phone Number" name="phone" :value="$user->phone" placeholder="+1 555 000 0000" /></div>
                    <div class="col-md-6"><x-form.select label="Gender" name="gender" :options="\App\Enums\Gender::options()" :selected="$user->gender?->value" placeholder="Select gender" /></div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" class="form-control" name="dob" data-flatpickr data-flatpickr-max="today" value="{{ $user->dob?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6"><x-form.input label="CNIC / National ID" name="cnic" :value="$user->cnic" /></div>
                    <div class="col-12"><x-form.input label="Bio" name="bio" type="textarea" :value="$user->bio" rows="4" placeholder="Tell us a little about yourself..." /></div>
                </div>
            </x-form>
        </x-card>
    @elseif ($tab === 'work')
        <x-card title="Work Information" subtitle="Update your employment details" icon="bi-briefcase" body-class="p-4">
            <x-form :action="route('profile.update')" method="PATCH" cancel-url="{{ route('profile.show', ['tab' => 'work']) }}">
                <input type="hidden" name="tab" value="work">
                <div class="row g-3">
                    <div class="col-md-6"><x-form.input label="Designation" name="designation" :value="$user->designation ?? $user->job_title" placeholder="e.g. Senior Software Engineer" /></div>
                    <div class="col-md-6"><x-form.input label="Department" name="department_label" :value="$user->department_label" placeholder="e.g. Engineering" /></div>
                    <div class="col-12"><x-form.input label="Address" name="address" :value="$user->address" /></div>
                    <div class="col-md-4"><x-form.input label="City" name="city" :value="$user->city" /></div>
                    <div class="col-md-4"><x-form.input label="Province / State" name="province" :value="$user->province" /></div>
                    <div class="col-md-4"><x-form.input label="Country" name="country" :value="$user->country" /></div>
                    <div class="col-md-4"><x-form.input label="Postal Code" name="postal_code" :value="$user->postal_code" /></div>
                    <div class="col-md-4"><x-form.input label="Emergency Contact" name="emergency_contact" :value="$user->emergency_contact" /></div>
                    <div class="col-md-4"><x-form.input label="Emergency Phone" name="emergency_phone" :value="$user->emergency_phone" /></div>
                </div>
            </x-form>
        </x-card>
    @elseif ($tab === 'security')
        <div class="row g-3 g-md-4">
            <div class="col-12 col-lg-6">
                <x-card title="Change Password" subtitle="Use a strong, unique password" icon="bi-shield-lock">
                    <x-form :action="route('profile.password')" method="PATCH" cancel-url="{{ route('profile.show', ['tab' => 'security']) }}">
                        <input type="hidden" name="tab" value="security">
                        <x-form.input label="Current Password" name="current_password" type="password" :required="true" />
                        <x-form.input label="New Password" name="password" type="password" :required="true" help="Minimum 8 characters with at least one letter and one number." />
                        <x-form.input label="Confirm New Password" name="password_confirmation" type="password" :required="true" />
                    </x-form>
                </x-card>
            </div>
            <div class="col-12 col-lg-6">
                <x-card title="Active Sessions" subtitle="Devices currently signed in" icon="bi-devices">
                    <div class="list-group list-group-flush">
                        @forelse ($user->loginLogs()->where('status', 'success')->whereNull('logout_at')->latest()->limit(6)->get() as $session)
                            <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-fallback sm"><i class="bi {{ $session->device === 'Mobile' ? 'bi-phone' : 'bi-laptop' }}"></i></span>
                                    <div>
                                        <div class="fw-500">{{ $session->browser ?? 'Unknown' }} on {{ $session->platform ?? 'Unknown' }}</div>
                                        <div class="fs-xs text-muted">{{ $session->ip_address }} · {{ diff_for_humans($session->login_at) }}</div>
                                    </div>
                                </div>
                                <span class="badge badge-soft-success">Active</span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No active sessions detected.</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    @elseif ($tab === 'activity')
        <x-card title="Activity Logs" subtitle="Your recent actions in the system" icon="bi-activity">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Description</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>
                                    @if ($activity->event)
                                        <span class="badge badge-soft-{{ $activity->event === 'created' ? 'success' : ($activity->event === 'deleted' ? 'danger' : 'primary') }}">{{ ucfirst($activity->event) }}</span>
                                    @else
                                        <span class="badge badge-soft-secondary">Action</span>
                                    @endif
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ format_datetime($activity->created_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $activities->links() }}</div>
        </x-card>
    @elseif ($tab === 'login-history')
        <x-card title="Login History" subtitle="All of your sign-in attempts" icon="bi-clock-history">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>Device</th>
                            <th>Status</th>
                            <th>Logout</th>
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
                                <td>{{ $log->logout_at ? format_datetime($log->logout_at) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No login records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $loginLogs->links() }}</div>
        </x-card>
    @elseif ($tab === 'permissions')
        <x-card title="My Permissions" subtitle="Permissions granted through your roles" icon="bi-shield-check">
            @php
                $grouped = $user->roles
                    ->flatMap(fn ($role) => $role->permissions)
                    ->unique('name')
                    ->groupBy(fn ($permission) => ucfirst(\Illuminate\Support\Str::before($permission->name, '.')));
            @endphp

            <div class="row g-3">
                @forelse ($grouped as $group => $permissions)
                    <div class="col-12 col-lg-6 col-xxl-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-grid text-primary me-1"></i>{{ $group }}</h5></div>
                            <div class="card-body py-2">
                                @foreach ($permissions as $permission)
                                    <div class="d-flex align-items-center gap-2 py-1">
                                        <i class="bi bi-check2-circle text-success"></i>
                                        <span class="font-monospace fs-sm">{{ $permission->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <x-empty-state title="No permissions assigned" text="Contact an administrator if you need access." icon="bi-shield-exclamation" />
                    </div>
                @endforelse
            </div>
        </x-card>
    @elseif ($tab === 'settings')
        <div class="row g-3 g-md-4">
            <div class="col-12 col-lg-7">
                <x-card title="Preferences" subtitle="Personalize how the system works for you" icon="bi-sliders" body-class="p-4">
                    <x-form :action="route('profile.preferences')" method="PATCH" cancel-url="{{ route('profile.show', ['tab' => 'settings']) }}">
                        <input type="hidden" name="tab" value="settings">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.select label="Theme" name="theme" :options="\App\Enums\Theme::options()" :selected="$user->theme" :required="true" />
                            </div>
                            <div class="col-md-6">
                                <x-form.select label="Language" name="language" :options="['en' => 'English']" :selected="$user->language" />
                            </div>
                            <div class="col-md-6">
                                <x-form.select label="Timezone" name="timezone" :options="\DateTimeZone::listIdentifiers(\DateTimeZone::ALL)" :selected="$user->timezone ?? config('app.timezone')" />
                            </div>
                            <div class="col-md-6">
                                <x-form.select label="Date Format" name="date_format" :options="['d M, Y' => now()->format('d M, Y'), 'M d, Y' => now()->format('M d, Y'), 'd/m/Y' => now()->format('d/m/Y'), 'Y-m-d' => now()->format('Y-m-d')]" :selected="$user->date_format" />
                            </div>
                        </div>
                    </x-form>
                </x-card>
            </div>
            <div class="col-12 col-lg-5">
                <x-card title="Profile Photo" subtitle="Upload a new profile picture" icon="bi-camera">
                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" data-loading>
                        @csrf
                        <input type="hidden" name="tab" value="settings">
                        <div class="d-flex align-items-center gap-3">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" class="avatar-lg rounded-circle" alt="">
                            @else
                                <span class="avatar-fallback lg">{{ initials($user->name) }}</span>
                            @endif
                            <div class="flex-grow-1">
                                <input type="file" class="form-control" name="avatar" accept="image/jpeg,image/png,image/webp" @error('avatar') is-invalid @enderror>
                                @error('avatar')
                                    <div class="invalid-feedback d-flex"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                                <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
                            </div>
                            <button type="submit" class="btn btn-soft-primary" data-loading-text="Uploading...">
                                <i class="bi bi-upload me-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </x-card>
            </div>
        </div>
    @endif
@endsection
