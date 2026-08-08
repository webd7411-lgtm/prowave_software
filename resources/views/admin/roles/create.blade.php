@extends('layouts.app')

@section('title', 'Add Role')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Role</li>
                </ol>
            </nav>
            <h1 class="page-title">Create Role</h1>
            <p class="page-subtitle">Name the role and assign its permissions.</p>
        </div>
    </div>

    <x-card title="New Role" subtitle="Permissions can be changed later" icon="bi-shield-plus" body-class="p-4">
        <x-form :action="route('admin.roles.store')" method="POST" spinner="Creating..." cancel-url="{{ route('admin.roles.index') }}">
            <div class="row mb-3">
                <div class="col-md-6">
                    <x-form.input label="Role Name" name="name" :required="true" placeholder="e.g. Content Editor" />
                </div>
            </div>

            <div class="fw-600 mb-2">
                <i class="bi bi-shield-check me-1"></i>Permissions
            </div>
            @include('admin.partials.permission-matrix', [
                'permissionGroups' => $permissionGroups,
                'selected' => [],
                'fieldName' => 'permissions',
            ])
        </x-form>
    </x-card>
@endsection
