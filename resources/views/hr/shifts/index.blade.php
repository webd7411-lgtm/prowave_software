@extends('layouts.app')

@section('title', 'Work Shifts')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Work Shifts</li>
                </ol>
            </nav>
            <h1 class="page-title">Shift Management</h1>
            <p class="page-subtitle">Define work schedules and attendance rules.</p>
        </div>
        @can('shifts.create')
            <a href="{{ route('hr.shifts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Shift
            </a>
        @endcan
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.shifts.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <label class="form-label fs-xs text-muted mb-1">Search</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by shift name...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(($filters['status'] ?? '') === '1')>Active</option>
                    <option value="0" @selected(($filters['status'] ?? '') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex justify-content-end">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </x-card>

    @if ($shifts->count())
        <div class="d-md-none">
            @foreach ($shifts as $shift)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-600 flex-grow-1">{{ $shift->name }}</span>
                            <span class="badge badge-soft-{{ $shift->is_active ? 'success' : 'secondary' }}">{{ $shift->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="fs-xs text-muted mt-2">
                            <div><i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Carbon::parse($shift->start_time)->format('h:i A') }} — {{ \Illuminate\Support\Carbon::parse($shift->end_time)->format('h:i A') }}</div>
                            <div><i class="bi bi-people me-1"></i>{{ $shift->employees_count }} employee(s) · Grace {{ $shift->grace_period }}m · Break {{ $shift->break_minutes }}m</div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-1 mt-2 pt-2 border-top">
                            @can('shifts.edit')
                                <a href="{{ route('hr.shifts.edit', $shift) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil me-1"></i>Edit</a>
                            @endcan
                            @can('shifts.delete')
                                <form method="POST" action="{{ route('hr.shifts.destroy', $shift) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete shift?" data-confirm-text="This shift will be permanently deleted." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
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
                            <th>Shift</th>
                            <th>Code</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Grace</th>
                            <th>Break</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shifts as $shift)
                            <tr>
                                <td data-label="Shift" class="fw-600">{{ $shift->name }}</td>
                                <td data-label="Code"><span class="badge badge-soft-secondary font-monospace">{{ $shift->code ?? '—' }}</span></td>
                                <td data-label="Start Time">{{ \Illuminate\Support\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                                <td data-label="End Time">{{ \Illuminate\Support\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                <td data-label="Grace">{{ $shift->grace_period }} min</td>
                                <td data-label="Break">{{ $shift->break_minutes }} min</td>
                                <td data-label="Employees">{{ $shift->employees_count }}</td>
                                <td data-label="Status">
                                    <span class="badge badge-soft-{{ $shift->is_active ? 'success' : 'secondary' }}">{{ $shift->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td data-label="" class="text-end fin-cell-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        @can('shifts.edit')
                                            <a href="{{ route('hr.shifts.edit', $shift) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('shifts.delete')
                                            <form method="POST" action="{{ route('hr.shifts.destroy', $shift) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete shift?" data-confirm-text="This shift will be permanently deleted." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $shifts->links() }}
        </div>
    @else
        <x-empty-state title="No shifts found" text="Create your first work shift to get started." :action="auth()->user()->can('shifts.create') ? route('hr.shifts.create') : null" action-text="Add Shift" />
    @endif
@endsection
