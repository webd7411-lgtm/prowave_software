@extends('layouts.app')

@section('title', 'Add Designation')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.designations.index') }}">Designations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Designation</li>
                </ol>
            </nav>
            <h1 class="page-title">Add Designation</h1>
            <p class="page-subtitle">Create a new job role.</p>
        </div>
    </div>

    <x-card title="Designation Information" icon="bi-patch-plus">
        <x-form action="{{ route('hr.designations.store') }}" submit="Save Designation" cancel-url="{{ route('hr.designations.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-form.input label="Name" name="name" required icon="bi-briefcase" placeholder="e.g. Software Developer" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Code" name="code" icon="bi-upc-scan" placeholder="e.g. DEV" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" placeholder="Select department" select2 />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select label="Default System Role" name="role_id" :options="$roles->pluck('name', 'id')" placeholder="Select default role" select2 />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.switch label="Active" name="is_active" checked />
                </div>
                <div class="col-12">
                    <x-form.input label="Description" name="description" type="textarea" rows="3" placeholder="Short description of the role..." />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection
