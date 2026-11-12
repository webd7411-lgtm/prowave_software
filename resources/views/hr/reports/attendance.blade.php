@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance Report</li>
                </ol>
            </nav>
            <h1 class="page-title">Attendance Report</h1>
            <p class="page-subtitle">{{ $records->count() }} record(s) found.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Present/Late" :value="$summary['present']" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Late" :value="$summary['late']" icon="bi-alarm" variant="warning" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Absent" :value="$summary['absent']" icon="bi-x-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="On Leave" :value="$summary['leave']" icon="bi-person-walking" variant="info" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Total Records" :value="$summary['total']" icon="bi-list-check" variant="primary" />
        </div>
    </div>

    <x-card body-class="p-3 print-hide">
        <form method="GET" action="{{ route('hr.reports.attendance') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
            @if ($filters)
                <div class="col-12">
                    <a href="{{ route('hr.reports.attendance') }}" class="text-muted small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Clear filters</a>
                </div>
            @endif
        </form>
    </x-card>

    @if ($records->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0 table-cards-lg">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Work Hours</th>
                            <th>Late</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td data-label="Date" class="fs-sm">{{ format_date($record->date) }}</td>
                                <td data-label="Employee" class="fw-500">{{ $record->employee?->name }}</td>
                                <td data-label="Check In" class="fs-sm">{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                <td data-label="Check Out" class="fs-sm">{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                <td data-label="Work Hours" class="fs-sm">{{ $record->total_work_minutes !== null ? intdiv($record->total_work_minutes, 60).'h '.($record->total_work_minutes % 60).'m' : '—' }}</td>
                                <td data-label="Late" class="fs-sm">{{ $record->late_minutes ? $record->late_minutes.' min' : '—' }}</td>
                                <td data-label="Overtime" class="fs-sm">{{ $record->overtime_minutes ? $record->overtime_minutes.' min' : '—' }}</td>
                                <td data-label="Status"><span class="badge badge-soft-{{ $record->status?->color() ?? 'secondary' }}">{{ $record->status?->label() ?? '—' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state title="No attendance records found" text="Adjust your filters to see results." />
    @endif
@endsection