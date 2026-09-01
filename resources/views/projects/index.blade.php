@extends('layouts.app')

@section('title', 'Projects Overview')

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Projects Directory</h1>
            <p class="text-muted mb-0 fs-sm">Manage software house projects, track health, deadlines, and financial velocity.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('projects.export')
                <a href="{{ route('projects.export', request()->all()) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            @endcan
            <button type="button" onclick="printUrlInHiddenFrame('{{ route('projects.print', request()->all()) }}')" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> Print
            </button>
            @can('projects.create')
                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importProjectsModal">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                </button>
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> New Project
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats Widgets --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-xs fw-semibold text-uppercase tracking-wider">Total Projects</div>
                        <div class="h2 fw-bold text-dark mb-0 mt-1">{{ number_format($totalProjects) }}</div>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-xs fw-semibold text-uppercase tracking-wider">Active Running</div>
                        <div class="h2 fw-bold text-primary mb-0 mt-1">{{ number_format($activeProjects) }}</div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-play-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-xs fw-semibold text-uppercase tracking-wider">Completed</div>
                        <div class="h2 fw-bold text-success mb-0 mt-1">{{ number_format($completedProjects) }}</div>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-xs fw-semibold text-uppercase tracking-wider">Overdue / Delayed</div>
                        <div class="h2 fw-bold text-danger mb-0 mt-1">{{ number_format($overdueProjects) }}</div>
                    </div>
                    <div class="rounded-3 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search project, code, client..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach(['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled', 'Overdue'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Priority --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        @foreach(['Low', 'Medium', 'High', 'Urgent'] as $pr)
                            <option value="{{ $pr }}" {{ request('priority') === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Client --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Manager --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <select name="manager_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Managers</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Overdue Checkbox & Reset --}}
                <div class="col-12 col-md-12 col-lg-1 d-flex align-items-center justify-content-end gap-2 mt-2 mt-lg-0">
                    <div class="form-check me-auto me-lg-0">
                        <input class="form-check-input" type="checkbox" name="overdue" value="1" id="overdueCheck" {{ request()->boolean('overdue') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fs-xs text-danger fw-semibold" for="overdueCheck">Overdue</label>
                    </div>
                    @if(request()->anyFilled(['search', 'status', 'priority', 'client_id', 'manager_id', 'overdue', 'start_date', 'deadline']))
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-light text-muted" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Projects Cards Listing --}}
    @if($projects->isEmpty())
        <div class="card border-0 shadow-sm text-center p-5">
            <div class="py-4">
                <i class="bi bi-folder-x display-3 text-muted"></i>
                <h4 class="fw-bold mt-3">No Projects Found</h4>
                <p class="text-muted">No project records matched your filters. Create a new project to get started.</p>
                @can('projects.create')
                    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm mt-2"><i class="bi bi-plus-lg me-1"></i> Create Project</a>
                @endcan
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($projects as $project)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100 hover-shadow transition">
                        <div class="card-body d-flex flex-column p-4">
                            {{-- Card Top Row --}}
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <div>
                                    <span class="badge bg-light text-muted border fs-xs mb-1">{{ $project->project_code }}</span>
                                    <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 240px;">
                                        <a href="{{ route('projects.show', $project) }}" class="text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                    </h5>
                                    <div class="fs-xs text-muted mt-1">
                                        <i class="bi bi-building me-1"></i> {{ $project->client?->company_name ?? 'Internal / No Client' }}
                                    </div>
                                </div>
                                {{-- Health Badge --}}
                                <span class="badge {{ $project->health_badge_class }} fs-xs px-2 py-1">
                                    <i class="bi bi-heart-pulse me-1"></i>{{ $project->health }}
                                </span>
                            </div>

                            {{-- Project Type & Priority --}}
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-secondary-subtle text-secondary fs-xs fw-normal">{{ $project->project_type }}</span>
                                <span class="badge {{ $project->priority_badge_class }} fs-xs">{{ $project->priority }} Priority</span>
                                <span class="badge {{ $project->status_badge_class }} fs-xs ms-auto">{{ $project->status }}</span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center fs-xs mb-1">
                                    <span class="text-muted fw-semibold">Overall Progress</span>
                                    <span class="fw-bold text-primary">{{ $project->progress }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%;" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            {{-- Manager & Dates --}}
                            <div class="row g-2 fs-xs text-muted bg-light rounded-3 p-2 mb-3 mt-auto">
                                <div class="col-6">
                                    <div class="fw-semibold text-dark">Manager</div>
                                    <div class="text-truncate"><i class="bi bi-person me-1"></i>{{ $project->manager?->name ?? 'Unassigned' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="fw-semibold text-dark">Deadline</div>
                                    <div class="{{ $project->deadline && $project->deadline->isPast() && $project->status !== 'Completed' ? 'text-danger fw-bold' : '' }}">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'No deadline' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Card Footer Actions & Team Avatars --}}
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                {{-- Team Avatars --}}
                                <div class="d-flex align-items-center">
                                    @foreach($project->members->take(3) as $m)
                                        <div class="rounded-circle bg-primary text-white fs-xs d-flex align-items-center justify-content-center me-n2 border border-2 border-white" style="width: 28px; height: 28px;" title="{{ $m->user?->name }} ({{ $m->role }})">
                                            {{ strtoupper(substr($m->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endforeach
                                    @if($project->members->count() > 3)
                                        <div class="rounded-circle bg-secondary text-white fs-xs d-flex align-items-center justify-content-center border border-2 border-white ms-2" style="width: 28px; height: 28px;">
                                            +{{ $project->members->count() - 3 }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Action Link --}}
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                    View Overview <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end mt-4">
            {{ $projects->links() }}
        </div>
    @endif

    {{-- Import Projects Modal --}}
    @can('projects.create')
    <div class="modal fade" id="importProjectsModal" tabindex="-1" aria-labelledby="importProjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="importProjectsModalLabel">
                            <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>Import Projects (CSV)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted fs-sm mb-3">Upload a CSV file containing project details. Make sure your CSV file matches the sample template format.</p>
                        
                        {{-- Required & Optional Fields Guidance --}}
                        <div class="alert alert-info py-2.5 px-3 mb-3 border-0 shadow-sm rounded-3">
                            <div class="fw-semibold fs-xs text-primary mb-1">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>Required Field (Must be filled in CSV):
                            </div>
                            <div class="fs-xs text-dark ms-3 mb-2">
                                • <strong class="text-danger">Project Name</strong> <span class="text-muted">(Headers: <code>Project Name</code>, <code>name</code>, <code>title</code>)</span>
                            </div>
                            <div class="fw-semibold fs-xs text-secondary mb-1">
                                <i class="bi bi-info-circle me-1"></i>Optional Fields (Will use defaults if empty):
                            </div>
                            <div class="fs-xs text-muted ms-3">
                                • <strong>Project Type</strong>, <strong>Description</strong>, <strong>Start Date</strong>, <strong>Deadline</strong>, <strong>Priority</strong>, <strong>Status</strong>, <strong>Budget</strong>, <strong>Currency</strong>, <strong>Estimated Hours</strong>, <strong>Notes</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".csv, .txt" required>
                        </div>

                        <div class="bg-light rounded-3 p-3 mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fs-xs text-muted">Need sample formatting?</span>
                                <a href="{{ route('projects.import-template') }}" class="btn btn-sm btn-link p-0 text-decoration-none fs-xs fw-bold">
                                    <i class="bi bi-download me-1"></i> Download CSV Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Upload & Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
