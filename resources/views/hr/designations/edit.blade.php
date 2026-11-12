@extends('layouts.app')

@section('title', 'Edit Designation')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.designations.index') }}">Designations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $designation->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Designation</h1>
            <p class="page-subtitle">Update the designation details.</p>
        </div>
    </div>

    <x-card title="Designation Information" icon="bi-pencil">
        <x-form :action="route('hr.designations.update', $designation)" method="PATCH" submit="Update Designation" cancel-url="{{ route('hr.designations.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-form.input label="Name" name="name" :value="$designation->name" required icon="bi-briefcase" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Code" name="code" :value="$designation->code" icon="bi-upc-scan" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :selected="$designation->department_id" placeholder="Select department" select2 />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select label="Default System Role" name="role_id" :options="$roles->pluck('name', 'id')" :selected="$designation->role_id" placeholder="Select default role" select2 />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.switch label="Active" name="is_active" :checked="$designation->is_active" />
                </div>
                <div class="col-12">
                    <x-form.input label="Description" name="description" type="textarea" rows="3" :value="$designation->description" />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection
