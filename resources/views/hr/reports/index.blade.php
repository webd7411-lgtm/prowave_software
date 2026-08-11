@extends('layouts.app')

@section('title', 'HR Reports')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                </ol>
            </nav>
            <h1 class="page-title">HR Reports</h1>
            <p class="page-subtitle">Generate and export workforce reports.</p>
        </div>
    </div>

    @if ($departments->isEmpty())
        <div class="alert alert-soft-warning d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle"></i>
            <span>No departments configured yet. Reports will be limited until departments are added.</span>
        </div>
    @endif

    <div class="row g-3">
        @foreach ([
            ['route' => 'hr.reports.employees', 'icon' => 'bi-people', 'title' => 'Employee Report', 'text' => 'All employees with department, shift, and joining details.'],
            ['route' => 'hr.reports.attendance', 'icon' => 'bi-calendar-check', 'title' => 'Attendance Report', 'text' => 'Daily attendance records with status and work hours.'],
            ['route' => 'hr.reports.leaves', 'icon' => 'bi-calendar-x', 'title' => 'Leave Report', 'text' => 'Leave requests with type, duration, and status.'],
            ['route' => 'hr.reports.tasks', 'icon' => 'bi-list-task', 'title' => 'Task Report', 'text' => 'Task assignment, progress, and completion summary.'],
            ['route' => 'hr.reports.updates', 'icon' => 'bi-journal-text', 'title' => 'Daily Updates Report', 'text' => 'Employee daily work update summaries.'],
        ] as $report)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route($report['route']) }}" class="text-decoration-none">
                    <div class="card h-100 report-card">
                        <div class="card-body d-flex gap-3">
                            <div class="report-icon">
                                <i class="bi {{ $report['icon'] }}"></i>
                            </div>
                            <div>
                                <h5 class="card-title">{{ $report['title'] }}</h5>
                                <p class="fs-sm text-muted mb-0">{{ $report['text'] }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection