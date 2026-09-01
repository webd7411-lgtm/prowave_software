@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Calendar</li>
                </ol>
            </nav>
            <h1 class="page-title">Company Calendar</h1>
            <p class="page-subtitle">Meetings, deadlines and leaves across the entire company.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group btn-group-sm" role="group">
                @foreach (['month' => 'Month', 'week' => 'Week', 'day' => 'Day', 'agenda' => 'Agenda'] as $key => $label)
                    <button type="button" class="btn {{ $view === $key ? 'btn-primary' : 'btn-outline-secondary' }}" data-calendar-view-btn="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-calendar-today><i class="bi bi-calendar-check me-1"></i>Today</button>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-9">
            <x-card body-class="p-0">
                <div class="calendar-toolbar d-flex flex-wrap align-items-center gap-2 px-3 py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="icon-btn btn-sm" data-calendar-nav="-1" aria-label="Previous">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="icon-btn btn-sm" data-calendar-nav="1" aria-label="Next">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <h5 class="mb-0 fw-600" id="calendar-title">Loading...</h5>
                    <div class="ms-auto d-flex flex-wrap gap-3 fs-xs text-muted legend">
                        <span><i class="bi bi-circle-fill me-1 text-primary"></i>Tasks</span>
                        <span><i class="bi bi-circle-fill me-1 text-warning"></i>Leave</span>
                        <span><i class="bi bi-circle-fill me-1 text-info"></i>Meetings</span>
                        <span><i class="bi bi-circle-fill me-1 text-purple" style="color:#8B5CF6"></i>Daily Tasks</span>
                    </div>
                </div>
                <div class="p-0" id="calendar-grid">
                    <div class="p-5 text-center">
                        <div class="spinner-border text-primary" role="status" aria-label="Loading calendar"></div>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-xl-3">
            <x-card title="In This View" subtitle="What's coming up" icon="bi-calendar-event" body-class="p-0">
                <div class="list-group list-group-flush" id="calendar-upcoming">
                    <div class="p-3 text-muted fs-sm text-center">Loading upcoming events...</div>
                </div>
            </x-card>
        </div>
    </div>

    <div data-calendar
         data-calendar-view="{{ $view }}"
         data-calendar-url="{{ route('calendar.events') }}"
         class="d-none"></div>
@endsection
