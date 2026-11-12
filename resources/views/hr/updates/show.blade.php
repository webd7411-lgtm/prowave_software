@extends('layouts.app')

@section('title', 'Daily Work Update')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hr.updates.index') }}">Daily Work Updates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Details</li>
                </ol>
            </nav>
            <h1 class="page-title">Daily Work Update</h1>
            <p class="page-subtitle">{{ format_date($update->date) }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('hr.updates.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-8">
            <x-card title="Report" icon="bi-journal-text">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <img src="{{ user_avatar($update->employee) }}" alt="" class="rounded-circle" style="width:48px;height:48px;object-fit:cover">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-600">{{ $update->employee?->name }}</div>
                        <div class="fs-xs text-muted">{{ $update->employee?->employee_id }} · {{ format_date($update->date) }}</div>
                    </div>
                    <div class="text-end">
                        <div><span class="badge badge-soft-{{ $update->status->color() }}">{{ $update->status->label() }}</span></div>
                        @if ($update->hours_worked)
                            <div class="fs-xs text-muted mt-1"><i class="bi bi-clock me-1"></i>{{ $update->hours_worked }} hr(s)</div>
                        @endif
                    </div>
                </div>

                <div class="vstack gap-4">
                    @if ($update->work_description)
                        <div>
                            <h6 class="section-label"><i class="bi bi-card-text me-2"></i>Work Description</h6>
                            <div class="fs-sm text-body">{{ $update->work_description }}</div>
                        </div>
                    @endif

                    @if ($update->tasks_completed)
                        <div>
                            <h6 class="section-label"><i class="bi bi-check2-square me-2"></i>Tasks Completed</h6>
                            <div class="fs-sm text-body">{{ $update->tasks_completed }}</div>
                        </div>
                    @endif

                    @if ($update->problems)
                        <div>
                            <h6 class="section-label"><i class="bi bi-exclamation-triangle me-2"></i>Problems / Challenges</h6>
                            <div class="fs-sm text-body">{{ $update->problems }}</div>
                        </div>
                    @endif

                    @if ($update->tomorrow_plan)
                        <div>
                            <h6 class="section-label"><i class="bi bi-calendar2-plus me-2"></i>Tomorrow's Plan</h6>
                            <div class="fs-sm text-body">{{ $update->tomorrow_plan }}</div>
                        </div>
                    @endif

                    @if ($update->additional_notes)
                        <div>
                            <h6 class="section-label"><i class="bi bi-paperclip me-2"></i>Additional Notes</h6>
                            <div class="fs-sm text-body">{{ $update->additional_notes }}</div>
                        </div>
                    @endif

                    @if (! $update->work_description && ! $update->tasks_completed && ! $update->problems && ! $update->tomorrow_plan && ! $update->additional_notes)
                        <div class="text-muted fs-sm">No content in this report.</div>
                    @endif
                </div>
            </x-card>

            @if ($update->review_remarks || $update->reviewer)
                <x-card title="Review" icon="bi-clipboard-check" class="mt-4">
                    <dl class="row mb-0 fs-sm">
                        <dt class="col-sm-3 text-muted">Reviewed By</dt>
                        <dd class="col-sm-9">{{ $update->reviewer?->name ?? '—' }}</dd>
                        <dt class="col-sm-3 text-muted">Reviewed At</dt>
                        <dd class="col-sm-9">{{ $update->reviewed_at ? $update->reviewed_at->format('M d, Y h:i A') : '—' }}</dd>
                        <dt class="col-sm-3 text-muted">Remarks</dt>
                        <dd class="col-sm-9">{{ $update->review_remarks ?: '—' }}</dd>
                    </dl>
                </x-card>
            @endif
        </div>

        <div class="col-12 col-lg-4">
            @can('review', $update)
                <x-card title="Review Report" icon="bi-pencil-square">
                    <form method="POST" action="{{ route('hr.updates.review', $update) }}" data-loading>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <div class="d-grid gap-2">
                                <label class="review-option">
                                    <input type="radio" name="status" value="approved" required class="form-check-input me-2">Approve
                                </label>
                                <label class="review-option">
                                    <input type="radio" name="status" value="reviewed" class="form-check-input me-2">Mark as Reviewed
                                </label>
                                <label class="review-option">
                                    <input type="radio" name="status" value="needs_revision" class="form-check-input me-2">Needs Revision
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review Remarks</label>
                            <textarea name="review_remarks" class="form-control" rows="3" placeholder="Optional note for the employee...">{{ old('review_remarks') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i>Submit Review</button>
                    </form>
                </x-card>
            @elsecan
                <x-card title="Review" icon="bi-clipboard-check">
                    <p class="fs-sm text-muted mb-0">The report has been reviewed.</p>
                </x-card>
            @endcan
        </div>
    </div>
@endsection