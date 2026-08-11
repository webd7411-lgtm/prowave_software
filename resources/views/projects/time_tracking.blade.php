@extends('layouts.app')

@section('title', 'Time Tracking')

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Time Tracking & Stopwatch</h1>
            <p class="text-muted mb-0 fs-sm">Track actual billable and development time for software projects and tasks.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Projects
            </a>
            @can('time_tracking.create')
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#globalManualTimeModal">
                    <i class="bi bi-plus-lg me-1"></i> Log Manual Time
                </button>
            @endcan
        </div>
    </div>

    {{-- Live Stopwatch Card --}}
    @can('time_tracking.create')
        <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-lg-7">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-stopwatch text-primary me-2"></i>Live Timer Stopwatch</h5>
                        
                        @if($activeTimer)
                            <form action="{{ route('projects.time.stop', $activeTimer) }}" method="POST" class="row g-2 align-items-center">
                                @csrf
                                <div class="col-12 col-md-8">
                                    <div class="p-3 bg-primary-subtle text-primary rounded-3">
                                        <div class="fw-bold fs-6">{{ $activeTimer->project->name }}</div>
                                        <div class="fs-xs text-muted">{{ $activeTimer->task?->title ?? 'General Work' }} — {{ $activeTimer->description }}</div>
                                        <div class="mt-1 font-monospace fw-bold fs-4 text-dark" id="timerCounter">00:00:00</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-danger w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-stop-circle fs-5"></i> Stop Timer
                                    </button>
                                </div>
                            </form>
                        @else
                            <form action="{{ route('projects.time.start') }}" method="POST" class="row g-2 align-items-center">
                                @csrf
                                <div class="col-12 col-md-5">
                                    <select name="project_id" class="form-select" required>
                                        <option value="">-- Select Project --</option>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <input type="text" name="description" class="form-control" placeholder="What are you working on?" required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-success w-100 fw-bold d-flex align-items-center justify-content-center gap-1 py-2">
                                        <i class="bi bi-play-circle"></i> Start Timer
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    {{-- Stats Summary --}}
                    <div class="col-12 col-lg-5 border-start-lg pt-3 pt-lg-0">
                        <div class="row g-2 text-center">
                            <div class="col-6 col-sm-3 col-lg-6">
                                <div class="p-2 rounded bg-light">
                                    <div class="fs-2xs text-muted text-uppercase fw-bold">Today</div>
                                    <div class="fw-bold text-dark fs-6">{{ round($todayMinutes / 60, 1) }} hrs</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 col-lg-6">
                                <div class="p-2 rounded bg-light">
                                    <div class="fs-2xs text-muted text-uppercase fw-bold">This Week</div>
                                    <div class="fw-bold text-primary fs-6">{{ round($weekMinutes / 60, 1) }} hrs</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 col-lg-6 mt-2">
                                <div class="p-2 rounded bg-light">
                                    <div class="fs-2xs text-muted text-uppercase fw-bold">This Month</div>
                                    <div class="fw-bold text-info fs-6">{{ round($monthMinutes / 60, 1) }} hrs</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 col-lg-6 mt-2">
                                <div class="p-2 rounded bg-light">
                                    <div class="fs-2xs text-muted text-uppercase fw-bold">Total Logged</div>
                                    <div class="fw-bold text-success fs-6">{{ round($totalMinutes / 60, 1) }} hrs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- History Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>Time Log History</h5>
            <form method="GET" action="{{ route('projects.time.index') }}" class="d-flex align-items-center gap-2">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light fs-xs text-uppercase text-muted">
                    <tr>
                        <th>User</th>
                        <th>Project</th>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Date / Time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody class="fs-sm">
                    @forelse($timeEntries as $entry)
                        <tr>
                            <td class="fw-semibold text-dark">{{ $entry->user?->name }}</td>
                            <td><span class="fw-semibold text-primary">{{ $entry->project->name }}</span></td>
                            <td>{{ $entry->task?->title ?? 'General Work' }}</td>
                            <td class="text-muted">{{ $entry->description }}</td>
                            <td>{{ $entry->start_time ? $entry->start_time->format('M d, Y H:i') : $entry->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($entry->is_running)
                                    <span class="badge bg-warning text-dark fw-bold animate-pulse">RUNNING...</span>
                                @else
                                    <span class="badge bg-info-subtle text-info fw-bold fs-xs">{{ $entry->formatted_duration }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No time entries recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($timeEntries->hasPages())
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-end">
                    {{ $timeEntries->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Global Manual Time Log Modal --}}
<div class="modal fade" id="globalManualTimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('projects.time.manual') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock text-primary me-2"></i>Log Manual Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-sm">Project <span class="text-danger">*</span></label>
                    <select name="project_id" class="form-select" required>
                        <option value="">-- Select Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
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
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Log</button>
            </div>
        </form>
    </div>
</div>

@if($activeTimer)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startTime = new Date("{{ $activeTimer->start_time->toIso8601String() }}").getTime();
        const timerElement = document.getElementById('timerCounter');

        function updateCounter() {
            const now = new Date().getTime();
            const diff = Math.max(0, now - startTime);

            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            if (timerElement) {
                timerElement.innerText = 
                    (hours < 10 ? "0" + hours : hours) + ":" +
                    (minutes < 10 ? "0" + minutes : minutes) + ":" +
                    (seconds < 10 ? "0" + seconds : seconds);
            }
        }

        setInterval(updateCounter, 1000);
        updateCounter();
    });
</script>
@endpush
@endif
@endsection
