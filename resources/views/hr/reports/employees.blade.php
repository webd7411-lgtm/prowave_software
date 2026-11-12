@extends('layouts.app')

@section('title', 'Employee Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Employee Report</li>
                </ol>
            </nav>
            <h1 class="page-title">Employee Report</h1>
            <p class="page-subtitle">{{ $employees->count() }} employee(s) found.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <x-card body-class="p-3 print-hide">
        <form method="GET" action="{{ route('hr.reports.employees') }}" class="row g-2 align-items-end">
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
                <label class="form-label fs-xs text-muted mb-1">Joining From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Joining To</label>
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
            @if ($filters)
                <div class="col-12">
                    <a href="{{ route('hr.reports.employees') }}" class="text-muted small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Clear filters</a>
                </div>
            @endif
        </form>
    </x-card>

    @if ($employees->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0 table-cards-lg">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Shift</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td data-label="Employee" class="fw-500">{{ $employee->name }}</td>
                                <td data-label="Employee ID">{{ $employee->employee_id }}</td>
                                <td data-label="Department">{{ $employee->department?->name ?? '—' }}</td>
                                <td data-label="Designation">{{ $employee->designation ?? '—' }}</td>
                                <td data-label="Shift">{{ $employee->shift?->name ?? '—' }}</td>
                                <td data-label="Email" class="fs-sm text-break">{{ $employee->email }}</td>
                                <td data-label="Status"><span class="badge badge-soft-{{ $employee->employee_status?->color() ?? 'secondary' }}">{{ $employee->employee_status?->label() ?? '—' }}</span></td>
                                <td data-label="Joined" class="fs-sm">{{ $employee->joining_date ? format_date($employee->joining_date) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state title="No employees found" text="Adjust your filters to see results." />
    @endif
@endsection