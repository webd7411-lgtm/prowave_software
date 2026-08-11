@extends('layouts.app')

@section('title', 'Task Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Task Report</li>
                </ol>
            </nav>
            <h1 class="page-title">Task Report</h1>
            <p class="page-subtitle">{{ $tasks->count() }} task(s) found.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Tasks" :value="$summary['total']" icon="bi-list-task" variant="primary" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Completed" :value="$summary['completed']" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="In Progress / Open" :value="$summary['pending']" icon="bi-hourglass-split" variant="warning" />
        </div>
    </div>

    <x-card body-class="p-3 print-hide">
        <form method="GET" action="{{ route('hr.reports.tasks') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Enums\TaskStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
            @if ($filters)
                <div class="col-12">
                    <a href="{{ route('hr.reports.tasks') }}" class="text-muted small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Clear filters</a>
                </div>
            @endif
        </form>
    </x-card>

    @if ($tasks->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Assignee</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Est./Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td class="fw-500">
                                    {{ $task->title }}
                                    @if ($task->description)
                                        <div class="fs-xs text-muted text-truncate" style="max-width:260px" title="{{ $task->description }}">{{ $task->description }}</div>
                                    @endif
                                </td>
                                <td>{{ $task->assignee?->name }}</td>
                                <td>{{ $task->department?->name ?? '—' }}</td>
                                <td><span class="badge badge-soft-{{ $task->priority?->color() ?? 'secondary' }}">{{ $task->priority?->label() ?? '—' }}</span></td>
                                <td class="fs-sm">{{ $task->deadline ? format_date($task->deadline) : '—' }}</td>
                                <td><span class="badge badge-soft-{{ $task->status->color() }}">{{ $task->status->label() }}</span></td>
                                <td class="fs-sm">{{ $task->estimated_hours ?? '—' }} / {{ $task->actual_hours ?? '—' }} hr(s)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state title="No tasks found" text="Adjust your filters to see results." />
    @endif
@endsection