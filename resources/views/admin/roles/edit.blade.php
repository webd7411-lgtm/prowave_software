@extends('layouts.app')

@section('title', 'Edit Role — '.$role->name)

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $role->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Role</h1>
            <p class="page-subtitle">Update the role name and its permissions.</p>
        </div>
    </div>

    <x-card title="Edit Role" subtitle="Modify name and permission matrix" icon="bi-shield-lock" body-class="p-4">
        <x-form :action="route('admin.roles.update', $role)" method="PATCH" spinner="Saving..." cancel-url="{{ route('admin.roles.index') }}">
            <div class="row mb-3">
                <div class="col-md-6">
                    <x-form.input label="Role Name" name="name" :value="$role->name" :required="true" />
                </div>
            </div>

            <div class="fw-600 mb-2">
                <i class="bi bi-shield-check me-1"></i>Permissions
            </div>
            @include('admin.partials.permission-matrix', [
                'permissionGroups' => $permissionGroups,
                'selected' => $role->permissions->pluck('id')->all(),
                'fieldName' => 'permissions',
            ])
        </x-form>
    </x-card>
@endsection
