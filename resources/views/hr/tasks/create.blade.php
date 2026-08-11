@extends('layouts.app')

@section('title', 'New Task')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.tasks.index') }}">Daily Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Task</li>
                </ol>
            </nav>
            <h1 class="page-title">New Task</h1>
            <p class="page-subtitle">Assign a new task to an employee.</p>
        </div>
    </div>

    <x-card title="Task Details" icon="bi-list-task">
        <x-form action="{{ route('hr.tasks.store') }}" submit="Create Task" cancel-url="{{ route('hr.tasks.index') }}" multipart>
            <div class="row g-3">
                <div class="col-12">
                    <x-form.input label="Title" name="title" required icon="bi-type" placeholder="e.g. Prepare monthly sales report" />
                </div>
                <div class="col-12">
                    <x-form.input label="Description" name="description" type="textarea" rows="4" placeholder="Describe the task in detail..." />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select
                        label="Assign To"
                        name="assigned_to"
                        :options="$employees"
                        required
                        placeholder="Select employee"
                        select2
                        icon="bi-person"
                    />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select
                        label="Department"
                        name="department_id"
                        :options="$departments->pluck('name', 'id')->all()"
                        placeholder="Select department"
                        icon="bi-building"
                    />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.select label="Priority" name="priority" :options="$priorities" placeholder="Select priority" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.select label="Status" name="status" :options="$statuses" placeholder="Select status" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Start Date" name="start_date" type="date" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Deadline" name="deadline" type="date" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Estimated Hours" name="estimated_hours" type="number" step="0.5" min="0" icon="bi-stopwatch" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Actual Hours" name="actual_hours" type="number" step="0.5" min="0" icon="bi-hourglass-split" />
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt">
                        <div class="form-text">Optional file up to 10MB.</div>
                    </div>
                </div>
                <div class="col-12">
                    <x-form.input label="Remarks" name="remarks" type="textarea" rows="2" placeholder="Additional notes for the assignee..." />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection
