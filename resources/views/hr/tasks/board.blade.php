@extends('layouts.app')

@section('title', 'My Task Board')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.tasks.index') }}">Daily Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Board</li>
                </ol>
            </nav>
            <h1 class="page-title">My Task Board</h1>
            <p class="page-subtitle">Tasks assigned to you, organised by status.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('tasks.create')
                <a href="{{ route('hr.tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>New Task
                </a>
            @endcan
        </div>
    </div>

    @php
        $grouped = $tasks->groupBy(fn ($task) => $task->status->value);
    @endphp

    <div class="row g-3 g-md-4 board-scroll">
        @foreach ($statuses as $value => $label)
            @php
                $columnTasks = $grouped->get($value, collect());
                $color = match ($value) {
                    'completed' => 'success',
                    'overdue', 'cancelled' => 'danger',
                    'in_progress' => 'primary',
                    'on_hold' => 'warning',
                    default => 'secondary',
                };
            @endphp
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-2">
                <div class="card board-column">
                    <div class="card-header py-2 d-flex align-items-center justify-content-between">
                        <span class="fw-600 fs-sm">
                            <span class="badge bg-{{ $color }} badge-dot me-1"></span>{{ $label }}
                        </span>
                        <span class="badge badge-soft-{{ $color }}">{{ $columnTasks->count() }}</span>
                    </div>
                    <div class="card-body board-body">
                        @forelse ($columnTasks as $task)
                            <div class="card board-card mb-2 {{ $task->isOverdue() && $value !== 'overdue' ? 'border-danger' : '' }}">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <a href="{{ route('hr.tasks.edit', $task) }}" class="fw-600 fs-sm text-decoration-none text-truncate">{{ $task->title }}</a>
                                        <span class="badge badge-soft-{{ $task->priority?->color() ?? 'secondary' }} flex-shrink-0 fs-xs">{{ $task->priority?->label() ?? '—' }}</span>
                                    </div>
                                    @if ($task->department)
                                        <div class="fs-xs text-muted mt-1"><i class="bi bi-building me-1"></i>{{ $task->department->name }}</div>
                                    @endif
                                    @if ($task->deadline)
                                        <div class="fs-xs mt-1 {{ $task->isOverdue() ? 'text-danger fw-600' : 'text-muted' }}">
                                            <i class="bi bi-calendar-event me-1"></i>Due {{ format_date($task->deadline) }}
                                            @if ($task->isOverdue())
                                                · {{ now()->startOfDay()->diffInDays($task->deadline) }} day(s) overdue
                                            @endif
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('hr.tasks.status', $task) }}" class="mt-2">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach ($statuses as $optValue => $optLabel)
                                                <option value="{{ $optValue }}" @selected($task->status->value === $optValue)>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted fs-xs py-4">No tasks</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
