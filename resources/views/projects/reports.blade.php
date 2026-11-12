@extends('layouts.app')

@section('title', 'Project Analytics & Reports')

@section('content')
<style>
@media print {
    nav, sidebar, .sidebar, .navbar, .btn, form, .no-print, header, footer, .card-header {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: system-ui, -apple-system, sans-serif !important;
        font-size: 11px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-top: 10px !important;
    }
    .table th, .table td {
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
        color: #0f172a !important;
        font-size: 11px !important;
    }
    .table th {
        background-color: #f1f5f9 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
    }
    .badge {
        border: 1px solid #94a3b8 !important;
        color: #000000 !important;
        background: transparent !important;
        font-weight: 600 !important;
        padding: 2px 6px !important;
    }
    .progress {
        border: 1px solid #cbd5e1 !important;
        background-color: #e2e8f0 !important;
    }
    .progress-bar {
        background-color: #2563eb !important;
    }
    @page {
        size: landscape;
        margin: 12mm;
    }
}
</style>

<div class="container-fluid px-0">
    {{-- Printable Document Header --}}
    <div class="d-none d-print-block text-center mb-4 pb-2 border-bottom">
        <h2 class="fw-bold mb-1 text-dark">Project Analytics & Financial Report</h2>
        <p class="text-muted small mb-0">Generated on {{ now('Asia/Karachi')->format('M d, Y h:i A') }} — {{ setting('general.company_name', 'Prowave Technologies') }}</p>
    </div>

    {{-- Screen Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 no-print">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Project Reports & Financial Analytics</h1>
            <p class="text-muted mb-0 fs-sm">Analyze project execution, delivery budgets, expenses, and employee task performance.</p>
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
            <button type="button" onclick="printUrlInHiddenFrame('{{ route('projects.reports.print', request()->all()) }}')" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> Print List
            </button>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
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
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">In Progress</div>
                <div class="h2 fw-bold text-primary mb-0 mt-1">{{ number_format($statusCounts['In Progress']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Completed Projects</div>
                <div class="h2 fw-bold text-success mb-0 mt-1">{{ number_format($statusCounts['Completed']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Completed Tasks</div>
                <div class="h2 fw-bold text-info mb-0 mt-1">{{ number_format($taskCounts['Completed']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="text-muted fs-xs font-semibold text-uppercase">Overdue Projects</div>
                <div class="h2 fw-bold text-danger mb-0 mt-1">{{ number_format($statusCounts['Overdue']) }}</div>
            </div>
        </div>
    </div>

    {{-- Report Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards-lg">
                <thead class="bg-light fs-xs text-uppercase text-muted">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Code</th>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Progress</th>
                        @can('project_finance.view')
                            <th class="text-end">Budget ({{ setting('general.currency', 'PKR') }})</th>
                            <th class="text-end">Expenses ({{ setting('general.currency', 'PKR') }})</th>
                            <th class="text-end">Est. Profit ({{ setting('general.currency', 'PKR') }})</th>
                        @endcan
                        <th>Deadline</th>
                        <th>Health</th>
                    </tr>
                </thead>
                <tbody class="fs-sm">
                    @forelse($reportProjects as $project)
                        @php $expenses = $project->expenses->sum('amount'); @endphp
                        <tr>
                            <td data-label="#" class="text-muted small">{{ $loop->iteration }}</td>
                            <td data-label="Code" class="fw-bold text-muted">{{ $project->project_code }}</td>
                            <td data-label="Project Name">
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td data-label="Client">{{ $project->client?->company_name ?? 'N/A' }}</td>
                            <td data-label="Manager">{{ $project->manager?->name ?? 'Unassigned' }}</td>
                            <td data-label="Status"><span class="badge {{ $project->status_badge_class }} fs-xs">{{ $project->status }}</span></td>
                            <td data-label="Progress">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; min-width: 70px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%;"></div>
                                    </div>
                                    <span class="fs-xs fw-bold">{{ $project->progress }}%</span>
                                </div>
                            </td>
                            @can('project_finance.view')
                                <td data-label="Budget" class="fw-bold text-end">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->budget, 2) }}</td>
                                <td data-label="Expenses" class="text-danger text-end">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($expenses, 2) }}</td>
                                <td data-label="Est. Profit" class="fw-bold text-success text-end">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->budget - $expenses, 2) }}</td>
                            @endcan
                            <td data-label="Deadline">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</td>
                            <td data-label="Health"><span class="badge {{ $project->health_badge_class }} fs-xs">{{ $project->health }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">No projects found for this report filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
