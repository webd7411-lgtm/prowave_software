@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Leave Types</li>
                </ol>
            </nav>
            <h1 class="page-title">Leave Types</h1>
            <p class="page-subtitle">Define the leave categories available to staff.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLeaveTypeModal">
                <i class="bi bi-plus-lg me-1"></i>New Leave Type
            </button>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card>
                @if ($leaveTypes->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-cards-lg">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th class="text-center">Days / Year</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Used</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($leaveTypes as $type)
                                <tr>
                                    <td data-label="Name">
                                        <div class="fw-medium">{{ $type->name }}</div>
                                        @if ($type->description)
                                            <div class="fs-xs text-muted">{{ $type->description }}</div>
                                        @endif
                                    </td>
                                    <td data-label="Code"><span class="badge bg-soft-dark-subtle text-dark">{{ $type->code }}</span></td>
                                    <td data-label="Days / Year" class="text-center fw-semibold">{{ $type->days_per_year }}</td>
                                    <td data-label="Paid" class="text-center">
                                        <span class="badge {{ $type->is_paid ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                            {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    </td>
                                    <td data-label="Used" class="text-center text-muted">{{ $type->leave_requests_count ?? 0 }}</td>
                                    <td data-label="" class="text-end">
                                        @can('settings.edit')
                                            <form action="{{ route('settings.leave-types.destroy', $type) }}" method="POST"
                                                  class="d-inline" data-confirm="Delete leave type {{ $type->name }}?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-danger-soft" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $leaveTypes->links() }}</div>
                @else
                    <x-empty-state icon="bi-door-open" title="No leave types yet"
                                   subtitle="Add your first leave type to let staff request time off." />
                @endif
            </x-card>
        </div>
    </div>

    <div class="modal fade" id="createLeaveTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('settings.leave-types.store') }}" data-loading>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Leave Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="100"
                                   placeholder="e.g. Annual Leave">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" maxlength="20" placeholder="Auto-generated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Days Per Year <span class="text-danger">*</span></label>
                            <input type="number" name="days_per_year" class="form-control" required min="0" max="365" value="14">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_paid" name="is_paid" value="1" checked>
                            <label class="form-check-label" for="is_paid">Paid leave</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Add Leave Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
