@extends('layouts.app')

@section('title', 'Designations')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Designations</li>
                </ol>
            </nav>
            <h1 class="page-title">Designation Management</h1>
            <p class="page-subtitle">Job roles and their department mapping.</p>
        </div>
        @can('designations.create')
            <a href="{{ route('hr.designations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Designation
            </a>
        @endcan
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.designations.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label fs-xs text-muted mb-1">Search</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search by designation name...">
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
                    <option value="1" @selected(($filters['status'] ?? '') === '1')>Active</option>
                    <option value="0" @selected(($filters['status'] ?? '') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex justify-content-end">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </x-card>

    @if ($designations->count())
        <div class="d-md-none">
            @foreach ($designations as $designation)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-primary fs-sm">{{ $designation->name }}</span>
                            <span class="badge badge-soft-{{ $designation->is_active ? 'success' : 'secondary' }} flex-shrink-0">{{ $designation->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="fs-xs text-muted mt-2">
                            <div><i class="bi bi-building me-1"></i>{{ $designation->department?->name ?? '—' }}</div>
                            <div><i class="bi bi-people me-1"></i>{{ $designation->users_count }} employee(s)</div>
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
                            <th>Designation</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($designations as $designation)
                            <tr>
                                <td class="fw-600">{{ $designation->name }}</td>
                                <td><span class="badge badge-soft-secondary font-monospace">{{ $designation->code ?? '—' }}</span></td>
                                <td>{{ $designation->department?->name ?? '—' }}</td>
                                <td>{{ $designation->users_count }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $designation->is_active ? 'success' : 'secondary' }}">{{ $designation->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        @can('designations.edit')
                                            <a href="{{ route('hr.designations.edit', $designation) }}" class="btn btn-sm btn-soft-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('designations.delete')
                                            <form method="POST" action="{{ route('hr.designations.destroy', $designation) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-soft-danger" data-confirm data-confirm-title="Delete designation?" data-confirm-text="This designation will be permanently deleted." data-confirm-confirm-text="Yes, delete"><i class="bi bi-trash"></i></button>
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
            {{ $designations->links() }}
        </div>
    @else
        <x-empty-state title="No designations found" text="Create your first designation to get started." :action="auth()->user()->can('designations.create') ? route('hr.designations.create') : null" action-text="Add Designation" />
    @endif
@endsection
