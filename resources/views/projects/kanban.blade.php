@extends('layouts.app')

@section('title', 'Project Kanban Board')

@section('content')
<div class="container-fluid px-0 kanban-page-wrapper">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Kanban Task Board</h1>
            <p class="text-muted mb-0 fs-sm">
                @if($project)
                    Managing tasks for project: <strong class="text-primary">{{ $project->name }}</strong> ({{ $project->project_code }})
                @else
                    Visual workflow board across all active software projects.
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('projects.tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list-task me-1"></i> List View
            </a>
            @can('project_tasks.create')
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createKanbanTaskModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Task
                </button>
            @endcan
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ $project ? route('projects.tasks.kanban', $project) : route('projects.tasks.kanban') }}" class="row g-2 align-items-center">
                @if(!$project)
                    <div class="col-12 col-md-3">
                        <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Projects</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->project_code }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Assignees</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
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

                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Deadlines</option>
                        <option value="today" {{ request('filter') === 'today' ? 'selected' : '' }}>Due Today</option>
                        <option value="this_week" {{ request('filter') === 'this_week' ? 'selected' : '' }}>Due This Week</option>
                        <option value="upcoming" {{ request('filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <div class="col-6 col-md-1-5">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" title="Deadline From Date" onchange="this.form.submit()">
                </div>

                <div class="col-6 col-md-1-5">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" title="Deadline To Date" onchange="this.form.submit()">
                </div>

                <div class="col-12 col-md-1 text-end">
                    @if(request()->anyFilled(['project_id', 'assigned_to', 'priority', 'filter', 'date_from', 'date_to']))
                        <a href="{{ $project ? route('projects.tasks.kanban', $project) : route('projects.tasks.kanban') }}" class="btn btn-sm btn-light text-muted" title="Clear Filters"><i class="bi bi-x-circle"></i> Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile Tab Switcher --}}
    <div class="d-md-none mb-3">
        <select class="form-select" id="mobileKanbanSelect">
            <option value="col-todo">To Do ({{ count($columns['To Do']) }})</option>
            <option value="col-in-progress">In Progress ({{ count($columns['In Progress']) }})</option>
            <option value="col-review">Review ({{ count($columns['Review']) }})</option>
            <option value="col-testing">Testing ({{ count($columns['Testing']) }})</option>
            <option value="col-completed">Completed ({{ count($columns['Completed']) }})</option>
        </select>
    </div>

    {{-- Kanban Board Columns Grid --}}
    <div class="row g-3 pb-4 kanban-board-grid">
        @php
            $columnConfig = [
                'To Do' => ['id' => 'col-todo', 'color' => 'border-secondary', 'badge' => 'bg-secondary'],
                'In Progress' => ['id' => 'col-in-progress', 'color' => 'border-primary', 'badge' => 'bg-primary'],
                'Review' => ['id' => 'col-review', 'color' => 'border-info', 'badge' => 'bg-info'],
                'Testing' => ['id' => 'col-testing', 'color' => 'border-purple', 'badge' => 'bg-dark'],
                'Completed' => ['id' => 'col-completed', 'color' => 'border-success', 'badge' => 'bg-success'],
            ];
        @endphp

        @foreach($columns as $colTitle => $tasks)
            @php $cfg = $columnConfig[$colTitle]; @endphp
            <div class="col-12 col-md-6 col-lg-4 col-xl-2-4 kanban-column-wrapper {{ $cfg['id'] }}">
                <div class="bg-light rounded-3 p-3 h-100 border-top border-4 {{ $cfg['color'] }} d-flex flex-column">
                    {{-- Column Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span>{{ $colTitle }}</span>
                            <span class="badge rounded-pill {{ $cfg['badge'] }} fs-2xs">{{ count($tasks) }}</span>
                        </h6>
                    </div>

                    {{-- Cards Drop Zone Container --}}
                    <div class="kanban-drop-zone flex-grow-1" data-status="{{ $colTitle }}">
                        @forelse($tasks as $task)
                            <div class="card border-0 shadow-sm mb-3 kanban-card draggable-card hover-shadow transition" data-task-id="{{ $task->id }}">
                                <div class="card-body p-3">
                                    {{-- Code & Priority --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-light text-muted border fs-2xs">{{ $task->task_code }}</span>
                                        <span class="badge {{ $task->priority_badge_class }} fs-2xs">{{ $task->priority }}</span>
                                    </div>

                                    {{-- Title --}}
                                    <h6 class="fw-bold text-dark mb-1 fs-sm">
                                        <a href="{{ route('projects.tasks.show', $task) }}" class="text-dark text-decoration-none hover-primary">
                                            {{ $task->title }}
                                        </a>
                                    </h6>

                                    {{-- Project Subtitle --}}
                                    <div class="fs-2xs text-muted mb-2 text-truncate">
                                        <i class="bi bi-folder me-1"></i>{{ $task->project->name }}
                                    </div>

                                    {{-- Deadline & Stats --}}
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top fs-2xs text-muted mt-2">
                                        <div class="{{ $task->deadline && $task->deadline->isPast() && $task->status !== 'Completed' ? 'text-danger fw-bold' : '' }}">
                                            <i class="bi bi-clock me-1"></i>{{ $task->deadline ? $task->deadline->format('M d') : 'No due' }}
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            @if($task->comments->count() > 0)
                                                <span><i class="bi bi-chat me-1"></i>{{ $task->comments->count() }}</span>
                                            @endif
                                            @if($task->attachments->count() > 0)
                                                <span><i class="bi bi-paperclip me-1"></i>{{ $task->attachments->count() }}</span>
                                            @endif
                                            @if($task->assignedTo)
                                                <div class="rounded-circle bg-primary text-white fs-2xs d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" title="{{ $task->assignedTo->name }}">
                                                    {{ strtoupper(substr($task->assignedTo->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Quick Move Dropdown --}}
                                    <div class="mt-2 pt-2 border-top d-flex justify-content-end">
                                        <select class="form-select form-select-2xs kanban-quick-move" data-task-id="{{ $task->id }}" style="width:110px; font-size:11px;">
                                            @foreach(['To Do', 'In Progress', 'Review', 'Testing', 'Completed'] as $st)
                                                <option value="{{ $st }}" {{ $task->status === $st ? 'selected' : '' }}>Move: {{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4 fs-xs border border-dashed rounded-3 bg-white">
                                Drop or move tasks here
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Create Kanban Task Modal --}}
<div class="modal fade" id="createKanbanTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.tasks.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Project <span class="text-danger">*</span></label>
                    <select name="project_id" class="form-select" required>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ ($project && $project->id == $p->id) ? 'selected' : '' }}>{{ $p->name }} ({{ $p->project_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Task Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold fs-sm">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-4">
                        <label class="form-label fw-semibold fs-sm">Status</label>
                        <select name="status" class="form-select">
                            <option value="To Do" selected>To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Review">Review</option>
                            <option value="Testing">Testing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="form-label fw-semibold fs-sm">Deadline Date</label>
                        <input type="date" name="deadline" class="form-control">
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="form-label fw-semibold fs-sm">Time</label>
                        <input type="time" name="deadline_time" class="form-control" value="18:00">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Task</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Quick move change event handler
        document.querySelectorAll('.kanban-quick-move').forEach(select => {
            select.addEventListener('change', function () {
                const taskId = this.getAttribute('data-task-id');
                const newStatus = this.value;

                fetch(`/projects/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            });
        });

        // Mobile column switcher (sync on load + resize)
        const mobileSelect = document.getElementById('mobileKanbanSelect');
        function syncKanbanColumns() {
            const cols = document.querySelectorAll('.kanban-column-wrapper');
            if (window.innerWidth < 768 && mobileSelect) {
                const targetId = mobileSelect.value;
                cols.forEach(col => {
                    col.style.display = col.classList.contains(targetId) ? 'block' : 'none';
                });
            } else {
                cols.forEach(col => { col.style.display = ''; });
            }
        }
        if (mobileSelect) {
            mobileSelect.addEventListener('change', syncKanbanColumns);
            window.addEventListener('resize', syncKanbanColumns);
            syncKanbanColumns();
        }
    });
</script>
@endpush
@endsection
