@extends('layouts.app')

@section('title', 'Project Overview — ' . $project->name)

@section('content')
<div class="container-fluid px-0">
    {{-- Top Banner --}}
    <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-light text-muted border fs-xs">{{ $project->project_code }}</span>
                        <span class="badge bg-secondary-subtle text-secondary fs-xs">{{ $project->project_type }}</span>
                        @if($project->billing_type === 'monthly')
                            <span class="badge bg-info-subtle text-info border border-info-subtle fs-xs"><i class="bi bi-arrow-repeat me-1"></i>Monthly Retainer</span>
                        @elseif($project->billing_type === 'annually')
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-xs"><i class="bi bi-calendar3 me-1"></i>Annual Contract</span>
                        @else
                            <span class="badge bg-light text-muted border fs-xs">One-Time Project</span>
                        @endif
                        @if($project->isRenewalDue())
                            <span class="badge bg-danger fs-xs text-white"><i class="bi bi-exclamation-triangle me-1"></i>Renewal / Payment Due</span>
                        @endif
                        <span class="badge {{ $project->status_badge_class }} fs-xs">{{ $project->status }}</span>
                        <span class="badge {{ $project->priority_badge_class }} fs-xs">{{ $project->priority }} Priority</span>
                        <span class="badge {{ $project->health_badge_class }} fs-xs fw-bold"><i class="bi bi-heart-pulse me-1"></i>Health: {{ $project->health }}</span>
                    </div>
                    <h1 class="h2 fw-bold text-dark mb-1">{{ $project->name }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-3 fs-sm text-muted">
                        <span><i class="bi bi-building me-1 text-primary"></i><strong>Client:</strong> {{ $project->client?->company_name ?? 'Internal Project' }}</span>
                        <span><i class="bi bi-person-badge me-1 text-primary"></i><strong>Manager:</strong> {{ $project->manager?->name ?? 'Unassigned' }}</span>
                        <span><i class="bi bi-calendar-event me-1 text-primary"></i><strong>Deadline:</strong> {{ $project->deadline ? $project->deadline->format('M d, Y') : 'No deadline' }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Back to Projects
                    </a>
                    @if($project->client_id && ($project->recurring_amount > 0 || $project->budget > 0))
                        @can('invoices.create')
                            <form action="{{ route('projects.generate-invoice', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Generate invoice for this project cycle?');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                                    <i class="bi bi-receipt"></i> Generate {{ $project->billing_type === 'monthly' ? 'Monthly' : ($project->billing_type === 'annually' ? 'Annual' : 'Project') }} Invoice
                                </button>
                            </form>
                        @endcan
                    @endif
                    @can('project_tasks.create')
                        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                            <i class="bi bi-plus-lg"></i> Add Task
                        </button>
                    @endcan
                    @can('projects.edit')
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @endcan
                    @can('projects.delete')
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Project">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            {{-- Progress & Top Stat Cards Row --}}
            <div class="row g-3 pt-3 border-top">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="fs-xs text-muted fw-semibold mb-1">Overall Completion</div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $project->progress }}%;"></div>
                        </div>
                        <span class="fw-bold text-dark fs-sm">{{ $project->progress }}%</span>
                    </div>
                </div>

                <div class="col-6 col-md-2 col-lg-2">
                    <div class="fs-xs text-muted fw-semibold">Tasks Completed</div>
                    <div class="fw-bold text-dark fs-6">{{ $completedTasks }} / {{ $totalTasks }}</div>
                </div>

                <div class="col-6 col-md-2 col-lg-2">
                    <div class="fs-xs text-muted fw-semibold">Overdue Tasks</div>
                    <div class="fw-bold {{ $overdueTasks > 0 ? 'text-danger' : 'text-success' }} fs-6">{{ $overdueTasks }}</div>
                </div>

                <div class="col-6 col-md-2 col-lg-2">
                    <div class="fs-xs text-muted fw-semibold">Tracked Time</div>
                    <div class="fw-bold text-dark fs-6">{{ $totalTrackedHours }} hrs</div>
                </div>

                @can('project_finance.view')
                    <div class="col-6 col-md-2 col-lg-3">
                        <div class="fs-xs text-muted fw-semibold">Budget & Profit</div>
                        <div class="fw-bold text-success fs-6">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->budget, 2) }} <span class="fs-xs text-muted">(Est. Profit: {{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($estimatedProfit, 2) }})</span></div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- Main Navigation Tabs --}}
    <ul class="nav nav-pills custom-pills mb-4 gap-2 border-bottom pb-2 flex-wrap" id="projectTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active btn-sm" id="overview-tab" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">
                <i class="bi bi-grid-1x2 me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tab-tasks" type="button" role="tab">
                <i class="bi bi-check2-square me-1"></i> Tasks ({{ $totalTasks }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm" id="team-tab" data-bs-toggle="tab" data-bs-target="#tab-team" type="button" role="tab">
                <i class="bi bi-people me-1"></i> Team ({{ $project->members->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm" id="files-tab" data-bs-toggle="tab" data-bs-target="#tab-files" type="button" role="tab">
                <i class="bi bi-paperclip me-1"></i> Files ({{ $project->files->count() }})
            </button>
        </li>

        @can('project_expenses.view')
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#tab-expenses" type="button" role="tab">
                    <i class="bi bi-receipt me-1"></i> Expenses
                </button>
            </li>
        @endcan
        @can('project_finance.view')
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm" id="financials-tab" data-bs-toggle="tab" data-bs-target="#tab-financials" type="button" role="tab">
                    <i class="bi bi-wallet2 me-1"></i> Financials
                </button>
            </li>
        @endcan
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm" id="activity-tab" data-bs-toggle="tab" data-bs-target="#tab-activity" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Activity
            </button>
        </li>
    </ul>

    {{-- Tab Contents --}}
    <div class="tab-content" id="projectTabContent">
        {{-- 1. OVERVIEW TAB --}}
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    {{-- Visual Timeline --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Visual Project Timeline</h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="position-relative py-2">
                                <div class="timeline-vertical">
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                            <i class="bi bi-play-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-sm">Project Start</div>
                                            <div class="fs-xs text-muted">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'Date not set' }}</div>
                                        </div>
                                    </div>



                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle {{ $project->status === 'Completed' ? 'bg-success' : 'bg-warning' }} text-white p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                            <i class="bi bi-check-all fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-sm">Project Deadline / Target</div>
                                            <div class="fs-xs text-muted">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'No target deadline' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($project->description)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text text-primary me-2"></i>Project Description</h5>
                            </div>
                            <div class="card-body pt-0">
                                <p class="text-secondary mb-0 fs-sm" style="white-space: pre-line;">{{ $project->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-lg-4">
                    {{-- Key Info Box --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-info-square text-primary me-2"></i>Project Summary</h5>
                        </div>
                        <div class="card-body pt-0 fs-sm">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Client</span>
                                <span class="fw-semibold text-dark">{{ $project->client?->company_name ?? 'Internal' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Project Manager</span>
                                <span class="fw-semibold text-dark">{{ $project->manager?->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Estimated Hours</span>
                                <span class="fw-semibold text-dark">{{ number_format($project->estimated_hours, 1) }} hrs</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Actual Tracked</span>
                                <span class="fw-semibold text-dark">{{ number_format($project->actual_hours, 1) }} hrs</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Created Date</span>
                                <span class="fw-semibold text-dark">{{ $project->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Activities --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-activity text-primary me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body pt-0 fs-xs">
                            @forelse($project->activities->take(5) as $act)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="fw-bold text-dark">{{ $act->action }}</div>
                                    <div class="text-muted">{{ $act->description }}</div>
                                    <div class="text-end fs-2xs text-muted mt-1">{{ $act->created_at->diffForHumans() }} by {{ $act->user?->name ?? 'System' }}</div>
                                </div>
                            @empty
                                <div class="text-muted text-center py-3">No activity recorded yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

        {{-- 3. TASKS TAB --}}
        <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <h5 class="fw-bold text-dark mb-0">Tasks Management</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('projects.tasks.kanban', $project) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-kanban me-1"></i> Open Kanban Board
                    </a>
                    @can('project_tasks.create')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                            <i class="bi bi-plus-lg me-1"></i> New Task
                        </button>
                    @endcan
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-cards-lg">
                        <thead class="bg-light fs-xs text-uppercase text-muted">
                            <tr>
                                <th>Task Code</th>
                                <th>Title</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fs-sm">
                            @forelse($project->tasks as $task)
                                <tr>
                                    <td data-label="Task Code" class="fw-bold text-muted">{{ $task->task_code }}</td>
                                    <td data-label="Title">
                                        <a href="{{ route('projects.tasks.show', $task) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                            {{ $task->title }}
                                        </a>

                                    </td>
                                    <td data-label="Assigned To">
                                        @if($task->assignedTo)
                                            <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>{{ $task->assignedTo->name }}</span>
                                        @else
                                            <span class="text-muted fs-xs">Unassigned</span>
                                        @endif
                                    </td>
                                    <td data-label="Priority"><span class="badge {{ $task->priority_badge_class }} fs-xs">{{ $task->priority }}</span></td>
                                    <td data-label="Status">
                                        <select class="form-select form-select-sm task-status-change" data-task-id="{{ $task->id }}">
                                            @foreach(['To Do', 'In Progress', 'Review', 'Testing', 'Completed', 'On Hold', 'Cancelled'] as $st)
                                                <option value="{{ $st }}" {{ $task->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td data-label="Deadline">{{ $task->deadline ? $task->deadline->format('M d, Y — h:i A') : 'N/A' }}</td>
                                    <td data-label="" class="text-end fin-cell-actions">
                                        <a href="{{ route('projects.tasks.show', $task) }}" class="btn btn-sm btn-light" title="View Task"><i class="bi bi-eye"></i></a>
                                        @can('project_tasks.delete')
                                            <form action="{{ route('projects.tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No tasks created yet for this project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. TEAM TAB --}}
        <div class="tab-pane fade" id="tab-team" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Project Team Members</h5>
                @can('project_team.manage')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignTeamModal">
                        <i class="bi bi-person-plus me-1"></i> Assign Team Member
                    </button>
                @endcan
            </div>

            <div class="row g-3">
                @forelse($project->members as $member)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white fs-5 fw-bold d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        {{ strtoupper(substr($member->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">{{ $member->user?->name }}</h6>
                                        <span class="badge bg-primary-subtle text-primary fs-xs">{{ $member->role }}</span>
                                    </div>
                                </div>
                                @can('project_team.manage')
                                    <form action="{{ route('projects.team.destroy', [$project, $member]) }}" method="POST" onsubmit="return confirm('Remove member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm text-danger p-0"><i class="bi bi-x-circle fs-5"></i></button>
                                    </form>
                                @endcan
                            </div>
                            <div class="mt-3 pt-2 border-top fs-xs text-muted d-flex justify-content-between">
                                <span>Assigned: {{ $member->assigned_date ? $member->assigned_date->format('M d, Y') : 'N/A' }}</span>
                                <span>Allocated: {{ $member->hours_allocation }} hrs</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-center p-4 text-muted">
                            No team members assigned yet.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- 5. FILES TAB --}}
        <div class="tab-pane fade" id="tab-files" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Project Documents & Files</h5>
                @can('project_files.upload')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="bi bi-cloud-upload me-1"></i> Upload File
                    </button>
                @endcan
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light fs-xs text-uppercase text-muted">
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fs-sm">
                            @forelse($project->files as $file)
                                <tr>
                                    <td data-label="File Name" class="fw-semibold text-dark">
                                        <i class="bi bi-file-earmark-text text-primary me-2 fs-5"></i>{{ $file->name }}
                                    </td>
                                    <td data-label="Type"><span class="badge bg-secondary-subtle text-secondary fs-xs">{{ $file->file_type }}</span></td>
                                    <td data-label="Size">{{ $file->formatted_size }}</td>
                                    <td data-label="Uploaded By">{{ $file->uploader?->name ?? 'System' }}</td>
                                    <td data-label="Date">{{ $file->created_at->format('M d, Y H:i') }}</td>
                                    <td data-label="" class="text-end fin-cell-actions">
                                        <a href="{{ route('projects.files.download', $file) }}" class="btn btn-sm btn-light me-1" title="Download"><i class="bi bi-download"></i></a>
                                        @can('project_files.delete')
                                            <form action="{{ route('projects.files.destroy', $file) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No files uploaded for this project yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        {{-- 7. EXPENSES TAB --}}
        @can('project_expenses.view')
            <div class="tab-pane fade" id="tab-expenses" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">Project Expenses</h5>
                    @can('project_expenses.create')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                            <i class="bi bi-plus-lg me-1"></i> Record Expense
                        </button>
                    @endcan
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light fs-xs text-uppercase text-muted">
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Added By</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="fs-sm">
                                @forelse($project->expenses as $expense)
                                    <tr>
                                        <td data-label="Category"><span class="badge bg-secondary-subtle text-secondary fs-xs">{{ $expense->category }}</span></td>
                                        <td data-label="Amount" class="fw-bold text-danger">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($expense->amount, 2) }}</td>
                                        <td data-label="Date">{{ $expense->expense_date->format('M d, Y') }}</td>
                                        <td data-label="Description" class="text-muted">{{ $expense->description }}</td>
                                        <td data-label="Added By">{{ $expense->addedBy?->name ?? 'N/A' }}</td>
                                        <td data-label="" class="text-end fin-cell-actions">
                                            @can('project_expenses.edit')
                                                <form action="{{ route('projects.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete expense?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No expenses recorded for this project yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan

        {{-- 8. FINANCIALS TAB --}}
        @can('project_finance.view')
            <div class="tab-pane fade" id="tab-financials" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3">
                            <div class="text-muted fs-xs text-uppercase">Project Budget</div>
                            <div class="h3 fw-bold text-dark mt-1 mb-0">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->budget, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3">
                            <div class="text-muted fs-xs text-uppercase">Total Received / Paid</div>
                            <div class="h3 fw-bold text-success mt-1 mb-0">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3">
                            <div class="text-muted fs-xs text-uppercase">Remaining Balance</div>
                            <div class="h3 fw-bold text-danger mt-1 mb-0">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->remaining_balance, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3">
                            <div class="text-muted fs-xs text-uppercase">Realized Profit</div>
                            <div class="h3 fw-bold text-info mt-1 mb-0">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($actualProfit, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm p-3 p-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i>Client Payment Receipts Breakdown</h5>
                                    <span class="fs-xs text-muted">History of installments received for this project</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 fs-sm table-cards-lg">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Receipt #</th>
                                            <th>Method</th>
                                            <th>Ref / Trx ID</th>
                                            <th>Notes</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($project->payments as $idx => $payment)
                                            <tr>
                                                <td data-label="#">{{ $idx + 1 }}</td>
                                                <td data-label="Date" class="fw-semibold">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—' }}</td>
                                                <td data-label="Receipt #" class="fw-bold text-primary">{{ $payment->payment_number }}</td>
                                                <td data-label="Method" class="text-capitalize"><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $payment->method) }}</span></td>
                                                <td data-label="Ref / Trx ID">{{ $payment->reference_number ?: '—' }}</td>
                                                <td data-label="Notes" class="text-muted">{{ $payment->notes ?: '—' }}</td>
                                                <td data-label="Amount" class="text-end fw-bold text-success">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="bi bi-info-circle me-1"></i> No payments recorded yet. Record payments against this client's invoices to see them here.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-light">
                                            <td colspan="6" class="text-end text-uppercase">Total Received:</td>
                                            <td class="text-end text-success fs-6">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->paid_amount, 2) }}</td>
                                        </tr>
                                        <tr class="fw-bold bg-danger-subtle text-danger">
                                            <td colspan="6" class="text-end text-uppercase">Remaining Balance:</td>
                                            <td class="text-end fs-6">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->remaining_balance, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm p-4 h-100">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-calculator text-primary me-2"></i>Profitability Analysis</h5>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>Project Budget</span>
                                <span class="fw-bold">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($project->budget, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>Total Project Expenses</span>
                                <span class="fw-bold text-danger">-{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($totalExpenses, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>Estimated Profit</span>
                                <span class="fw-bold text-success">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($estimatedProfit, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 pt-3">
                                <span class="fw-bold">Actual Realized Profit</span>
                                <span class="fw-bold fs-5 text-success">{{ $project->currency ?? setting('general.currency','PKR') }} {{ number_format($actualProfit, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- 9. ACTIVITY TAB --}}
        <div class="tab-pane fade" id="tab-activity" role="tabpanel">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-clock-history text-primary me-2"></i>Full Project Activity Log</h5>
                <div class="timeline-vertical">
                    @forelse($project->activities as $activity)
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="rounded-circle bg-light border p-2 text-primary d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                <i class="bi bi-activity fs-6"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-sm">{{ $activity->action }}</div>
                                <div class="fs-xs text-muted mb-1">{{ $activity->description }}</div>
                                <div class="fs-2xs text-muted">{{ $activity->created_at->format('M d, Y h:i A') }} by <strong>{{ $activity->user?->name ?? 'System' }}</strong></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">No activity logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Create Task Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('projects.tasks.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold fs-sm">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Design API Endpoint for Payments" required>
                    </div>



                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold fs-sm">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold fs-sm">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold fs-sm">Status</label>
                        <select name="status" class="form-select">
                            <option value="To Do" selected>To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Review">Review</option>
                            <option value="Testing">Testing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold fs-sm">Deadline Date</label>
                        <input type="date" name="deadline" class="form-control">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold fs-sm">Deadline Time</label>
                        <input type="time" name="deadline_time" class="form-control" value="18:00">
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Est. Hours</label>
                        <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" value="0">
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold fs-sm">Task Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Detailed instructions for the task..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 mt-3">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Task</button>
            </div>
        </form>
    </div>
</div>



{{-- Assign Team Modal --}}
<div class="modal fade" id="assignTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.team.store', $project) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Assign Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Employee <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        @foreach(['Project Manager', 'Team Lead', 'Developer', 'Designer', 'QA', 'SEO', 'Content Writer', 'Sales', 'Other'] as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Hours Allocation</label>
                    <input type="number" step="1" name="hours_allocation" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Assign Member</button>
            </div>
        </form>
    </div>
</div>

{{-- Upload File Modal --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.files.store', $project) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-upload text-primary me-2"></i>Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Category <span class="text-danger">*</span></label>
                    <select name="file_type" class="form-select" required>
                        @foreach(['Requirements', 'Design Files', 'Client Documents', 'Contracts', 'Screenshots', 'Source Documents', 'Other Files'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Upload File</button>
            </div>
        </form>
    </div>
</div>

{{-- Manual Time Entry Modal --}}
<div class="modal fade" id="manualTimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.time.manual') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-stopwatch text-primary me-2"></i>Log Hours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Task (Optional)</label>
                    <select name="task_id" class="form-select">
                        <option value="">-- General Project Work --</option>
                        @foreach($project->tasks as $t)
                            <option value="{{ $t->id }}">{{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Hours <span class="text-danger">*</span></label>
                        <input type="number" step="0.25" min="0.1" name="hours" class="form-control" placeholder="2.5" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Work Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Summary of work performed..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Log Time</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Expense Modal --}}
@can('project_expenses.create')
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.expenses.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Record Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Expense Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="Hosting & Domain">Hosting & Domain</option>
                        <option value="Software Licenses">Software Licenses</option>
                        <option value="Subcontractor / Freelancer">Subcontractor / Freelancer</option>
                        <option value="Assets & Hardware">Assets & Hardware</option>
                        <option value="Travel & Logistics">Travel & Logistics</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Receipt Attachment</label>
                    <input type="file" name="receipt" class="form-control">
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Expense</button>
            </div>
        </form>
    </div>
</div>
@endcan


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Quick task status change via AJAX
        document.querySelectorAll('.task-status-change').forEach(select => {
            select.addEventListener('change', function () {
                const taskId = this.getAttribute('data-task-id');
                const status = this.value;

                fetch(`{{ url('/projects/tasks') }}/${taskId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
