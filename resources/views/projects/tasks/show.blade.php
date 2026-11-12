@extends('layouts.app')

@section('title', 'Task — ' . $task->title)

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <span class="badge bg-light text-muted border fs-xs">{{ $task->task_code }}</span>
                <span class="badge {{ $task->priority_badge_class }} fs-xs">{{ $task->priority }} Priority</span>
                <span class="badge {{ $task->status_badge_class }} fs-xs">{{ $task->status }}</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1">{{ $task->title }}</h1>
            <p class="text-muted mb-0 fs-sm">
                Project: <a href="{{ route('projects.show', $task->project) }}" class="fw-semibold text-primary text-decoration-none">{{ $task->project->name }}</a>

            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('projects.tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Tasks
            </a>
            <a href="{{ route('projects.tasks.kanban', $task->project) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-kanban me-1"></i> Kanban Board
            </a>
            <a href="{{ route('projects.show', $task->project) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-folder me-1"></i> Project Overview
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Task Details & Comments --}}
        <div class="col-12 col-lg-8">
            {{-- Description Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-card-text text-primary me-2"></i>Task Description & Instructions</h5>
                </div>
                <div class="card-body pt-0">
                    @if($task->description)
                        <div class="text-secondary fs-sm" style="white-space: pre-line;">{{ $task->description }}</div>
                    @else
                        <div class="text-muted fs-sm italic">No specific description provided for this task.</div>
                    @endif
                </div>
            </div>

            {{-- Task Attachments --}}
            @if($task->attachments->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-paperclip text-primary me-2"></i>Task Attachments</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-2">
                            @foreach($task->attachments as $att)
                                <div class="col-12 col-sm-6">
                                    <div class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-light">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                                            <div class="text-truncate">
                                                <div class="fw-semibold text-dark fs-xs text-truncate">{{ $att->name }}</div>
                                                <div class="text-muted fs-2xs">{{ $att->formatted_size }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('projects.files.download', $att) }}" class="btn btn-sm btn-light"><i class="bi bi-download"></i></a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Task Comments & Discussion Thread --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-left-text text-primary me-2"></i>Task Discussion & Comments</h5>
                </div>
                <div class="card-body pt-0">
                    {{-- Post Comment Form --}}
                    @can('task_comments.create')
                        <form action="{{ route('projects.tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf
                            <div class="mb-2">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Write a comment or issue update..." required></textarea>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <input type="file" name="attachment" class="form-control form-control-sm w-100 w-sm-auto">
                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                    <i class="bi bi-send me-1"></i> Post Comment
                                </button>
                            </div>
                        </form>
                    @endcan

                    {{-- Comments Feed --}}
                    <div class="comments-stream">
                        @forelse($task->comments as $comment)
                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light mb-3">
                                <div class="rounded-circle bg-primary text-white fs-6 fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px;">
                                    {{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark fs-sm">{{ $comment->user?->name }}</span>
                                        <span class="text-muted fs-2xs">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="fs-sm text-secondary mb-2" style="white-space: pre-line;">{{ $comment->comment }}</div>

                                    @if($comment->attachment_path)
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($comment->attachment_path) }}" target="_blank" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-paperclip"></i> {{ $comment->attachment_name ?? 'Attachment' }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($comment->user_id === auth()->id() || auth()->user()->hasAnyRole(['Super Admin', 'Administrator', 'Project Manager']))
                                    @can('task_comments.delete')
                                        <form action="{{ route('projects.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Delete comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger p-0 ms-2"><i class="bi bi-x-circle"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        @empty
                            <div class="text-muted text-center py-4 fs-sm">No comments yet. Start the discussion above.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Attributes --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i>Task Status & Properties</h5>
                </div>
                <div class="card-body pt-0 fs-sm">
                    {{-- Status Change --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Update Status</label>
                        <select class="form-select" id="detailTaskStatusSelect">
                            @foreach(['To Do', 'In Progress', 'Review', 'Testing', 'Completed', 'On Hold', 'Cancelled'] as $st)
                                <option value="{{ $st }}" {{ $task->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Assigned To</span>
                        <span class="fw-bold text-dark">{{ $task->assignedTo?->name ?? 'Unassigned' }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Assigned By</span>
                        <span class="fw-semibold text-dark">{{ $task->assignedBy?->name ?? 'N/A' }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Deadline</span>
                        <span class="fw-semibold {{ $task->deadline && $task->deadline->isPast() && $task->status !== 'Completed' ? 'text-danger fw-bold' : 'text-dark' }}">
                            {{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Estimated Hours</span>
                        <span class="fw-semibold text-dark">{{ number_format($task->estimated_hours, 1) }} hrs</span>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Actual Spent Hours</span>
                        <span class="fw-bold text-primary">{{ number_format($task->actual_hours, 1) }} hrs</span>
                    </div>
                </div>
            </div>

            {{-- Time Logs for Task --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock me-2 text-primary"></i>Task Time Entries</h5>
                </div>
                <div class="card-body pt-0 fs-xs">
                    @forelse($task->timeEntries as $time)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold text-dark">{{ $time->user?->name }}</div>
                                <div class="text-muted fs-2xs">{{ $time->created_at->format('M d, Y') }}</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary fw-bold">{{ $time->formatted_duration }}</span>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No time tracked specifically on this task.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('detailTaskStatusSelect');
        if (select) {
            select.addEventListener('change', function () {
                fetch(`{{ url('/projects/tasks/' . $task->id . '/status') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: this.value })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
