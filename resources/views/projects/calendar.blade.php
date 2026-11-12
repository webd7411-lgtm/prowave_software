@extends('layouts.app')

@section('title', 'Project Calendar')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Project & Task Calendar</h1>
            <p class="text-muted mb-0 fs-sm">Visual schedule of task deadlines and delivery targets.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Projects
            </a>
            <form method="GET" action="{{ route('projects.calendar') }}" class="d-flex align-items-center gap-2 mb-0 flex-grow-1 flex-sm-grow-0">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Calendar Events List Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-calendar3 text-primary me-2"></i>Upcoming Deadlines & Delivery Schedule</h5>
            <span class="badge bg-primary-subtle text-primary fs-xs">{{ count($events) }} Scheduled Items</span>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-xs text-uppercase text-muted">
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-sm">
                        @forelse($events as $evt)
                            <tr>
                                <td data-label="Type">
                                    @if($evt['type'] === 'Task')
                                        <span class="badge bg-primary-subtle text-primary fs-xs"><i class="bi bi-check2-square me-1"></i>Task</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success fs-xs"><i class="bi bi-folder-symlink me-1"></i>Project</span>
                                    @endif
                                </td>
                                <td data-label="Title" class="fw-bold text-dark">{{ $evt['title'] }}</td>
                                <td data-label="Project"><span class="text-secondary fw-semibold">{{ $evt['project_name'] }}</span></td>
                                <td data-label="Target Date" class="fw-semibold text-primary"><i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($evt['start'])->format('M d, Y') }}</td>
                                <td data-label="Status"><span class="badge bg-light text-dark border fs-xs">{{ $evt['status'] }}</span></td>
                                <td data-label="" class="text-end fin-cell-actions">
                                    <a href="{{ $evt['url'] }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i> Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No scheduled deadlines found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
