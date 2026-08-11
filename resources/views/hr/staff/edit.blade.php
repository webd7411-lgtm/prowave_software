@extends('layouts.app')

@section('title', 'Edit Staff')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.staff.index') }}">Staff</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.staff.show', $employee) }}">{{ $employee->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Staff</h1>
            <p class="page-subtitle">Update {{ $employee->name }}'s employment information.</p>
        </div>
    </div>

    <x-card title="Edit Staff Member" subtitle="Update the employee details" icon="bi-pencil">
        @include('hr.staff._form', ['employee' => $employee])
    </x-card>
@endsection
