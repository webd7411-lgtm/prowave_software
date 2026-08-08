@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
            <h1 class="page-title">Role Management</h1>
            <p class="page-subtitle">Define roles and control what each can access.</p>
        </div>
        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Role
            </a>
        @endcan
    </div>

    <x-card body-class="p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-dt-order-col="0">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>Created</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-fallback sm">
                                        <i class="bi {{ $role->name === 'Super Admin' ? 'bi-shield-fill' : ($role->name === 'Client' ? 'bi-person' : 'bi-person-badge') }}"></i>
                                    </span>
                                    <div>
                                        <div class="fw-500">{{ $role->name }}</div>
                                        <div class="fs-xs text-muted">guard: web</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">{{ $role->permissions_count ?? $role->permissions->count() }} permissions</span>
                            </td>
                            <td>{{ $role->users_count ?? $role->users()->count() }}</td>
                            <td>{{ format_date($role->created_at) }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-1">
                                    @can('roles.edit')
                                        @if ($role->name !== 'Super Admin')
                                            <a class="btn btn-icon btn-soft-primary btn-sm" href="{{ route('admin.roles.edit', $role) }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('roles.delete')
                                        @if (! in_array($role->name, ['Super Admin', 'Administrator']) && $role->users()->count() === 0)
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-icon btn-soft-danger btn-sm" data-confirm data-confirm-title="Delete role?" data-confirm-text="This role and its permissions will be removed." data-confirm-confirm-text="Yes, delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
