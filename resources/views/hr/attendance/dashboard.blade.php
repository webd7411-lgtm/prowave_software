@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')
    @php
        $todayRecord = auth()->user()->attendanceRecords()->whereDate('date', now()->toDateString())->first();
        [$calYear, $calMonth] = [(int) substr($date, 0, 4), (int) substr($date, 5, 2)];
        $firstDay = \Illuminate\Support\Carbon::create($calYear, $calMonth, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startPad = $firstDay->dayOfWeek; // 0 = Sunday
        $presentByDay = $chart['present'];
        $absentByDay = $chart['absent'];
        $lateByDay = $chart['late'];
    @endphp

    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <h1 class="page-title">Attendance Dashboard</h1>
            <p class="page-subtitle">Today's overview and monthly trends.</p>
        </div>
        <form method="GET" action="{{ route('hr.attendance.dashboard') }}" class="d-flex gap-2 align-items-center">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" max="{{ now()->toDateString() }}">
            <button type="submit" class="btn btn-sm btn-soft-primary"><i class="bi bi-calendar-event me-1"></i>Go</button>
        </form>
    </div>

    @can('attendance.create')
        <div class="alert alert-soft-primary d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history fs-5"></i>
                <div>
                    <div class="fw-600">{{ $todayRecord ? 'Attendance for today' : 'Mark your attendance' }}</div>
                    @if ($todayRecord)
                        <div class="fs-sm text-muted">
                            @if ($todayRecord->check_in) Checked in at {{ \Illuminate\Support\Carbon::parse($todayRecord->check_in)->format('h:i A') }} @endif
                            @if ($todayRecord->check_out) · Checked out at {{ \Illuminate\Support\Carbon::parse($todayRecord->check_out)->format('h:i A') }} @endif
                            <span class="badge badge-soft-{{ $todayRecord->status->color() }} ms-1">{{ $todayRecord->status->label() }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                @if (! $todayRecord || ! $todayRecord->check_in)
                    <form method="POST" action="{{ route('hr.attendance.check-in') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Check In</button>
                    </form>
                @endif
                @if ($todayRecord && $todayRecord->check_in && ! $todayRecord->check_out)
                    <form method="POST" action="{{ route('hr.attendance.check-out') }}">
                        @csrf
                        <button type="submit" class="btn btn-soft-danger"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
                    </form>
                @endif
            </div>
        </div>
    @endcan

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Present Today" :value="$summary['present']" icon="bi-check2-circle" variant="success" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Absent Today" :value="$summary['absent']" icon="bi-x-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Late Today" :value="$summary['late']" icon="bi-alarm" variant="warning" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="On Leave" :value="$summary['on_leave']" icon="bi-person-walking" variant="info" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Total Staff" :value="$summary['total_staff']" icon="bi-people" variant="primary" />
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-stat-card label="Attendance %" :value="$summary['attendance_pct']" suffix="%" icon="bi-percent" variant="secondary" />
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-7">
            <x-card title="Monthly Attendance" subtitle="{{ $firstDay->format('F Y') }}" icon="bi-bar-chart-line" body-class="p-3">
                <div class="chart-wrap" style="height: 280px;">
                    <canvas id="attendanceChart"
                        data-chart="bar"
                        data-chart-labels="{{ json_encode($chart['labels']) }}"
                        data-chart-datasets="{{ json_encode([
                            ['label' => 'Present', 'data' => $chart['present'], 'backgroundColor' => 'rgba(16,185,129,0.7)', 'borderRadius' => 4],
                            ['label' => 'Late', 'data' => $chart['late'], 'backgroundColor' => 'rgba(245,158,11,0.7)', 'borderRadius' => 4],
                            ['label' => 'Absent', 'data' => $chart['absent'], 'backgroundColor' => 'rgba(239,68,68,0.7)', 'borderRadius' => 4],
                        ]) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-5">
            <x-card title="Monthly Calendar" subtitle="{{ $firstDay->format('F Y') }}" icon="bi-calendar3">
                <div class="attendance-calendar">
                    <div class="row g-1 text-center mb-2 fs-xs text-muted">
                        <div class="col">S</div><div class="col">M</div><div class="col">T</div><div class="col">W</div><div class="col">T</div><div class="col">F</div><div class="col">S</div>
                    </div>
                    <div class="row g-1">
                        @for ($i = 0; $i < $startPad; $i++)
                            <div class="col"></div>
                        @endfor
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $idx = $day - 1;
                                $p = $presentByDay[$idx] ?? 0;
                                $a = $absentByDay[$idx] ?? 0;
                                $l = $lateByDay[$idx] ?? 0;
                                $cell = 'empty';
                                $dot = '';
                                if ($p > 0) { $cell = 'present'; }
                                if ($l > 0) { $cell = 'late'; }
                                if ($a > 0) { $cell = 'absent'; }
                                $isToday = $day === (int) now()->day && $calMonth === (int) now()->month && $calYear === (int) now()->year;
                            @endphp
                            <div class="col text-center">
                                <div class="cal-day {{ $cell }} {{ $isToday ? 'today' : '' }}" title="{{ $p ? 'Present '.$p : '' }} {{ $l ? '· Late '.$l : '' }} {{ $a ? '· Absent '.$a : '' }}">
                                    {{ $day }}
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-3 fs-xs text-muted">
                    <span><span class="cal-legend present"></span> Present</span>
                    <span><span class="cal-legend late"></span> Late</span>
                    <span><span class="cal-legend absent"></span> Absent</span>
                    <span><span class="cal-legend empty"></span> No record</span>
                </div>
            </x-card>
        </div>

        <div class="col-12">
            <x-card title="Attendance List" subtitle="{{ format_date($date) }}" icon="bi-list-check">
                <form method="GET" action="{{ route('hr.attendance.dashboard') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <select name="employee_id" class="form-select form-select-sm select2">
                            <option value="">All employees</option>
                            @foreach ($employees as $id => $name)
                                <option value="{{ $id }}" @selected(request('employee_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All departments</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}" @selected(request('department_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-sm btn-soft-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                </form>

                <div class="d-md-none">
                    @forelse ($dailyList as $record)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="min-w-0 me-2">
                                <div class="fw-500 text-truncate">{{ $record->employee?->name }}</div>
                                <div class="fs-xs text-muted">{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }} — {{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</div>
                            </div>
                            <span class="badge badge-soft-{{ $record->status->color() }} flex-shrink-0">{{ $record->status->label() }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">No attendance for this date.</div>
                    @endforelse
                </div>

                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dailyList as $record)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ user_avatar($record->employee) }}" alt="" class="rounded-circle" style="width:28px;height:28px;object-fit:cover">
                                                <span class="fw-500">{{ $record->employee?->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                        <td>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                        <td>{{ $record->total_work_minutes !== null ? intdiv($record->total_work_minutes, 60).'h '.($record->total_work_minutes % 60).'m' : '—' }}</td>
                                        <td><span class="badge badge-soft-{{ $record->status->color() }}">{{ $record->status->label() }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No attendance for this date.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $dailyList->links() }}
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
