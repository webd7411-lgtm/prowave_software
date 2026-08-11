@extends('layouts.app')

@section('title', 'Add Staff')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.staff.index') }}">Staff</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Staff</li>
                </ol>
            </nav>
            <h1 class="page-title">Add Staff Member</h1>
            <p class="page-subtitle">Create a new employee profile.</p>
        </div>
    </div>

    <x-card title="Staff Information" subtitle="Fill in the employee details" icon="bi-person-plus">
        @include('hr.staff._form', ['employee' => null])
    </x-card>
@endsection
