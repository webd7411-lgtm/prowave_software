@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Department</h1>
            <p class="page-subtitle">Update department details.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-card title="Edit Department" subtitle="Modify the department information" icon="bi-building-gear" body-class="p-4">
                <x-form :action="route('admin.departments.update', $department)" method="PATCH" spinner="Saving..." cancel-url="{{ route('admin.departments.index') }}">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <x-form.input label="Department Name" name="name" :value="$department->name" :required="true" />
                        </div>
                        <div class="col-md-5">
                            <x-form.input label="Code" name="code" :value="$department->code" :required="true" help="Short, unique identifier." />
                        </div>
                        <div class="col-12">
                            <x-form.input label="Description" name="description" type="textarea" rows="3" :value="$department->description" />
                        </div>
                        <div class="col-12">
                            <x-form.switch label="Active" name="is_active" :checked="$department->is_active" help="Inactive departments cannot be assigned to users." />
                        </div>
                    </div>
                </x-form>
            </x-card>
        </div>
    </div>
@endsection
