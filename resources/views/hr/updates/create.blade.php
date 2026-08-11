@extends('layouts.app')

@section('title', 'New Daily Update')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.updates.index') }}">Daily Work Updates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Update</li>
                </ol>
            </nav>
            <h1 class="page-title">New Daily Update</h1>
            <p class="page-subtitle">Report what you accomplished today.</p>
        </div>
    </div>

    <x-card title="Work Report" icon="bi-journal-text">
        <x-form action="{{ route('hr.updates.store') }}" submit="Submit Update" cancel-url="{{ route('hr.updates.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-form.input label="Date" name="date" type="date" required value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" icon="bi-calendar3" />
                </div>
                <div class="col-12 col-md-4">
                    <x-form.input label="Hours Worked" name="hours_worked" type="number" step="0.5" min="0" max="24" icon="bi-hourglass-split" help="Total productive hours today." />
                </div>
                <div class="col-12">
                    <x-form.input label="Work Description" name="work_description" type="textarea" rows="4" placeholder="What did you work on today?" />
                </div>
                <div class="col-12">
                    <x-form.input label="Tasks Completed" name="tasks_completed" type="textarea" rows="3" placeholder="List the tasks you completed..." />
                </div>
                <div class="col-12">
                    <x-form.input label="Problems / Challenges" name="problems" type="textarea" rows="3" placeholder="Any blockers or issues encountered..." />
                </div>
                <div class="col-12">
                    <x-form.input label="Tomorrow's Plan" name="tomorrow_plan" type="textarea" rows="3" placeholder="What do you plan to do tomorrow..." />
                </div>
                <div class="col-12">
                    <x-form.input label="Additional Notes" name="additional_notes" type="textarea" rows="2" placeholder="Anything else to share..." />
                </div>
            </div>
        </x-form>
    </x-card>
@endsection