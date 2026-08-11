@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Leave Requests</li>
                </ol>
            </nav>
            <h1 class="page-title">Leave Requests</h1>
            <p class="page-subtitle">Review and manage employee leave applications.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('leave.view')
                <a href="{{ route('hr.leave.dashboard') }}" class="btn btn-soft-primary">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            @endcan
            @can('leave.create')
                <a href="{{ route('hr.leave.create') }}" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-1"></i>New Request
                </a>
            @endcan
        </div>
    </div>

    <x-card body-class="p-3">
        <form method="GET" action="{{ route('hr.leave.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Employee</label>
                <select name="employee_id" class="form-select select2" data-placeholder="All employees">
                    <option value="">All employees</option>
                    @foreach ($employees as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['employee_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['department_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Leave Type</label>
                <select name="leave_type_id" class="form-select">
                    <option value="">All</option>
                    @foreach ($leaveTypes as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['leave_type_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">From</label>
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">To</label>
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-12 col-md-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-soft-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </x-card>

    @if ($leaves->count())
        <div class="d-md-none">
            @foreach ($leaves as $leave)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ user_avatar($leave->employee) }}" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-600 text-truncate">{{ $leave->employee?->name }}</div>
                                <div class="fs-xs text-muted">{{ $leave->leaveType?->name ?? '—' }}</div>
                            </div>
                            <span class="badge badge-soft-{{ $leave->status->color() }}">{{ $leave->status->label() }}</span>
                        </div>
                        <div class="row g-2 mt-2 fs-xs text-muted">
                            <div class="col-6"><i class="bi bi-calendar-range me-1"></i>{{ format_date($leave->start_date) }} → {{ format_date($leave->end_date) }}</div>
                            <div class="col-6"><i class="bi bi-calendar-day me-1"></i>{{ $leave->days }} day(s)</div>
                        </div>
                        @if ($leave->status->value === 'pending' && auth()->user()->can('leave.approve'))
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-soft-success flex-grow-1" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}" data-leave-status="approved"><i class="bi bi-check-lg me-1"></i>Approve</button>
                                <button type="button" class="btn btn-sm btn-soft-danger flex-grow-1" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}" data-leave-status="rejected"><i class="bi bi-x-lg me-1"></i>Reject</button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ user_avatar($leave->employee) }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                                        <div class="min-w-0">
                                            <div class="fw-500 text-truncate">{{ $leave->employee?->name }}</div>
                                            <div class="fs-xs text-muted">{{ $leave->employee?->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $leave->leaveType?->name ?? '—' }}</td>
                                <td>
                                    <div>{{ format_date($leave->start_date) }}</div>
                                    <div class="fs-xs text-muted">→ {{ format_date($leave->end_date) }}</div>
                                </td>
                                <td>{{ $leave->days }}</td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width:220px" title="{{ $leave->reason }}">{{ $leave->reason ?: '—' }}</span>
                                </td>
                                <td><span class="badge badge-soft-{{ $leave->status->color() }}">{{ $leave->status->label() }}</span></td>
                                <td class="fs-sm text-muted">{{ $leave->reviewer?->name ?? '—' }}</td>
                                <td class="text-end">
                                    @if ($leave->status->value === 'pending' && auth()->user()->can('leave.approve'))
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-soft-success" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}" data-leave-status="approved" title="Approve"><i class="bi bi-check-lg"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}" data-leave-status="rejected" title="Reject"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $leaves->links() }}
        </div>
    @else
        <x-empty-state
            title="No leave requests found"
            text="Try adjusting your filters or create a new request."
            :action="auth()->user()->can('leave.create') ? route('hr.leave.create') : null"
            action-text="New Request"
        />
    @endif

    @push('modals')
        @foreach ($leaves as $leave)
            @if ($leave->status->value === 'pending')
                <x-modal
                    id="reviewModal{{ $leave->id }}"
                    title="Review leave request"
                    subtitle="{{ $leave->employee?->name }} · {{ $leave->leaveType?->name }} ({{ format_date($leave->start_date) }} → {{ format_date($leave->end_date) }})"
                >
                    <form method="POST" action="{{ route('hr.leave.review', $leave) }}" data-loading>
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <div class="mb-3">
                            <label class="form-label">Remarks <span class="text-muted fs-xs">(optional)</span></label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Add a note for the employee...">{{ old('remarks') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-grow-1" data-review-submit="approved"><i class="bi bi-check-lg me-1"></i>Approve</button>
                            <button type="submit" class="btn btn-outline-danger flex-grow-1" data-review-submit="rejected"><i class="bi bi-x-lg me-1"></i>Reject</button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endforeach
    @endpush
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-review-submit]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const form = button.closest('form');
                    form.querySelector('input[name="status"]').value = button.dataset.reviewSubmit;
                    form.submit();
                });
            });
        });
    </script>
@endpush
