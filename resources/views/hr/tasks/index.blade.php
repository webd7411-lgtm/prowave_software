@extends('layouts.app')

@section('title', 'Daily Tasks')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Daily Tasks</li>
                </ol>
            </nav>
            <h1 class="page-title">Daily Tasks</h1>
            <p class="page-subtitle">Assign, track, and manage daily work tasks.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hr.tasks.board') }}" class="btn btn-soft-primary">
                <i class="bi bi-kanban me-1"></i>My Board
            </a>
            @can('tasks.create')
                <a href="{{ route('hr.tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>New Task
                </a>
            @endcan
        </div>
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.tasks.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Search</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search tasks...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Assigned To</label>
                <select name="assigned_to" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($employees as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['assigned_to'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['department_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['priority'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
        <div class="mt-2">
            <a href="{{ route('hr.tasks.index', array_merge(request()->except('my'), ['my' => 1])) }}" class="text-muted small text-decoration-none {{ ! empty($filters['my']) ? 'fw-600' : '' }}">
                <i class="bi bi-person-check me-1"></i>Only my tasks
            </a>
        </div>
    </x-card>

    @if ($tasks->count())
        <div class="d-md-none">
            @foreach ($tasks as $task)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-600 text-truncate">{{ $task->title }}</div>
                                <div class="fs-xs text-muted mt-1">
                                    <div><i class="bi bi-person me-1"></i>{{ $task->assignee?->name }}</div>
                                    <div><i class="bi bi-tag me-1"></i>{{ $task->department?->name ?? '—' }}</div>
                                    @if ($task->deadline)
                                        <div><i class="bi bi-calendar-event me-1"></i>Due {{ format_date($task->deadline) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge badge-soft-{{ $task->priority?->color() ?? 'secondary' }}">{{ $task->priority?->label() ?? '—' }}</span>
                                <div class="mt-1"><span class="badge badge-soft-{{ $task->status->color() }}">{{ $task->status->label() }}</span></div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-1 mt-2 pt-2 border-top">
                            @can('update', $task)
                                <a href="{{ route('hr.tasks.edit', $task) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil me-1"></i>Edit</a>
                            @endcan
                            @can('delete', $task)
                                <form method="POST" action="{{ route('hr.tasks.destroy', $task) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete task?" data-confirm-text="This task will be permanently deleted." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-cards-lg">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Assignee</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td data-label="Task">
                                    <div class="fw-500">{{ $task->title }}</div>
                                    @if ($task->description)
                                        <div class="fs-xs text-muted text-break" title="{{ $task->description }}">{{ \Illuminate\Support\Str::limit($task->description, 80) }}</div>
                                    @endif
                                </td>
                                <td data-label="Assignee">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ user_avatar($task->assignee) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover">
                                        <span class="fs-sm">{{ $task->assignee?->name }}</span>
                                    </div>
                                </td>
                                <td data-label="Department" class="fs-sm">{{ $task->department?->name ?? '—' }}</td>
                                <td data-label="Priority"><span class="badge badge-soft-{{ $task->priority?->color() ?? 'secondary' }}">{{ $task->priority?->label() ?? '—' }}</span></td>
                                <td data-label="Deadline" class="fs-sm">
                                    @if ($task->deadline)
                                        <span class="{{ $task->isOverdue() ? 'text-danger fw-600' : '' }}">{{ format_date($task->deadline) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <form method="POST" action="{{ route('hr.tasks.status', $task) }}" class="d-flex align-items-center gap-1">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm task-status-select" data-task-id="{{ $task->id }}">
                                            @foreach ($statuses as $value => $label)
                                                <option value="{{ $value }}" @selected($task->status->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <noscript><button type="submit" class="btn btn-sm btn-soft-primary"><i class="bi bi-check"></i></button></noscript>
                                    </form>
                                </td>
                                <td data-label="" class="text-end fin-cell-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        @can('update', $task)
                                            <a href="{{ route('hr.tasks.edit', $task) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $task)
                                            <form method="POST" action="{{ route('hr.tasks.destroy', $task) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete task?" data-confirm-text="This task will be permanently deleted." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $tasks->links() }}
        </div>
    @else
        <x-empty-state
            title="No tasks found"
            text="Try adjusting your filters or create a new task."
            :action="auth()->user()->can('tasks.create') ? route('hr.tasks.create') : null"
            action-text="New Task"
        />
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.task-status-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    const form = select.closest('form');
                    if (form) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
