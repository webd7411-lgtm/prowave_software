@extends('layouts.app')

@section('title', 'Project Analytics & Reports')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Project Reports & Financial Analytics</h1>
            <p class="text-muted mb-0 fs-sm">Analyze project execution, delivery milestones, budgets, expenses, and employee task performance.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Back to Projects
            </a>
            @can('project_reports.export')
                <a href="{{ route('projects.reports.export', request()->all()) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-download"></i> Export CSV Report
                </a>
            @endcan
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('projects.reports.index') }}" class="row g-2 align-items-center">
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="status" {{ request('type') === 'status' ? 'selected' : '' }}>Project Status Report</option>
                        <option value="progress" {{ request('type') === 'progress' ? 'selected' : '' }}>Progress Report</option>
                        <option value="tasks" {{ request('type') === 'tasks' ? 'selected' : '' }}>Task Completion</option>
                        <option value="expenses" {{ request('type') === 'expenses' ? 'selected' : '' }}>Expenses Report</option>
                        <option value="profitability" {{ request('type') === 'profitability' ? 'selected' : '' }}>Profitability Report</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="manager_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Managers</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>

                <div class="col-6 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" placeholder="End Date">
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">In Progress</div>
                <div class="h2 fw-bold text-primary mb-0 mt-1">{{ number_format($statusCounts['In Progress']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Completed Projects</div>
                <div class="h2 fw-bold text-success mb-0 mt-1">{{ number_format($statusCounts['Completed']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Completed Tasks</div>
                <div class="h2 fw-bold text-info mb-0 mt-1">{{ number_format($taskCounts['Completed']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Overdue Projects</div>
                <div class="h2 fw-bold text-danger mb-0 mt-1">{{ number_format($statusCounts['Overdue']) }}</div>
            </div>
        </div>
    </div>

    {{-- Report Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light fs-xs text-uppercase text-muted">
                    <tr>
                        <th>Code</th>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Progress</th>
                        @can('project_finance.view')
                            <th>Budget ($)</th>
                            <th>Expenses ($)</th>
                            <th>Est. Profit ($)</th>
                        @endcan
                        <th>Deadline</th>
                        <th>Health</th>
                    </tr>
                </thead>
                <tbody class="fs-sm">
                    @forelse($reportProjects as $project)
                        @php $expenses = $project->expenses->sum('amount'); @endphp
                        <tr>
                            <td class="fw-bold text-muted">{{ $project->project_code }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td>{{ $project->client?->company_name ?? 'N/A' }}</td>
                            <td>{{ $project->manager?->name ?? 'Unassigned' }}</td>
                            <td><span class="badge {{ $project->status_badge_class }} fs-xs">{{ $project->status }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width: 100px;">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%;"></div>
                                    </div>
                                    <span class="fs-xs fw-bold">{{ $project->progress }}%</span>
                                </div>
                            </td>
                            @can('project_finance.view')
                                <td class="fw-bold">${{ number_format($project->budget, 2) }}</td>
                                <td class="text-danger">${{ number_format($expenses, 2) }}</td>
                                <td class="fw-bold text-success">${{ number_format($project->budget - $expenses, 2) }}</td>
                            @endcan
                            <td>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</td>
                            <td><span class="badge {{ $project->health_badge_class }} fs-xs">{{ $project->health }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">No projects found for this report filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
