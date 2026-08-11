@extends('layouts.app')

@section('title', 'Leave Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Leave Report</li>
                </ol>
            </nav>
            <h1 class="page-title">Leave Report</h1>
            <p class="page-subtitle">{{ $leaves->count() }} leave record(s) found.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <x-card body-class="p-3 print-hide">
        <form method="GET" action="{{ route('hr.reports.leaves') }}" class="row g-2 align-items-end">
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
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach (App\Enums\LeaveStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
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
                    <a href="{{ route('hr.reports.leaves') }}" class="text-muted small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Clear filters</a>
                </div>
            @endif
        </form>
    </x-card>

    @if ($leaves->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaves as $leave)
                            <tr>
                                <td class="fw-500">{{ $leave->employee?->name }}</td>
                                <td>{{ $leave->leaveType?->name ?? '—' }}</td>
                                <td class="fs-sm">{{ format_date($leave->start_date) }}</td>
                                <td class="fs-sm">{{ format_date($leave->end_date) }}</td>
                                <td>{{ $leave->days }}</td>
                                <td class="fs-sm">
                                    <span class="text-truncate d-inline-block" style="max-width:220px" title="{{ $leave->reason }}">{{ $leave->reason ?: '—' }}</span>
                                </td>
                                <td><span class="badge badge-soft-{{ $leave->status->color() }}">{{ $leave->status->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state title="No leave records found" text="Adjust your filters to see results." />
    @endif
@endsection