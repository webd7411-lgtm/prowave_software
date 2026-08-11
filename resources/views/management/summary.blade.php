@extends('layouts.app')

@section('title', 'Management Summary')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Management</li>
                </ol>
            </nav>
            <h1 class="page-title">Management Summary</h1>
            <p class="page-subtitle">Executive snapshot of the business right now.</p>
        </div>
    </div>

    @if ($alerts->count())
        <div class="mb-3">
            @foreach ($alerts as $alert)
                <div class="alert alert-soft-{{ $alert['type'] }} border-0 d-flex align-items-center gap-2 mb-2">
                    <i class="bi {{ $alert['icon'] }}"></i>
                    <span>{{ $alert['text'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                    <div class="stat-label">Employees</div>
                    <div class="stat-value">{{ $totalEmployees }}</div>
                    <div class="stat-foot">{{ $attendanceToday }} present today</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-building"></i></div>
                    <div class="stat-label">Clients</div>
                    <div class="stat-value">{{ $totalClients }}</div>
                    <div class="stat-foot">{{ $newLeads }} new leads this month</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-kanban"></i></div>
                    <div class="stat-label">Active Projects</div>
                    <div class="stat-value">{{ $activeProjects }}</div>
                    <div class="stat-foot">{{ $completedProjects }} completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-check2-circle"></i></div>
                    <div class="stat-label">Lead Conversion</div>
                    <div class="stat-value">{{ $leadConversion }}%</div>
                    <div class="stat-foot">{{ $overdueTasks }} overdue tasks</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-value text-success">{{ setting('general.currency', 'USD') }} {{ format_money($monthlyRevenue) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Monthly Expenses</div>
                    <div class="stat-value text-danger">{{ setting('general.currency', 'USD') }} {{ format_money($monthlyExpenses) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-label">Net Profit</div>
                    <div class="stat-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ setting('general.currency', 'USD') }} {{ format_money($netProfit) }}</div>
                    <div class="stat-foot">Outstanding receivables: {{ setting('general.currency', 'USD') }} {{ format_money($outstanding) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <x-card title="Revenue vs Expenses" subtitle="Last 6 months" icon="bi-graph-up">
                <div class="report-bar-chart d-flex align-items-end gap-2" style="height: 240px;">
                    @php
                        $months = collect();
                        for ($i = 5; $i >= 0; $i--) {
                            $months->push(now()->subMonths($i)->format('Y-m'));
                        }
                        $maxVal = max($months->map(fn ($m) => max($revenueTrend[$m] ?? 0, $expenseTrend[$m] ?? 0, 1))->max(), 1);
                    @endphp
                    @foreach ($months as $month)
                        @php
                            $rev = $revenueTrend[$month] ?? 0;
                            $exp = $expenseTrend[$month] ?? 0;
                        @endphp
                        <div class="flex-grow-1 text-center">
                            <div class="d-flex align-items-end justify-content-center gap-1" style="height: 200px;">
                                <div class="report-bar report-bar-revenue rounded-top" style="height: {{ max(($rev / $maxVal) * 100, 2) }}%;" title="Revenue: {{ format_money($rev) }}"></div>
                                <div class="report-bar report-bar-expense rounded-top" style="height: {{ max(($exp / $maxVal) * 100, 2) }}%;" title="Expenses: {{ format_money($exp) }}"></div>
                            </div>
                            <div class="mt-1 fs-xs fw-medium">{{ \Illuminate\Support\Carbon::parse($month . '-01')->format('M y') }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex gap-3 mt-3 fs-xs text-muted">
                    <span><span class="legend-dot bg-success"></span> Revenue</span>
                    <span><span class="legend-dot bg-danger"></span> Expenses</span>
                </div>
            </x-card>

            <x-card title="Upcoming Deadlines" subtitle="Next 14 days" icon="bi-calendar-event" class="mt-3">
                @if ($upcomingDeadlines->count())
                    <div class="list-group list-group-flush">
                        @foreach ($upcomingDeadlines as $item)
                            <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action px-0 d-flex align-items-center gap-3">
                                <div class="deadline-date text-center flex-shrink-0">
                                    <div class="fs-sm fw-bold">{{ $item['date']->format('d') }}</div>
                                    <div class="fs-xs text-muted">{{ $item['date']->format('M') }}</div>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-medium text-truncate">{{ $item['title'] }}</div>
                                    <div class="fs-xs text-muted">
                                        {{ $item['type'] }}
                                        @if (! empty($item['project'])) · {{ $item['project'] }} @endif
                                        @if (! empty($item['assignee'])) · {{ $item['assignee'] }} @endif
                                    </div>
                                </div>
                                <span class="badge {{ $item['type'] === 'Project' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-info-subtle text-info-emphasis' }}">{{ $item['type'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="bi-calendar-check" title="No deadlines in the next 14 days" />
                @endif
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card title="Project Status" subtitle="All projects" icon="bi-pie-chart">
                @if ($projectStatus->count())
                    @php $total = max($projectStatus->sum(), 1); @endphp
                    @foreach ($projectStatus as $label => $count)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between fs-sm mb-1">
                                <span>{{ $label }}</span>
                                <span class="fw-semibold">{{ $count }} ({{ round(($count / $total) * 100) }}%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $label === 'Completed' ? 'bg-success' : ($label === 'Cancelled' ? 'bg-danger' : 'bg-primary') }}" style="width: {{ ($count / $total) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <x-empty-state icon="bi-kanban" title="No projects yet" />
                @endif
            </x-card>

            <x-card title="Quick Actions" icon="bi-lightning" class="mt-3">
                <div class="d-grid gap-2">
                    <a href="{{ route('reports.index') }}" class="btn btn-soft-primary"><i class="bi bi-graph-up me-1"></i>Open Reports</a>
                    <a href="{{ route('calendar.index') }}" class="btn btn-soft-info"><i class="bi bi-calendar3 me-1"></i>View Calendar</a>
                    <a href="{{ route('crm.leads.index') }}" class="btn btn-soft-success"><i class="bi bi-person-plus me-1"></i>Add Lead</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-soft-secondary"><i class="bi bi-people me-1"></i>Manage Team</a>
                </div>
            </x-card>
        </div>
    </div>
@endsection
