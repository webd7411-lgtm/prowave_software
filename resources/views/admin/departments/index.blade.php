@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Departments</li>
                </ol>
            </nav>
            <h1 class="page-title">Departments</h1>
            <p class="page-subtitle">Organize your teams by department.</p>
        </div>
        @can('departments.create')
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Department
            </a>
        @endcan
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.departments.index') }}" id="deptFilterForm">
        <div class="col-12 col-md-4">
            <div class="position-relative">
                <i class="bi bi-search position-absolute" style="left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--pw-text-muted); pointer-events: none; z-index: 2;"></i>
                <input type="search" class="form-control ps-5" name="search" id="dept-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search departments...">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-soft-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-soft-secondary" title="Reset Filter"><i class="bi bi-x-circle me-1"></i>Reset Filter</a>
        </div>
    </form>

    <x-card body-class="p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table dt-mobile-cards" data-dt-order-col="0" data-dt-options='{"responsive":false}'>
                <thead>
                    <tr>
                        <th data-priority="1">Department</th>
                        <th>Code</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="no-sort" data-priority="1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td data-label="Department">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-fallback sm"><i class="bi bi-building"></i></span>
                                    <div class="fw-500">{{ $department->name }}</div>
                                </div>
                            </td>
                            <td data-label="Code"><span class="badge badge-soft-secondary">{{ $department->code }}</span></td>
                            <td data-label="Members"><span class="badge badge-soft-primary">{{ $department->users_count }}</span></td>
                            <td data-label="Status">
                                @if ($department->is_active)
                                    <span class="badge badge-soft-success">Active</span>
                                @else
                                    <span class="badge badge-soft-secondary">Inactive</span>
                                @endif
                            </td>
                            <td data-label="Created">{{ format_date($department->created_at) }}</td>
                            <td data-label="">
                                <div class="d-flex justify-content-end gap-1">
                                    @can('departments.edit')
                                        <a class="btn btn-icon btn-soft-primary btn-sm" href="{{ route('admin.departments.edit', $department) }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('departments.delete')
                                        @if ($department->users_count === 0)
                                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-icon btn-soft-danger btn-sm" data-confirm data-confirm-title="Delete department?" data-confirm-text="This department will be permanently removed." data-confirm-confirm-text="Yes, delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No departments yet" text="Create your first department to organize users." icon="bi-building" :action="auth()->user()->can('departments.create') ? route('admin.departments.create') : null" action-text="Add Department" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $departments->links() }}
        </div>
    </x-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('dept-search-input');
            if (searchInput) {
                searchInput.addEventListener('search', function () {
                    if (!this.value) {
                        window.location.href = "{{ route('admin.departments.index') }}";
                    }
                });
            }
        });
    </script>
@endpush
