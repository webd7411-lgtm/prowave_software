@extends('layouts.app')

@section('title', 'Tasks Management')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                {{ request('view') === 'my' ? 'My Assigned Tasks' : 'All Project Tasks' }}
            </h1>
            <p class="text-muted mb-0 fs-sm">Filter tasks by status, priority, project, deadline, or view your assigned tasks.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm me-2">
                <a href="{{ route('projects.tasks.index') }}" class="btn {{ request('view') !== 'my' ? 'btn-primary' : 'btn-outline-secondary' }}">All Tasks</a>
                <a href="{{ route('projects.tasks.index', ['view' => 'my']) }}" class="btn {{ request('view') === 'my' ? 'btn-primary' : 'btn-outline-secondary' }}"><i class="bi bi-person me-1"></i>My Tasks</a>
            </div>

            <a href="{{ route('projects.tasks.kanban') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-kanban me-1"></i> Kanban Board
            </a>
            @can('project_tasks.create')
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskGlobalModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Task
                </button>
            @endcan
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('projects.tasks.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="view" value="{{ request('view') }}">

                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search task, code..." value="{{ request('search') }}">
                    </div>
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
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach(['To Do', 'In Progress', 'Review', 'Testing', 'Completed', 'On Hold', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Deadlines</option>
                        <option value="today" {{ request('filter') === 'today' ? 'selected' : '' }}>Due Today</option>
                        <option value="upcoming" {{ request('filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="completed" {{ request('filter') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        @foreach(['Low', 'Medium', 'High', 'Urgent'] as $pr)
                            <option value="{{ $pr }}" {{ request('priority') === $pr ? 'selected' : '' }}>{{ $pr }} Priority</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-1 text-end">
                    @if(request()->anyFilled(['search', 'project_id', 'status', 'filter', 'priority']))
                        <a href="{{ route('projects.tasks.index', ['view' => request('view')]) }}" class="btn btn-sm btn-light text-muted" title="Clear"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tasks Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light fs-xs text-uppercase text-muted">
                    <tr>
                        <th>Task Code</th>
                        <th>Task Title</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Est / Act Hours</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="fs-sm">
                    @forelse($tasks as $task)
                        <tr>
                            <td class="fw-bold text-muted">{{ $task->task_code }}</td>
                            <td>
                                <a href="{{ route('projects.tasks.show', $task) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                    {{ $task->title }}
                                </a>
                                @if($task->milestone)
                                    <div class="fs-xs text-muted"><i class="bi bi-flag me-1"></i>{{ $task->milestone->name }}</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $task->project) }}" class="text-secondary text-decoration-none hover-primary fw-medium">
                                    {{ $task->project->name }}
                                </a>
                            </td>
                            <td>
                                @if($task->assignedTo)
                                    <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>{{ $task->assignedTo->name }}</span>
                                @else
                                    <span class="text-muted fs-xs">Unassigned</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $task->priority_badge_class }} fs-xs">{{ $task->priority }}</span></td>
                            <td><span class="badge {{ $task->status_badge_class }} fs-xs">{{ $task->status }}</span></td>
                            <td class="{{ $task->deadline && $task->deadline->isPast() && $task->status !== 'Completed' ? 'text-danger fw-bold' : '' }}">
                                {{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}
                            </td>
                            <td>{{ $task->estimated_hours }} / {{ $task->actual_hours }} hrs</td>
                            <td class="text-end">
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
                            <td colspan="9" class="text-center py-5 text-muted">No tasks found matching your filter criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-end">
                    {{ $tasks->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Global Task Create Modal --}}
<div class="modal fade" id="createTaskGlobalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('projects.tasks.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Create Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold fs-sm">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->project_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold fs-sm">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold fs-sm">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold fs-sm">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold fs-sm">Deadline</label>
                        <input type="date" name="deadline" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold fs-sm">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 mt-3">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Task</button>
            </div>
        </form>
    </div>
</div>
@endsection
