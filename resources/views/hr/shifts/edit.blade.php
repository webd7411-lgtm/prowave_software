@extends('layouts.app')

@section('title', 'Edit Shift')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.shifts.index') }}">Work Shifts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $shift->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Shift</h1>
            <p class="page-subtitle">Update the work schedule.</p>
        </div>
    </div>

    <x-card title="Shift Information" icon="bi-clock">
        <x-form :action="route('hr.shifts.update', $shift)" method="PATCH" submit="Update Shift" cancel-url="{{ route('hr.shifts.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-form.input label="Shift Name" name="name" :value="$shift->name" required icon="bi-clock-history" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Code" name="code" :value="$shift->code" icon="bi-upc-scan" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Start Time" name="start_time" type="time" :value="\Illuminate\Support\Carbon::parse($shift->start_time)->format('H:i')" required icon="bi-sunrise" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="End Time" name="end_time" type="time" :value="\Illuminate\Support\Carbon::parse($shift->end_time)->format('H:i')" required icon="bi-sunset" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Grace Period (minutes)" name="grace_period" type="number" min="0" max="180" :value="$shift->grace_period" icon="bi-arrow-up-right-circle" help="Allowed late arrival before marked late." />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input label="Break Duration (minutes)" name="break_minutes" type="number" min="0" max="300" :value="$shift->break_minutes" icon="bi-cup-hot" />
                </div>
                <div class="col-12">
                    <x-form.switch label="Active" name="is_active" :checked="$shift->is_active" />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection
