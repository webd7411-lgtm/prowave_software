@extends('layouts.app')

@section('title', 'Leave Dashboard')

@section('content')
    @php
        $byType = $approvedLeaves->groupBy(fn ($l) => $l->leaveType?->name ?? 'Other');
    @endphp

    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Leave Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <h1 class="page-title">Leave Dashboard</h1>
            <p class="page-subtitle">Overview of leave activity across the company.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('leave.create')
                <a href="{{ route('hr.leave.create') }}" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-1"></i>New Request
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Pending" :value="$pending" icon="bi-hourglass-split" variant="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Approved" :value="$approved" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Rejected" :value="$rejected" icon="bi-x-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="On Leave Today" :value="$onLeaveToday" icon="bi-person-walking" variant="info" />
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-5">
            <x-card title="Approved by Type" subtitle="{{ now()->format('F Y') }}" icon="bi-pie-chart">
                <div class="chart-wrap" style="height: 280px;">
                    @if ($approvedLeaves->count())
                        <canvas id="leaveTypeChart"
                            data-chart="doughnut"
                            data-chart-labels="{{ json_encode($byType->keys()->all()) }}"
                            data-chart-datasets="{{ json_encode([
                                ['data' => $byType->map->count()->values()->all(), 'backgroundColor' => ['rgba(16,185,129,0.8)', 'rgba(59,130,246,0.8)', 'rgba(245,158,11,0.8)', 'rgba(168,85,247,0.8)', 'rgba(236,72,153,0.8)'], 'borderWidth' => 0],
                            ]) }}"
                        ></canvas>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted fs-sm">No approved leaves this month.</div>
                    @endif
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-7">
            <x-card title="Approved This Month" subtitle="{{ now()->format('F Y') }}" icon="bi-calendar-check">
                <div class="d-md-none">
                    @forelse ($approvedLeaves as $leave)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="min-w-0 me-2">
                                <div class="fw-500 text-truncate">{{ $leave->employee?->name }}</div>
                                <div class="fs-xs text-muted">{{ $leave->leaveType?->name }} · {{ format_date($leave->start_date) }} → {{ format_date($leave->end_date) }}</div>
                            </div>
                            <span class="badge badge-soft-secondary flex-shrink-0">{{ $leave->days }} day(s)</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No approved leaves this month.</div>
                    @endforelse
                </div>

                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($approvedLeaves as $leave)
                                    <tr>
                                        <td class="fw-500">{{ $leave->employee?->name }}</td>
                                        <td>{{ $leave->leaveType?->name }}</td>
                                        <td class="fs-sm text-muted">{{ format_date($leave->start_date) }} → {{ format_date($leave->end_date) }}</td>
                                        <td>{{ $leave->days }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No approved leaves this month.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-12">
            <x-card title="Recent Requests" icon="bi-clock-history">
                <div class="d-md-none">
                    @forelse ($recentRequests as $leave)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <img src="{{ user_avatar($leave->employee) }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-500 text-truncate">{{ $leave->employee?->name }}</div>
                                <div class="fs-xs text-muted">{{ $leave->leaveType?->name }} · {{ format_date($leave->start_date) }}</div>
                            </div>
                            <span class="badge badge-soft-{{ $leave->status->color() }} flex-shrink-0">{{ $leave->status->label() }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No leave requests yet.</div>
                    @endforelse
                </div>

                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Requested</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentRequests as $leave)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ user_avatar($leave->employee) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover">
                                                <span class="fw-500">{{ $leave->employee?->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $leave->leaveType?->name }}</td>
                                        <td class="fs-sm text-muted">{{ $leave->created_at->diffForHumans() }}</td>
                                        <td>{{ $leave->days }}</td>
                                        <td><span class="badge badge-soft-{{ $leave->status->color() }}">{{ $leave->status->label() }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No leave requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Prowave && window.Prowave.initCharts) {
                window.Prowave.initCharts();
            }
        });
    </script>
@endpush
