@extends('layouts.app')

@section('title', 'Add Department')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Department</li>
                </ol>
            </nav>
            <h1 class="page-title">Add Department</h1>
            <p class="page-subtitle">Create a new department.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-card title="New Department" subtitle="Provide a name and short code" icon="bi-building-add" body-class="p-4">
                <x-form :action="route('admin.departments.store')" method="POST" spinner="Creating..." cancel-url="{{ route('admin.departments.index') }}">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <x-form.input label="Department Name" name="name" :required="true" placeholder="e.g. Engineering" />
                        </div>
                        <div class="col-md-5">
                            <x-form.input label="Code" name="code" :required="true" placeholder="e.g. ENG" help="Short, unique identifier." />
                        </div>
                        <div class="col-12">
                            <x-form.input label="Description" name="description" type="textarea" rows="3" placeholder="What does this department do?" />
                        </div>
                        <div class="col-12">
                            <x-form.switch label="Active" name="is_active" :checked="true" help="Inactive departments cannot be assigned to users." />
                        </div>
                    </div>
                </x-form>
            </x-card>
        </div>
    </div>
@endsection
