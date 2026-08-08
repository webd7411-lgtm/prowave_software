@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
            <h1 class="page-title">Permission Directory</h1>
            <p class="page-subtitle">Browse every permission and which roles hold it.</p>
        </div>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.permissions.index') }}">
        <div class="col-12 col-md-4">
            <div class="position-relative">
                <i class="bi bi-search position-absolute" style="left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--pw-text-muted); pointer-events: none; z-index: 2;"></i>
                <input type="search" class="form-control ps-5" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search permission...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="role">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
        </div>
    </form>

    <div class="row g-3">
        @foreach ($permissions as $group => $permissionsInGroup)
            <div class="col-12 col-lg-6 col-xxl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-grid text-primary"></i> {{ $group }}
                            <span class="badge badge-soft-secondary ms-auto">{{ $permissionsInGroup->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body py-2">
                        @foreach ($permissionsInGroup as $permission)
                            <div class="d-flex align-items-start justify-content-between gap-2 py-2 border-bottom border-light-subtle">
                                <div class="min-w-0">
                                    <div class="fw-500 font-monospace fs-sm">{{ $permission->name }}</div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    @if ($selectedRole)
                                        @if ($permission->roles->contains(fn ($r) => $r->name === $selectedRole))
                                            <span class="badge badge-soft-success">Assigned</span>
                                        @else
                                            <span class="badge badge-soft-secondary">Not assigned</span>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-primary">{{ $permission->roles->count() }} role(s)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($permissions->isEmpty())
        <x-empty-state title="No permissions found" text="Try adjusting your search." icon="bi-shield-exclamation" />
    @endif
@endsection
