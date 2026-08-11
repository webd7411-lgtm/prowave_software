@extends('layouts.app')

@section('title', $employee->name)

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.staff.index') }}">Staff</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $employee->name }}</li>
                </ol>
            </nav>
            <h1 class="page-title">Staff Profile</h1>
            <p class="page-subtitle">Complete profile for {{ $employee->name }}.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('staff.edit')
                <a href="{{ route('hr.staff.edit', $employee) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endcan
            @can('staff.delete')
                @if (! $employee->trashed())
                    <form method="POST" action="{{ route('hr.staff.destroy', $employee) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-soft-danger" data-confirm data-confirm-title="Delete staff?" data-confirm-text="This staff member will be moved to trash." data-confirm-confirm-text="Yes, delete">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('hr.staff.restore', $employee->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-soft-primary" data-confirm data-confirm-title="Restore staff?" data-confirm-text="This staff member will be restored." data-confirm-confirm-text="Yes, restore" data-confirm-danger="false">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-4">
            <div class="card overflow-hidden">
                <div class="profile-cover"></div>
                <div class="text-center pb-4">
                    <div class="profile-avatar-wrap mb-3">
                        <img src="{{ user_avatar($employee) }}" class="profile-avatar" alt="{{ $employee->name }}">
                    </div>
                    <h4 class="mb-1">{{ $employee->name }}</h4>
                    <p class="text-muted mb-2">{{ $employee->designation ?? 'Team Member' }}</p>

                    <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                        <span class="badge badge-soft-{{ $employee->employee_status?->color() ?? 'secondary' }}">{{ $employee->employee_status?->label() ?? '—' }}</span>
                        @if ($employee->employment_type)
                            <span class="badge badge-soft-info">{{ $employee->employment_type->label() }}</span>
                        @endif
                    </div>

                    <div class="divider mx-4"></div>

                    <div class="text-start px-4">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-person-badge me-2"></i>Employee ID</span>
                            <span class="fw-500">{{ $employee->employee_id ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                            <span class="fw-500 text-truncate ms-2">{{ $employee->email }}</span>
                        </div>
                        @if ($employee->phone)
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-telephone me-2"></i>Phone</span>
                                <span class="fw-500">{{ $employee->phone }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-building me-2"></i>Department</span>
                            <span class="fw-500">{{ $employee->department?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-person-badge me-2"></i>Manager</span>
                            <span class="fw-500 text-truncate ms-2">{{ $employee->reportingManager?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Joined</span>
                            <span class="fw-500">{{ $employee->joining_date ? format_date($employee->joining_date) : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <ul class="nav nav-tabs profile-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab"><i class="bi bi-person-vcard me-1"></i>Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attendance" type="button" role="tab"><i class="bi bi-calendar-check me-1"></i>Attendance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tasks" type="button" role="tab"><i class="bi bi-list-task me-1"></i>Tasks</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-leave" type="button" role="tab"><i class="bi bi-calendar-week me-1"></i>Leave</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-updates" type="button" role="tab"><i class="bi bi-journal-text me-1"></i>Updates</button>
                </li>
            </ul>

            <div class="tab-content py-4">
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-12">
                            <x-card title="Personal Information" icon="bi-person-vcard">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Father Name</div><div class="fw-500">{{ $employee->father_name ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Gender</div><div class="fw-500">{{ $employee->gender?->label() ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Date of Birth</div><div class="fw-500">{{ $employee->dob ? format_date($employee->dob) : '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">CNIC</div><div class="fw-500">{{ $employee->cnic ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">WhatsApp</div><div class="fw-500">{{ $employee->whatsapp ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">City</div><div class="fw-500">{{ $employee->city ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Emergency Contact</div><div class="fw-500">{{ $employee->emergency_contact ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Emergency Phone</div><div class="fw-500">{{ $employee->emergency_phone ?? '—' }}</div></div>
                                </div>
                                @if ($employee->address)
                                    <hr>
                                    <div class="fs-xs text-muted text-uppercase mb-1">Address</div>
                                    <p class="mb-0">{{ $employee->address }}</p>
                                @endif
                            </x-card>
                        </div>

                        <div class="col-12">
                            <x-card title="Work Information" icon="bi-briefcase">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Employment Type</div><div class="fw-500">{{ $employee->employment_type?->label() ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Shift</div><div class="fw-500">{{ $employee->shift?->name ?? '—' }}</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Salary</div><div class="fw-500">@money($employee->salary)</div></div>
                                    <div class="col-6 col-md-3"><div class="fs-xs text-muted text-uppercase">Subordinates</div><div class="fw-500">{{ $employee->subordinates->count() }}</div></div>
                                </div>
                                @if ($employee->notes)
                                    <hr>
                                    <div class="fs-xs text-muted text-uppercase mb-1">Notes</div>
                                    <p class="mb-0">{{ $employee->notes }}</p>
                                @endif
                            </x-card>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <x-stat-card label="Present" :value="$attendanceSummary['present']" icon="bi-check2-circle" variant="success" />
                        </div>
                        <div class="col-6 col-md-3">
                            <x-stat-card label="Late" :value="$attendanceSummary['late']" icon="bi-alarm" variant="warning" />
                        </div>
                        <div class="col-6 col-md-3">
                            <x-stat-card label="Absent" :value="$attendanceSummary['absent']" icon="bi-x-circle" variant="danger" />
                        </div>
                        <div class="col-6 col-md-3">
                            <x-stat-card label="On Leave" :value="$attendanceSummary['leave']" icon="bi-person-walking" variant="info" />
                        </div>
                    </div>
                    <x-card title="Recent Attendance" icon="bi-calendar-check">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentAttendance as $record)
                                        <tr>
                                            <td>{{ format_date($record->date) }}</td>
                                            <td>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                            <td>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                            <td>
                                                @if ($record->total_work_minutes !== null)
                                                    {{ intdiv($record->total_work_minutes, 60) }}h {{ $record->total_work_minutes % 60 }}m
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="badge badge-soft-{{ $record->status->color() }}">{{ $record->status->label() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">No attendance records yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @can('attendance.view')
                            <a href="{{ route('hr.attendance.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline mt-2">View all attendance</a>
                        @endcan
                    </x-card>
                </div>

                <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <x-stat-card label="Total Tasks" :value="$taskSummary['total']" icon="bi-list-task" variant="primary" />
                        </div>
                        <div class="col-4">
                            <x-stat-card label="Completed" :value="$taskSummary['completed']" icon="bi-check2-all" variant="success" />
                        </div>
                        <div class="col-4">
                            <x-stat-card label="Pending" :value="$taskSummary['pending']" icon="bi-hourglass-split" variant="warning" />
                        </div>
                    </div>
                    <x-card title="Recent Tasks" icon="bi-list-check">
                        @forelse ($recentTasks as $task)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="min-w-0 me-2">
                                    <div class="fw-600 text-truncate">{{ $task->title }}</div>
                                    <div class="fs-xs text-muted">Deadline: {{ $task->deadline ? format_date($task->deadline) : '—' }}</div>
                                </div>
                                <span class="badge badge-soft-{{ $task->status->color() }} flex-shrink-0">{{ $task->status->label() }}</span>
                            </div>
                        @empty
                            <x-empty-state title="No tasks assigned" icon="bi-list-task" />
                        @endforelse
                    </x-card>
                </div>

                <div class="tab-pane fade" id="tab-leave" role="tabpanel">
                    <div class="row g-3 mb-3">
                        @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $leaveStatus)
                            <div class="col-6 col-md-3">
                                <div class="card stat-card card-hover">
                                    <div class="card-body">
                                        <div class="stat-label text-capitalize">{{ $leaveStatus }}</div>
                                        <div class="stat-value">{{ $leaveSummary->get($leaveStatus, 0) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <x-card title="Leave Balance" icon="bi-calendar-week">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Allocated</th>
                                        <th>Used</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($employee->leaveBalances as $balance)
                                        <tr>
                                            <td>{{ $balance->leaveType?->name ?? '—' }}</td>
                                            <td>{{ $balance->allocated }}</td>
                                            <td>{{ $balance->used }}</td>
                                            <td><span class="badge badge-soft-{{ $balance->remaining > 0 ? 'success' : 'danger' }}">{{ $balance->remaining }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No leave balances yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>

                <div class="tab-pane fade" id="tab-updates" role="tabpanel">
                    <x-card title="Daily Work Updates" icon="bi-journal-text">
                        @forelse ($recentUpdates as $update)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-600">{{ format_date($update->date) }}</span>
                                    <span class="badge badge-soft-{{ $update->status->color() }}">{{ $update->status->label() }}</span>
                                </div>
                                @if ($update->work_description)
                                    <p class="mb-1 mt-1 text-muted text-truncate">{{ $update->work_description }}</p>
                                @endif
                                <div class="fs-xs text-muted">{{ $update->hours_worked ? $update->hours_worked.' hrs' : '—' }}</div>
                            </div>
                        @empty
                            <x-empty-state title="No updates submitted" icon="bi-journal-text" />
                        @endforelse
                    </x-card>
                </div>

            </div>
        </div>
    </div>
@endsection
