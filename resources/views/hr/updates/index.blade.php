@extends('layouts.app')

@section('title', 'Daily Work Updates')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Daily Work Updates</li>
                </ol>
            </nav>
            <h1 class="page-title">Daily Work Updates</h1>
            <p class="page-subtitle">Track daily reports submitted by employees.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('daily_reports.create')
                <a href="{{ route('hr.updates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>New Update
                </a>
            @endcan
        </div>
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.updates.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
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
                <label class="form-label fs-xs text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-12 col-md-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
        <div class="mt-2">
            <a href="{{ route('hr.updates.index', array_merge(request()->except('my'), ['my' => 1])) }}" class="text-muted small text-decoration-none {{ ! empty($filters['my']) ? 'fw-600' : '' }}">
                <i class="bi bi-person-check me-1"></i>Only my updates
            </a>
        </div>
    </x-card>

    @if ($updates->count())
        <div class="d-md-none">
            @foreach ($updates as $update)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ user_avatar($update->employee) }}" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-600 text-truncate">{{ $update->employee?->name }}</div>
                                <div class="fs-xs text-muted">{{ format_date($update->date) }} · {{ $update->hours_worked }} hr(s)</div>
                            </div>
                            <span class="badge badge-soft-{{ $update->status->color() }}">{{ $update->status->label() }}</span>
                        </div>
                        <a href="{{ route('hr.updates.show', $update) }}" class="btn btn-sm btn-soft-primary mt-2 w-100"><i class="bi bi-eye me-1"></i>View</a>
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
                            <th>Work Summary</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($updates as $update)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ user_avatar($update->employee) }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                                        <div class="min-w-0">
                                            <div class="fw-500 text-truncate">{{ $update->employee?->name }}</div>
                                            <div class="fs-xs text-muted">{{ $update->employee?->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ format_date($update->date) }}</td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width:280px" title="{{ $update->work_description }}">{{ $update->work_description ?: '—' }}</span>
                                </td>
                                <td>{{ $update->hours_worked ?: '—' }}</td>
                                <td><span class="badge badge-soft-{{ $update->status->color() }}">{{ $update->status->label() }}</span></td>
                                <td class="fs-sm text-muted">{{ $update->reviewer?->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('hr.updates.show', $update) }}" class="btn btn-sm btn-soft-primary" title="View"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $updates->links() }}
        </div>
    @else
        <x-empty-state
            title="No daily updates found"
            text="Try adjusting your filters or submit a new update."
            :action="auth()->user()->can('daily_reports.create') ? route('hr.updates.create') : null"
            action-text="New Update"
        />
    @endif
@endsection