@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Staff</li>
                </ol>
            </nav>
            <h1 class="page-title">Staff Management</h1>
            <p class="page-subtitle">Manage employees, profiles, and employment information.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('staff.export')
                <a href="{{ route('hr.staff.export', request()->query()) }}" class="btn btn-soft-success" title="Export Excel">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export
                </a>
                <a href="{{ route('hr.staff.export-pdf', request()->query()) }}" class="btn btn-soft-danger" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
            @endcan
            @can('staff.create')
                <a href="{{ route('hr.staff.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add Staff
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Total Staff" :value="$stats['total']" icon="bi-people" variant="primary" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Active" :value="$stats['active']" icon="bi-person-check" variant="success" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="On Leave" :value="$stats['on_leave']" icon="bi-person-walking" variant="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Terminated" :value="$stats['terminated']" icon="bi-person-x" variant="danger" />
        </div>
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.staff.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Search</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Name, employee ID, email, phone...">
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
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['department_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Designation</label>
                <select name="designation" class="form-select">
                    <option value="">All</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation }}" @selected(($filters['designation'] ?? '') === $designation)>{{ $designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Employment</label>
                <select name="employment_type" class="form-select">
                    <option value="">All</option>
                    @foreach ($employmentTypes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['employment_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-soft-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('hr.staff.index') }}" class="btn btn-soft-secondary flex-grow-1" title="Reset all filters"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
            </div>
        </form>

        @if (empty($filters['trashed']))
            <div class="mt-2">
                <a href="{{ route('hr.staff.index', array_merge(request()->except('trashed'), ['trashed' => 1])) }}" class="text-muted small text-decoration-none">
                    <i class="bi bi-trash me-1"></i>View trashed staff
                </a>
            </div>
        @else
            <div class="mt-2">
                <a href="{{ route('hr.staff.index', request()->except('trashed')) }}" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Back to active staff
                </a>
            </div>
        @endif
    </x-card>

    @if ($employees->count())
        <div class="d-md-none">
            @foreach ($employees as $employee)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ user_avatar($employee) }}" alt="{{ $employee->name }}" class="rounded-circle" style="width:44px;height:44px;object-fit:cover">
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('hr.staff.show', $employee) }}" class="fw-600 text-decoration-none text-truncate d-block">{{ $employee->name }}</a>
                                <div class="fs-xs text-muted text-truncate">{{ $employee->employee_id }} · {{ $employee->designation ?? '—' }}</div>
                            </div>
                            <span class="badge badge-soft-{{ $employee->employee_status?->color() ?? 'secondary' }} flex-shrink-0">{{ $employee->employee_status?->label() ?? '—' }}</span>
                        </div>
                        <div class="row g-2 mt-2 fs-xs text-muted">
                            <div class="col-12 col-sm-6 text-break"><i class="bi bi-envelope me-1"></i>{{ $employee->email }}</div>
                            <div class="col-6"><i class="bi bi-building me-1"></i>{{ $employee->department?->name ?? '—' }}</div>
                            <div class="col-6"><i class="bi bi-telephone me-1"></i>{{ $employee->phone ?? '—' }}</div>
                            <div class="col-12 col-sm-6"><i class="bi bi-calendar3 me-1"></i>Joined {{ $employee->joining_date ? format_date($employee->joining_date) : '—' }}</div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-1 mt-2 pt-2 border-top">
                            <a href="{{ route('hr.staff.show', $employee) }}" class="btn btn-sm btn-soft-primary" title="View"><i class="bi bi-eye me-1"></i>View</a>
                            @if (! $employee->trashed())
                                @can('staff.edit')
                                    <a href="{{ route('hr.staff.edit', $employee) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('staff.delete')
                                    <form method="POST" action="{{ route('hr.staff.destroy', $employee) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete staff?" data-confirm-text="This staff member will be moved to trash." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            @else
                                <form method="POST" action="{{ route('hr.staff.restore', $employee->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-soft-primary" data-confirm data-confirm-title="Restore staff?" data-confirm-text="This staff member will be restored." data-confirm-confirm-text="Yes, restore" data-confirm-danger="false">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-cards-lg">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Employment</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td data-label="Employee">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ user_avatar($employee) }}" alt="{{ $employee->name }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover">
                                        <div class="min-w-0">
                                            <a href="{{ route('hr.staff.show', $employee) }}" class="fw-600 text-decoration-none text-truncate d-block">{{ $employee->name }}</a>
                                            <div class="fs-xs text-muted text-break">{{ $employee->employee_id }} · {{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Department">{{ $employee->department?->name ?? '—' }}</td>
                                <td data-label="Designation">{{ $employee->designation ?? '—' }}</td>
                                <td data-label="Employment">{{ $employee->employment_type?->label() ?? '—' }}</td>
                                <td data-label="Shift">{{ $employee->shift?->name ?? '—' }}</td>
                                <td data-label="Status">
                                    <span class="badge badge-soft-{{ $employee->employee_status?->color() ?? 'secondary' }}">{{ $employee->employee_status?->label() ?? '—' }}</span>
                                </td>
                                <td data-label="Joined">{{ $employee->joining_date ? format_date($employee->joining_date) : '—' }}</td>
                                <td data-label="" class="text-end fin-cell-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('hr.staff.show', $employee) }}" class="btn btn-sm btn-soft-primary" title="View"><i class="bi bi-eye"></i></a>
                                        @if (! $employee->trashed())
                                            @can('staff.edit')
                                                <a href="{{ route('hr.staff.edit', $employee) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                            @endcan
                                            @can('staff.delete')
                                                <form method="POST" action="{{ route('hr.staff.destroy', $employee) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete staff?" data-confirm-text="This staff member will be moved to trash." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        @else
                                            <form method="POST" action="{{ route('hr.staff.restore', $employee->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-soft-primary" data-confirm data-confirm-title="Restore staff?" data-confirm-text="This staff member will be restored." data-confirm-confirm-text="Yes, restore" data-confirm-danger="false"><i class="bi bi-arrow-counterclockwise"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $employees->links() }}
        </div>
    @else
        <x-empty-state
            title="{{ ! empty($filters['trashed']) ? 'No trashed staff' : 'No staff members found' }}"
            text="Try adjusting your search or filters."
            :action="! empty($filters['trashed']) ? null : route('hr.staff.create')"
            action-text="Add Staff"
        />
    @endif
@endsection
