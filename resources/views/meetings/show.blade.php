@extends('layouts.app')

@section('title', $meeting->title)

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('meetings.index') }}">Meetings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $meeting->title }}</li>
                </ol>
            </nav>
            <h1 class="page-title">{{ $meeting->title }}</h1>
            <p class="page-subtitle">{{ \App\Models\Meeting::TYPES[$meeting->type] ?? ucfirst($meeting->type) }} · {{ $meeting->meeting_date->format('D, M d, Y') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('meetings.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i>Back</a>
            @can('meetings.edit')
                <button type="button" class="btn btn-soft-primary" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
            @endcan
            @can('meetings.delete')
                <button type="button" class="btn btn-soft-danger" data-confirm="Delete this meeting permanently?"
                    data-form-submit="#delete-meeting-form">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                <form id="delete-meeting-form" method="POST" action="{{ route('meetings.destroy', $meeting) }}" class="d-none">@csrf @method('DELETE')</form>
            @endcan
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-4">
            <x-card title="Meeting Details" icon="bi-calendar3">
                <ul class="list-unstyled mb-0 details-list">
                    <li>
                        <i class="bi bi-tag text-primary"></i>
                        <span class="text-muted">Type</span>
                        <span class="fw-500">{{ \App\Models\Meeting::TYPES[$meeting->type] ?? ucfirst($meeting->type) }}</span>
                    </li>
                    <li>
                        <i class="bi bi-calendar-event text-primary"></i>
                        <span class="text-muted">Date</span>
                        <span class="fw-500">{{ $meeting->meeting_date->format('D, M d, Y') }}</span>
                    </li>
                    <li>
                        <i class="bi bi-clock text-primary"></i>
                        <span class="text-muted">Time</span>
                        <span class="fw-500">{{ $meeting->start_time ? \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') : '—' }} – {{ $meeting->end_time ? \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') : '—' }}</span>
                    </li>
                    @if ($meeting->location)
                        <li>
                            <i class="bi bi-geo-alt text-primary"></i>
                            <span class="text-muted">Location</span>
                            <span class="fw-500">{{ $meeting->location }}</span>
                        </li>
                    @endif
                    @if ($meeting->meeting_link)
                        <li>
                            <i class="bi bi-camera-video text-primary"></i>
                            <span class="text-muted">Link</span>
                            <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener" class="fw-500 text-primary">Join meeting <i class="bi bi-box-arrow-up-right fs-xs"></i></a>
                        </li>
                    @endif
                    @if ($meeting->client)
                        <li>
                            <i class="bi bi-buildings text-primary"></i>
                            <span class="text-muted">Client</span>
                            <a href="{{ route('crm.clients.show', $meeting->client) }}" class="fw-500 text-primary">{{ $meeting->client->company_name }}</a>
                        </li>
                    @endif
                    @if ($meeting->project)
                        <li>
                            <i class="bi bi-folder2-open text-primary"></i>
                            <span class="text-muted">Project</span>
                            <a href="{{ route('projects.show', $meeting->project) }}" class="fw-500 text-primary">{{ $meeting->project->name }}</a>
                        </li>
                    @endif
                    <li>
                        <i class="bi bi-person text-primary"></i>
                        <span class="text-muted">Organizer</span>
                        <span class="fw-500">{{ $meeting->creator?->name ?? '—' }}</span>
                    </li>
                </ul>
            </x-card>

            <x-card title="Participants ({{ $participants->count() }})" icon="bi-people" class="mt-3">
                @if ($participants->isNotEmpty())
                    <div class="d-flex flex-column gap-2">
                        @foreach ($participants as $participant)
                            <div class="d-flex align-items-center gap-2">
                                @if ($participant->avatar_url)
                                    <img src="{{ $participant->avatar_url }}" class="avatar-sm rounded-circle" alt="">
                                @else
                                    <span class="avatar-fallback sm">{{ initials($participant->name) }}</span>
                                @endif
                                <span class="fw-500">{{ $participant->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 fs-sm">No participants listed.</p>
                @endif
            </x-card>
        </div>

        <div class="col-12 col-xl-8">
            @if ($meeting->agenda)
                <x-card title="Agenda" icon="bi-list-check" class="mb-3">
                    <div class="preserve-lines text-body">{!! nl2br(e($meeting->agenda)) !!}</div>
                </x-card>
            @endif

            @if ($meeting->description)
                <x-card title="Description" icon="bi-card-text" class="mb-3">
                    <div class="text-body">{{ $meeting->description }}</div>
                </x-card>
            @endif

            @can('meetings.edit')
                <x-card title="Meeting Notes & Outcomes" subtitle="Record notes, decisions and action items after the meeting" icon="bi-journal-text" class="mb-3">
                    <form method="POST" action="{{ route('meetings.notes', $meeting) }}" data-loading>
                        @csrf @method('PATCH')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    @foreach (\App\Models\Meeting::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ $meeting->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date</label>
                                <input type="date" name="follow_up_date" value="{{ $meeting->follow_up_date ? ($meeting->follow_up_date instanceof \DateTimeInterface ? $meeting->follow_up_date->format('Y-m-d') : \Carbon\Carbon::parse($meeting->follow_up_date)->format('Y-m-d')) : '' }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Meeting Notes</label>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Summary of what was discussed...">{{ $meeting->notes }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Decisions</label>
                                <textarea name="decisions" class="form-control" rows="3" placeholder="Key decisions made...">{{ $meeting->decisions }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Action Items</label>
                                <textarea name="action_items" class="form-control" rows="3" placeholder="Who does what, by when...">{{ $meeting->action_items }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save Notes</button>
                            </div>
                        </div>
                    </form>
                </x-card>
            @else
                @if ($meeting->notes || $meeting->decisions || $meeting->action_items)
                    <x-card title="Meeting Notes & Outcomes" icon="bi-journal-text" class="mb-3">
                        @if ($meeting->notes)
                            <h6 class="fw-600">Notes</h6>
                            <div class="preserve-lines text-body mb-3">{!! nl2br(e($meeting->notes)) !!}</div>
                        @endif
                        @if ($meeting->decisions)
                            <h6 class="fw-600">Decisions</h6>
                            <div class="preserve-lines text-body mb-3">{!! nl2br(e($meeting->decisions)) !!}</div>
                        @endif
                        @if ($meeting->action_items)
                            <h6 class="fw-600">Action Items</h6>
                            <div class="preserve-lines text-body">{!! nl2br(e($meeting->action_items)) !!}</div>
                        @endif
                    </x-card>
                @endif
            @endcan
        </div>
    </div>

    @can('meetings.edit')
        <div class="modal fade" id="editMeetingModal" tabindex="-1" aria-labelledby="editMeetingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="POST" action="{{ route('meetings.update', $meeting) }}" data-loading>
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMeetingModalLabel"><i class="bi bi-calendar3 text-primary me-2"></i>Edit Meeting</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meeting Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required value="{{ $meeting->title }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Meeting Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        @foreach (\App\Models\Meeting::TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ $meeting->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        @foreach (\App\Models\Meeting::STATUSES as $key => $label)
                                            <option value="{{ $key }}" {{ $meeting->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="meeting_date" class="form-control" required value="{{ $meeting->meeting_date->format('Y-m-d') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="start_time" class="form-control" value="{{ $meeting->start_time }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="end_time" class="form-control" value="{{ $meeting->end_time }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" value="{{ $meeting->location }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Meeting Link</label>
                                    <input type="url" name="meeting_link" class="form-control" value="{{ $meeting->meeting_link }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Client</label>
                                    <select name="client_id" class="form-select select2" data-placeholder="Select client">
                                        <option value=""></option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ $meeting->client_id === $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Project</label>
                                    <select name="project_id" class="form-select select2" data-placeholder="Select project">
                                        <option value=""></option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}" {{ $meeting->project_id === $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-500 mb-0">Select Participants</label>
                                        <span class="fs-xs text-muted" id="edit_selected_count">{{ count((array) ($meeting->participant_ids ?? [])) }} selected</span>
                                    </div>
                                    <div class="card p-2 border shadow-none bg-body-tertiary">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                            <input type="text" class="form-control form-control-sm border-start-0 participant-search-input" data-target="#edit_participants_list" placeholder="Search team member by name...">
                                        </div>
                                        <div class="participant-scroll-list overflow-auto pe-1" id="edit_participants_list" style="max-height: 180px;">
                                            @foreach ($users as $user)
                                                @php $checked = in_array($user->id, (array) ($meeting->participant_ids ?? [])); @endphp
                                                <div class="participant-item d-flex align-items-center p-2 rounded hover-bg-light border mb-1 bg-surface" data-name="{{ strtolower($user->name) }}">
                                                    <div class="form-check m-0 w-100 d-flex align-items-center justify-content-between cursor-pointer">
                                                        <div class="d-flex align-items-center">
                                                            <input class="form-check-input me-2 participant-checkbox" type="checkbox" name="participant_ids[]" value="{{ $user->id }}" id="edit_user_{{ $user->id }}" {{ $checked ? 'checked' : '' }} data-counter="#edit_selected_count">
                                                            <label class="form-check-label fw-500 cursor-pointer text-body mb-0" for="edit_user_{{ $user->id }}">
                                                                {{ $user->name }}
                                                            </label>
                                                        </div>
                                                        <span class="badge bg-secondary-soft text-muted fs-xs">{{ $user->job_title ?: ($user->relationLoaded('roles') && $user->roles->first() ? $user->roles->first()->name : 'Staff') }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Agenda</label>
                                    <textarea name="agenda" class="form-control" rows="3">{{ $meeting->agenda }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $meeting->description }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
