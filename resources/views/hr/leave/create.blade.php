@extends('layouts.app')

@section('title', 'New Leave Request')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Leave Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Request</li>
                </ol>
            </nav>
            <h1 class="page-title">New Leave Request</h1>
            <p class="page-subtitle">Submit a leave application on behalf of an employee.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-8">
            <x-card title="Leave Details" icon="bi-calendar3">
                <x-form action="{{ route('hr.leave.store') }}" submit="Submit Request" cancel-url="{{ route('hr.leave.index') }}">
                    <div class="row g-3">
                        <div class="col-12">
                            <x-form.select
                                label="Employee"
                                name="employee_id"
                                :options="$employees"
                                required
                                placeholder="Select employee"
                                select2
                                icon="bi-person"
                            />
                        </div>
                        <div class="col-12 col-md-6">
                            <x-form.select
                                label="Leave Type"
                                name="leave_type_id"
                                :options="$leaveTypes->pluck('name', 'id')->all()"
                                required
                                placeholder="Select leave type"
                                icon="bi-tag"
                            />
                        </div>
                        <div class="col-12 col-md-3">
                            <x-form.input label="Start Date" name="start_date" type="date" required />
                        </div>
                        <div class="col-12 col-md-3">
                            <x-form.input label="End Date" name="end_date" type="date" required />
                        </div>
                        <div class="col-12">
                            <x-form.input label="Reason" name="reason" type="textarea" rows="4" placeholder="Briefly describe the reason for leave..." />
                        </div>
                    </div>
                </x-form>
            </x-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-card title="Available Leave Types" icon="bi-info-circle">
                <ul class="list-unstyled mb-0">
                    @forelse ($leaveTypes as $leaveType)
                        <li class="d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-500">{{ $leaveType->name }}</div>
                                @if ($leaveType->description)
                                    <div class="fs-xs text-muted">{{ $leaveType->description }}</div>
                                @endif
                            </div>
                            <div class="text-end ms-3">
                                <span class="badge badge-soft-{{ $leaveType->is_paid ? 'success' : 'secondary' }}">{{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}</span>
                                <div class="fs-xs text-muted mt-1">{{ $leaveType->days_per_year }} day(s)/year</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-muted fs-sm">No leave types configured yet.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
@endsection
