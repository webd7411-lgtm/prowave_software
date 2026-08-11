@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.tasks.index') }}">Daily Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Task</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Task</h1>
            <p class="page-subtitle">Update task details and progress.</p>
        </div>
    </div>

    <x-card title="Task Details" icon="bi-list-task">
        <x-form action="{{ route('hr.tasks.update', $task) }}" method="PATCH" submit="Update Task" cancel-url="{{ route('hr.tasks.index') }}" multipart>
            <div class="row g-3">
                <div class="col-12">
                    <x-form.input label="Title" name="title" required icon="bi-type" :value="$task->title" />
                </div>
                <div class="col-12">
                    <x-form.input label="Description" name="description" type="textarea" rows="4" :value="$task->description" />
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
                        :selected="$task->assigned_to"
                    />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select
                        label="Department"
                        name="department_id"
                        :options="$departments->pluck('name', 'id')->all()"
                        placeholder="Select department"
                        icon="bi-building"
                        :selected="$task->department_id"
                    />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.select label="Priority" name="priority" :options="$priorities" placeholder="Select priority" :selected="$task->priority?->value" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.select label="Status" name="status" :options="$statuses" placeholder="Select status" :selected="$task->status->value" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Start Date" name="start_date" type="date" :value="$task->start_date?->format('Y-m-d')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Deadline" name="deadline" type="date" :value="$task->deadline?->format('Y-m-d')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Estimated Hours" name="estimated_hours" type="number" step="0.5" min="0" icon="bi-stopwatch" :value="$task->estimated_hours" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form.input label="Actual Hours" name="actual_hours" type="number" step="0.5" min="0" icon="bi-hourglass-split" :value="$task->actual_hours" />
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt">
                        <div class="form-text">
                            Optional file up to 10MB.
                            @if ($task->attachment)
                                · Current: <a href="{{ asset('storage/'.$task->attachment) }}" target="_blank" class="text-decoration-none">view attachment</a> <span class="text-muted">(upload a new file to replace)</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <x-form.input label="Remarks" name="remarks" type="textarea" rows="2" :value="$task->remarks" />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection
