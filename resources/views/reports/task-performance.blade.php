@extends('layouts.app')

@section('title', 'Task Performance Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Task Performance</li>
                </ol>
            </nav>
            <h1 class="page-title">Task Performance</h1>
            <p class="page-subtitle">Project and daily task completion by team member.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['report' => 'task-performance', 'from' => $from?->toDateString(), 'to' => $to?->toDateString()]) }}"
               class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.task-performance') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from?->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to?->toDateString() }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ route('reports.task-performance') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <x-card title="Team Task Completion" subtitle="Tasks created in the selected range" icon="bi-list-check">
        @if ($rows->count())
            @php
                $max = $rows->max('total');
            @endphp
            <div class="table-responsive">
                <table class="table align-middle mb-4 table-cards-lg">
                    <thead>
                    <tr>
                        <th>Team Member</th>
                        <th style="width: 30%;">Throughput</th>
                        <th class="text-center">Total</th>
                        <th class="text-center text-success">Completed</th>
                        <th class="text-center text-danger">Overdue</th>
                        <th class="text-end">Completion</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td data-label="Team Member" class="fw-medium">{{ $row['name'] }}</td>
                            <td data-label="Throughput">
                                <div class="progress w-100" style="height: 8px; max-width: 180px;">
                                    <div class="progress-bar" style="width: {{ $max > 0 ? ($row['total'] / $max) * 100 : 0 }}%"></div>
                                </div>
                            </td>
                            <td data-label="Total" class="text-center fw-semibold">{{ $row['total'] }}</td>
                            <td data-label="Completed" class="text-center text-success">{{ $row['completed'] }}</td>
                            <td data-label="Overdue" class="text-center text-danger">{{ $row['overdue'] }}</td>
                            <td data-label="Completion" class="text-end fw-semibold">{{ $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1).'%' : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state icon="bi-list-check" title="No tasks in this period" />
        @endif
    </x-card>
@endsection
