@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                </ol>
            </nav>
            <h1 class="page-title">Attendance Records</h1>
            <p class="page-subtitle">View and manage daily attendance.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('attendance.view')
                <a href="{{ route('hr.attendance.dashboard') }}" class="btn btn-soft-primary">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Present" :value="$summary['present']" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Late" :value="$summary['late']" icon="bi-alarm" variant="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Absent" :value="$summary['absent']" icon="bi-x-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="On Leave" :value="$summary['on_leave']" icon="bi-person-walking" variant="info" />
        </div>
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.attendance.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Employee</label>
                <select name="employee_id" class="form-select select2" data-placeholder="All employees">
                    <option value="">All employees</option>
                    @foreach ($employees as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['employee_id'] ?? '') == $id)>{{ $name }}</option>
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
                <label class="form-label fs-xs text-muted mb-1">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-md-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </x-card>

    @if ($attendance->count())
        <div class="d-md-none">
            @foreach ($attendance as $record)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-600 flex-grow-1">{{ $record->employee?->name }}</span>
                            <span class="badge badge-soft-{{ $record->status->color() }}">{{ $record->status->label() }}</span>
                        </div>
                        <div class="fs-xs text-muted mt-2">
                            <div class="row g-1">
                                <div class="col-6"><i class="bi bi-calendar3 me-1"></i>{{ format_date($record->date) }}</div>
                                <div class="col-6"><i class="bi bi-box-arrow-in-right me-1"></i>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</div>
                                <div class="col-6"><i class="bi bi-box-arrow-right me-1"></i>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</div>
                                <div class="col-6"><i class="bi bi-stopwatch me-1"></i>{{ $record->total_work_minutes !== null ? intdiv($record->total_work_minutes, 60).'h '.($record->total_work_minutes % 60).'m' : '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Work Hours</th>
                            <th>Late</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendance as $record)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ user_avatar($record->employee) }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                                        <div class="min-w-0">
                                            <div class="fw-500 text-truncate">{{ $record->employee?->name }}</div>
                                            <div class="fs-xs text-muted">{{ $record->employee?->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ format_date($record->date) }}</td>
                                <td>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                <td>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                <td>{{ $record->total_work_minutes !== null ? intdiv($record->total_work_minutes, 60).'h '.($record->total_work_minutes % 60).'m' : '—' }}</td>
                                <td>{{ $record->late_minutes ? $record->late_minutes.' min' : '—' }}</td>
                                <td>{{ $record->overtime_minutes ? $record->overtime_minutes.' min' : '—' }}</td>
                                <td><span class="badge badge-soft-{{ $record->status->color() }}">{{ $record->status->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $attendance->links() }}
        </div>
    @else
        <x-empty-state title="No attendance records" text="Records will appear here once employees check in." />
    @endif
@endsection
