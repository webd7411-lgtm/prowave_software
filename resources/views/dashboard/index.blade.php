@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $greeting = match (true) {
        now()->hour < 12 => 'Good morning',
        now()->hour < 18 => 'Good afternoon',
        default => 'Good evening',
    };
@endphp

@section('content')
    {{-- ============ Welcome header ============ --}}
    <div class="page-header dashboard-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <h1 class="page-title">
                {{ $greeting }}, {{ Str::before(auth()->user()?->name ?? 'there', ' ') }} 👋
            </h1>
            <p class="page-subtitle">
                Here's what's happening across {{ setting('company.name', 'Prowave') }} today —
                {{ \Carbon\Carbon::now()->format('l, F j, Y') }}.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('users.import')
                <a href="{{ route('admin.users.import') }}" class="btn btn-outline">
                    <i class="bi bi-upload me-1"></i>Import
                </a>
            @endcan
            @can('users.export')
                <a href="{{ route('admin.users.export') }}" class="btn btn-soft-success">
                    <i class="bi bi-cloud-download me-1"></i>Export
                </a>
            @endcan
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </a>
            @endcan
        </div>
    </div>

    {{-- ============ KPI cards ============ --}}
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                label="Total Staff"
                icon="bi-people-fill"
                variant="primary"
                :counter="$stats['total_users']"
                :trend="'All employees & members'"
                :trend-up="true"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                label="Active Staff"
                icon="bi-person-check-fill"
                variant="success"
                :counter="$stats['active_users']"
                :trend="'Currently active accounts'"
                :trend-up="true"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                label="New This Month"
                icon="bi-person-plus-fill"
                variant="info"
                :counter="$stats['new_this_month']"
                :trend="($trends['users_delta'] >= 0 ? '+' : '') . $trends['users_delta'] . '% vs last month'"
                :trend-up="$trends['users_delta'] >= 0"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-stat-card
                label="Departments"
                icon="bi-diagram-3-fill"
                variant="secondary"
                :counter="$stats['total_departments']"
                :trend="$stats['total_logins'] . ' total logins'"
                :trend-up="true"
            />
        </div>
    </div>

    {{-- ============ Charts row ============ --}}
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-xl-8">
            <x-card
                title="Staff Growth"
                subtitle="New staff members over the last 6 months"
                icon="bi-graph-up-arrow"
                :actions="'<span class=\'badge badge-soft-primary\'>Live</span>'"
            >
                <div class="chart-wrapper chart-wrapper-lg">
                    <canvas id="staffGrowthChart" data-chart="line"
                        data-chart-labels="{{ json_encode($chartData['labels']) }}"
                        data-chart-datasets="{{ json_encode([
                            [
                                'label' => 'New Staff',
                                'data' => $chartData['values'],
                                'borderColor' => '#2563eb',
                                'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                                'fill' => true,
                                'tension' => 0.4,
                                'borderWidth' => 2.5,
                                'pointRadius' => 4,
                                'pointBackgroundColor' => '#2563eb',
                                'pointBorderColor' => '#fff',
                                'pointBorderWidth' => 2,
                            ],
                        ]) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <x-card title="Staff Distribution" subtitle="Active vs. inactive" icon="bi-pie-chart">
                <div class="chart-wrapper chart-wrapper-sm">
                    <canvas id="staffStatusChart" data-chart="doughnut"
                        data-chart-labels="{{ json_encode(['Active', 'Inactive']) }}"
                        data-chart-datasets="{{ json_encode([[
                            'data' => $chartData['active_vs_inactive'],
                            'backgroundColor' => ['#2563eb', '#e5e9f0'],
                            'borderWidth' => 0,
                            'hoverOffset' => 8,
                        ]]) }}"
                    ></canvas>
                </div>
                <div class="row g-2 mt-3 text-center">
                    <div class="col-6">
                        <div class="bg-primary-soft rounded py-2 px-2">
                            <div class="fw-700 text-primary">{{ $stats['active_users'] }}</div>
                            <div class="fs-xs text-muted">Active</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-secondary rounded py-2 px-2">
                            <div class="fw-700 text-secondary">{{ $stats['inactive_users'] }}</div>
                            <div class="fs-xs text-muted">Inactive</div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ============ Module overview widgets ============ --}}
    <div class="row g-3 g-md-4 mb-4">
        {{-- Sign-ins today --}}
        <div class="col-12 col-md-6 col-xl-3">
            <x-card title="Sign-ins Today" subtitle="Active user sessions" icon="bi-box-arrow-in-right" hover>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-emerald-soft">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="stat-value" data-counter="{{ $stats['logins_today'] }}">0</div>
                        <div class="stat-label">{{ $stats['logins_today'] === 1 ? 'login' : 'logins' }} today</div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Attendance widget (presentational) --}}
        <div class="col-12 col-md-6 col-xl-3">
            <x-card title="Attendance" subtitle="Today's overview" icon="bi-calendar-check" hover>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fs-sm">Present rate</span>
                    <span class="fw-700">{{ $attendance['present_pct'] }}%</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $attendance['present_pct'] }}%;"></div>
                </div>
                <div class="d-flex justify-content-between fs-sm">
                    <span><i class="bi bi-person-check text-success me-1"></i>{{ $attendance['present'] }} Present</span>
                    <span><i class="bi bi-person-x text-secondary me-1"></i>{{ $attendance['absent'] }} Absent</span>
                </div>
            </x-card>
        </div>

        {{-- Open projects (presentational) --}}
        <div class="col-12 col-md-6 col-xl-3">
            <x-card title="Active Projects" subtitle="Module coming soon" icon="bi-kanban" hover>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-indigo-soft">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">in progress</div>
                    </div>
                </div>
                <div class="mt-3">
                    <x-empty-state title="No projects yet" text="The Projects module will appear here." icon="bi-kanban" />
                </div>
            </x-card>
        </div>

        {{-- Pending tasks (presentational) --}}
        <div class="col-12 col-md-6 col-xl-3">
            <x-card title="Pending Tasks" subtitle="Across all teams" icon="bi-list-check" hover>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">awaiting action</div>
                    </div>
                </div>
                <div class="mt-3">
                    <x-empty-state title="No tasks yet" text="Tasks will appear here once the module is live." icon="bi-list-check" />
                </div>
            </x-card>
        </div>
    </div>

    {{-- ============ Revenue / charts (presentational) ============ --}}
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-xl-8">
            <x-card
                title="Revenue Overview"
                subtitle="Income vs. expenses (Module coming soon)"
                icon="bi-graph-up"
                :actions="'<span class=\'badge badge-soft-secondary\'>Preview</span>'"
            >
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-3">
                    <div class="d-flex gap-4">
                        <div>
                            <div class="fs-xs text-muted text-uppercase">Revenue</div>
                            <div class="fw-700 h5 mb-0 text-success">$0</div>
                        </div>
                        <div>
                            <div class="fs-xs text-muted text-uppercase">Expenses</div>
                            <div class="fw-700 h5 mb-0 text-danger">$0</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-soft-info">Sales Executive</span>
                        <span class="badge badge-soft-info">Accountant</span>
                    </div>
                </div>
                <div class="chart-wrapper chart-wrapper-md bg-soft rounded p-2 d-flex align-items-center justify-content-center">
                    <x-empty-state title="Revenue data coming soon" text="Connect the Finance module to see income vs. expenses." icon="bi-cash-stack" />
                </div>
            </x-card>
        </div>

        {{-- Recent logins --}}
        <div class="col-12 col-xl-4">
            <x-card title="Recent Sign-ins" subtitle="Latest successful logins" icon="bi-shield-check" body-class="p-0">
                <div class="list-group list-group-flush">
                    @forelse ($recentLogins as $log)
                        <div class="list-group-item px-3 py-3 d-flex align-items-center gap-3">
                            <span class="avatar-fallback sm">{{ $log->user ? initials($log->user->name) : '?' }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-500 text-truncate">{{ $log->user?->name ?? $log->email }}</div>
                                <div class="fs-xs text-muted">{{ $log->browser ?? 'Unknown' }} · {{ $log->ip_address }}</div>
                            </div>
                            <span class="fs-xs text-muted text-nowrap">{{ \Carbon\Carbon::parse($log->login_at)->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="py-4">
                            <x-empty-state title="No sign-ins yet" text="Login activity will appear here." icon="bi-shield-check" />
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- ============ Recent activity + users ============ --}}
    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-5">
            <x-card title="Recent Activity" subtitle="Latest system events" icon="bi-activity">
                @if ($activities->count())
                    <div class="activity-timeline">
                        @foreach ($activities as $activity)
                            <div class="activity-item {{ in_array($activity->event, ['created']) ? 'success' : ($activity->event === 'deleted' ? 'danger' : '') }}">
                                <div class="activity-title">{{ $activity->description }}</div>
                                <div class="activity-meta">
                                    {{ $activity->causer?->name ?? 'System' }} · {{ diff_for_humans($activity->created_at) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state title="No activity yet" text="System events and user actions will appear here." icon="bi-activity" />
                @endif
            </x-card>
        </div>

        <div class="col-12 col-xl-7">
            <x-card
                title="Recent Staff"
                subtitle="Latest people added to the system"
                icon="bi-people"
                :actions="auth()->user()->can('users.view') ? '<a href=\''.route('admin.users.index').'\' class=\'btn btn-soft-primary btn-sm\'>View All</a>' : ''"
            >
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($user->avatar_url)
                                                <img src="{{ $user->avatar_url }}" class="avatar-sm rounded-circle" alt="">
                                            @else
                                                <span class="avatar-fallback sm">{{ initials($user->name) }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="fw-500 text-body text-truncate">{{ $user->name }}</div>
                                                <div class="text-muted fs-xs text-truncate">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">{{ $user->department?->name ?? ($user->department_label ?? '—') }}</td>
                                    <td>
                                        <span class="badge badge-soft-secondary">{{ $user->roles->first()?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if ($user->is_active)
                                            <span class="badge badge-soft-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.4rem;"></i>Active</span>
                                        @else
                                            <span class="badge badge-soft-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state title="No staff yet" text="Users will appear here once they are added." icon="bi-people" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
