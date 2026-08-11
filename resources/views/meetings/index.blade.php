@extends('layouts.app')

@section('title', 'Meetings')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Meetings</li>
                </ol>
            </nav>
            <h1 class="page-title">Meetings</h1>
            <p class="page-subtitle">Schedule, attend and document every company meeting.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('meetings.create')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#meetingModal">
                    <i class="bi bi-plus-lg me-1"></i>Schedule Meeting
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach (\App\Models\Meeting::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Meeting::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-6 col-md-2 col-lg-2">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" aria-label="From date">
                </div>
                <div class="col-6 col-sm-6 col-md-2 col-lg-2">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" aria-label="To date">
                </div>
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search meetings..." aria-label="Search">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        @if (request()->hasAny(['type', 'status', 'from', 'to', 'search']))
                            <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary">×</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        @forelse ($meetings as $meeting)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card meeting-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge badge-soft-{{ $meeting->type === 'client' ? 'info' : ($meeting->type === 'project' ? 'success' : ($meeting->type === 'hr' ? 'warning' : 'secondary')) }}">
                                {{ \App\Models\Meeting::TYPES[$meeting->type] ?? ucfirst($meeting->type) }}
                            </span>
                            <span class="badge bg-{{ $meeting->status === 'completed' ? 'success' : ($meeting->status === 'cancelled' ? 'danger' : ($meeting->status === 'ongoing' ? 'primary' : 'warning')) }}-subtle text-{{ $meeting->status === 'completed' ? 'success' : ($meeting->status === 'cancelled' ? 'danger' : ($meeting->status === 'ongoing' ? 'primary' : 'warning')) }} rounded-pill">
                                {{ ucfirst($meeting->status) }}
                            </span>
                        </div>

                        <a href="{{ route('meetings.show', $meeting) }}" class="text-decoration-none">
                            <h5 class="fw-600 text-body mb-1 meeting-title">{{ $meeting->title }}</h5>
                        </a>

                        <div class="text-muted fs-sm mb-3">
                            <i class="bi bi-calendar-event me-1"></i>{{ $meeting->meeting_date->format('D, M d, Y') }}
                            @if ($meeting->start_time)
                                · {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }}
                            @endif
                        </div>

                        <div class="mt-auto">
                            @if ($meeting->client || $meeting->project)
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @if ($meeting->client)
                                        <span class="badge badge-soft-info"><i class="bi bi-buildings me-1"></i>{{ $meeting->client->company_name }}</span>
                                    @endif
                                    @if ($meeting->project)
                                        <span class="badge badge-soft-primary"><i class="bi bi-folder2-open me-1"></i>{{ $meeting->project->name }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-xs text-muted">
                                    @if ($meeting->location)
                                        <i class="bi bi-geo-alt me-1"></i>{{ $meeting->location }}
                                    @elseif ($meeting->meeting_link)
                                        <i class="bi bi-camera-video me-1"></i>Online
                                    @endif
                                </span>
                                <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-sm btn-soft-primary">
                                    <i class="bi bi-arrow-right me-1"></i>Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <x-card>
                    <x-empty-state
                        title="No meetings found"
                        text="Schedule your first meeting to keep everyone aligned."
                        icon="bi-people"
                        :action="auth()->user()->can('meetings.create') ? 'javascript:void(0)' : null"
                        action-text="Schedule Meeting"
                    />
                </x-card>
            </div>
        @endforelse
    </div>

    @if ($meetings->hasPages())
        <div class="mt-4">{{ $meetings->links() }}</div>
    @endif

    @can('meetings.create')
        <div class="modal fade" id="meetingModal" tabindex="-1" aria-labelledby="meetingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="POST" action="{{ route('meetings.store') }}" data-loading>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="meetingModalLabel"><i class="bi bi-calendar-plus text-primary me-2"></i>Schedule Meeting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body meeting-modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-500">Meeting Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-lg" required maxlength="255" placeholder="e.g. Weekly project sync">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Meeting Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select form-select-lg meeting-select2" required>
                                        @foreach (\App\Models\Meeting::TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="meeting_date" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-500">Start</label>
                                    <input type="time" name="start_time" class="form-control form-control-lg">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-500">End</label>
                                    <input type="time" name="end_time" class="form-control form-control-lg">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Client</label>
                                    <select name="client_id" class="form-select form-select-lg meeting-select2" data-placeholder="Select client (optional)">
                                        <option value=""></option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Project</label>
                                    <select name="project_id" class="form-select form-select-lg meeting-select2" data-placeholder="Select project (optional)">
                                        <option value=""></option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Participants</label>
                                    <select name="participant_ids[]" class="form-select form-select-lg meeting-select2" multiple data-placeholder="Select participants">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Location</label>
                                    <input type="text" name="location" class="form-control form-control-lg" placeholder="Room 204 / Office HQ">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Meeting Link</label>
                                    <input type="url" name="meeting_link" class="form-control form-control-lg" placeholder="https://meet.example.com/xyz">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Description</label>
                                    <textarea name="description" class="form-control" rows="2" maxlength="5000" placeholder="Brief description of the meeting purpose..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Agenda</label>
                                    <textarea name="agenda" class="form-control" rows="2" maxlength="10000" placeholder="1. Project status&#10;2. Budget review&#10;3. Next steps"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-calendar-plus me-1"></i>Schedule Meeting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
