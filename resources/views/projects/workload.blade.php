@extends('layouts.app')

@section('title', 'Employee Workload Overview')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Employee Workload Overview</h1>
            <p class="text-muted mb-0 fs-sm">Monitor employee task distribution, pending workload, and resource utilization.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
    </div>

    <div class="row g-3">
        @forelse($users as $user)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 hover-shadow transition">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                <div class="rounded-circle bg-primary text-white fs-5 fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h5 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h5>
                                    <span class="fs-xs text-muted text-break">{{ $user->email }}</span>
                                </div>
                            </div>
                            <span class="badge {{ $user->workload_badge }} fs-xs px-2 py-1 flex-shrink-0">{{ $user->workload_status }}</span>
                        </div>

                        {{-- Progress bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between fs-xs mb-1">
                                <span class="text-muted fw-semibold">Load Level</span>
                                <span class="fw-bold text-dark">{{ $user->workload_percentage }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $user->workload_status === 'Overloaded' ? 'bg-danger' : ($user->workload_status === 'Heavy' ? 'bg-warning' : 'bg-primary') }}" role="progressbar" style="width: {{ $user->workload_percentage }}%;"></div>
                            </div>
                        </div>

                        {{-- Task Stats Grid --}}
                        <div class="row g-2 text-center bg-light rounded-3 p-2 mb-3 fs-xs">
                            <div class="col-6 col-sm-3">
                                <div class="text-muted fw-semibold">Assigned</div>
                                <div class="fw-bold text-dark fs-6">{{ $user->total_tasks_count }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted fw-semibold">In Progress</div>
                                <div class="fw-bold text-primary fs-6">{{ $user->in_progress_count }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted fw-semibold">Done</div>
                                <div class="fw-bold text-success fs-6">{{ $user->completed_count }}</div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="text-muted fw-semibold">Overdue</div>
                                <div class="fw-bold text-danger fs-6">{{ $user->overdue_count }}</div>
                            </div>
                        </div>

                        {{-- Hours Allocation --}}
                        <div class="d-flex justify-content-between align-items-center fs-xs text-muted pt-2 border-top">
                            <span><i class="bi bi-clock me-1"></i>Est: <strong>{{ number_format($user->total_estimated_hours, 1) }}h</strong></span>
                            <span><i class="bi bi-stopwatch me-1"></i>Actual: <strong>{{ number_format($user->total_actual_hours, 1) }}h</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                No employee workload records found.
            </div>
        @endforelse
    </div>
</div>
@endsection
