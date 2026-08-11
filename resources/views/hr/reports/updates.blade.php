@extends('layouts.app')

@section('title', 'Daily Updates Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Daily Updates Report</li>
                </ol>
            </nav>
            <h1 class="page-title">Daily Updates Report</h1>
            <p class="page-subtitle">{{ $updates->count() }} update(s) found.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <x-card body-class="p-3 print-hide">
        <form method="GET" action="{{ route('hr.reports.updates') }}" class="row g-2 align-items-end">
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
                    <a href="{{ route('hr.reports.updates') }}" class="text-muted small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Clear filters</a>
                </div>
            @endif
        </form>
    </x-card>

    @if ($updates->count())
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Hours</th>
                            <th>Work Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($updates as $update)
                            <tr>
                                <td class="fs-sm">{{ format_date($update->date) }}</td>
                                <td class="fw-500">{{ $update->employee?->name }}</td>
                                <td>{{ $update->hours_worked ?: '—' }}</td>
                                <td class="fs-sm">
                                    <span class="text-truncate d-inline-block" style="max-width:320px" title="{{ $update->work_description }}">{{ $update->work_description ?: '—' }}</span>
                                </td>
                                <td><span class="badge badge-soft-{{ $update->status->color() }}">{{ $update->status->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state title="No daily updates found" text="Adjust your filters to see results." />
    @endif
@endsection