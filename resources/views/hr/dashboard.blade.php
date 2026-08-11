@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">HR</li>
                </ol>
            </nav>
            <h1 class="page-title">HR Dashboard</h1>
            <p class="page-subtitle">Company overview, attendance, leave, and task activity.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Total Employees" :value="$totalEmployees" icon="bi-people" variant="primary" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Active" :value="$activeEmployees" icon="bi-person-check" variant="success" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="On Leave" :value="$onLeave" icon="bi-person-walking" variant="warning" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Pending Leaves" :value="$pendingLeaves" icon="bi-hourglass-split" variant="info" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Pending Updates" :value="$pendingUpdates" icon="bi-journal-check" variant="secondary" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Open Tasks" :value="$pendingTasks" icon="bi-list-task" variant="danger" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Present Today" :value="$attendance['present']" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Absent Today" :value="$attendance['absent']" icon="bi-x-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Late Today" :value="$attendance['late']" icon="bi-alarm" variant="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="New This Month" :value="$newEmployees" icon="bi-person-plus" variant="info" />
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-7">
            <x-card title="Attendance Trend" subtitle="{{ now()->format('F Y') }}" icon="bi-bar-chart-line" body-class="p-3">
                <div class="chart-wrap" style="height: 280px;">
                    <canvas id="attendanceTrendChart"
                        data-chart="line"
                        data-chart-labels="{{ json_encode($attendanceChart['labels'] ?? []) }}"
                        data-chart-datasets="{{ json_encode([
                            ['label' => 'Present', 'data' => $attendanceChart['present'] ?? [], 'borderColor' => 'rgba(16,185,129,1)', 'backgroundColor' => 'rgba(16,185,129,0.15)', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2, 'pointRadius' => 3],
                            ['label' => 'Late', 'data' => $attendanceChart['late'] ?? [], 'borderColor' => 'rgba(245,158,11,1)', 'backgroundColor' => 'rgba(245,158,11,0.15)', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2, 'pointRadius' => 3],
                            ['label' => 'Absent', 'data' => $attendanceChart['absent'] ?? [], 'borderColor' => 'rgba(239,68,68,1)', 'backgroundColor' => 'rgba(239,68,68,0.15)', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2, 'pointRadius' => 3],
                        ]) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-5">
            <x-card title="Leave Requests by Status" icon="bi-pie-chart" body-class="p-3">
                <div class="chart-wrap" style="height: 280px;">
                    <canvas id="leaveStatusChart"
                        data-chart="doughnut"
                        data-chart-labels="{{ json_encode($leaveChart['labels']) }}"
                        data-chart-datasets="{{ json_encode([
                            ['data' => $leaveChart['data'], 'backgroundColor' => ['rgba(245,158,11,0.85)', 'rgba(16,185,129,0.85)', 'rgba(239,68,68,0.85)'], 'borderWidth' => 0],
                        ]) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-7">
            <x-card title="Department Overview" icon="bi-diagram-3" class="h-100">
                <div class="d-md-none">
                    @forelse ($departments as $department)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <span class="fw-500">{{ $department->name }}</span>
                            <span class="badge badge-soft-primary">{{ $department->users_count }} staff</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No departments found.</div>
                    @endforelse
                </div>
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Staff</th>
                                    <th style="width:45%">Headcount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    @php
                                        $pct = $totalEmployees ? round($department->users_count / $totalEmployees * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-500">{{ $department->name }}</td>
                                        <td>{{ $department->users_count }}</td>
                                        <td>
                                            <div class="progress" style="height:6px">
                                                <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No departments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-5">
            <x-card title="Recent Activity" icon="bi-activity" class="h-100">
                <div class="d-md-none">
                    @forelse ($recentActivities as $log)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <img src="{{ user_avatar($log->user) }}" alt="" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fs-sm text-truncate">{{ $log->user?->name ?? 'Unknown user' }}</div>
                                <div class="fs-xs text-muted">{{ $log->login_at?->diffForHumans() }}</div>
                            </div>
                            <i class="bi bi-box-arrow-in-right text-muted flex-shrink-0"></i>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No recent activity.</div>
                    @endforelse
                </div>
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentActivities as $log)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ user_avatar($log->user) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover">
                                                <span class="fw-500">{{ $log->user?->name ?? 'Unknown user' }}</span>
                                            </div>
                                        </td>
                                        <td class="fs-sm text-muted">{{ $log->login_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No recent activity.</td></tr>
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