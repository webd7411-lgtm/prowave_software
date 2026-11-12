@extends('layouts.app')
@section('title', $lead->company_name)
@section('content')
<div class="page-header">
    <div>
        <div class="fs-xs text-muted">{{ $lead->lead_number }}</div>
        <h1 class="page-title">{{ $lead->company_name }}</h1>
        <p class="page-subtitle">{{ $lead->contact_person ?: 'No contact person' }} &middot; {{ $lead->email ?: $lead->phone }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('crm.leads.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i>Back to Leads</a>
        @can('leads.edit')
            <a href="{{ route('crm.leads.edit', $lead) }}" class="btn btn-soft-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endcan
        @if($lead->phone)
            <a href="tel:{{ $lead->phone }}" class="btn btn-soft-primary"><i class="bi bi-telephone me-1"></i>Call</a>
        @endif
        @can('leads.delete')
            <form method="POST" action="{{ route('crm.leads.destroy', $lead) }}" onsubmit="return confirm('Are you sure you want to delete this lead?')" class="d-inline m-0">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-soft-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <x-card title="Lead information" body-class="p-3">
            <div class="row g-3 fs-sm">
                <div class="col-12 col-sm-6">
                    <span class="text-muted d-block mb-1">Status</span>
                    <span class="badge" style="background:{{ $statusColors[$lead->status] ?? '#6c757d' }}">{{ ucwords(str_replace('_', ' ', $lead->status)) }}</span>
                    @if(in_array($lead->status, ['meeting_scheduled', 'call_again']))
                        @php
                            $fu = $lead->followups->firstWhere('status', 'scheduled') ?? $lead->followups->first();
                        @endphp
                        @if($fu && $fu->follow_up_date)
                            <div class="fs-xs text-muted mt-1">
                                <i class="bi bi-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}
                                @if($fu->follow_up_time)
                                    {{ \Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
                <div class="col-12 col-sm-6">
                    <span class="text-muted d-block">Priority</span>{{ ucfirst($lead->priority) }}
                </div>

                <div class="col-12 col-sm-6">
                    <span class="text-muted d-block">Estimated budget</span>{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}
                </div>
                <div class="col-12 col-sm-6">
                    <span class="text-muted d-block">Owner</span>
                    @if($canAssign ?? false)
                        <form method="POST" action="{{ route('crm.leads.assign', $lead) }}" class="d-flex align-items-center gap-1 mt-1">
                            @csrf @method('PATCH')
                            <select name="owner_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 100%;">
                                <option value="">Unassigned</option>
                                @foreach($salesTeam as $u)
                                    <option value="{{ $u->id }}" @selected($lead->owner_id == $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span class="fw-semibold">{{ $lead->owner?->name ?? 'Unassigned' }}</span>
                    @endif
                </div>
                <div class="col-12 col-sm-6">
                    <span class="text-muted d-block">Expected close</span>{{ $lead->expected_closing_date?->format('M d, Y') ?? '—' }}
                </div>
                <div class="col-12">
                    <span class="text-muted d-block">Notes</span><span class="text-break">{{ $lead->notes ?: 'No notes added.' }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="Follow-up history & Schedule" class="mt-3" body-class="p-3">
            @forelse($lead->followups as $fu)
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 border-bottom pb-2 mb-2">
                    <div>
                        <div class="fw-600 fs-sm text-capitalize">
                            <i class="bi bi-calendar-event me-1 text-primary"></i>{{ ucfirst($fu->type) }}
                            &middot; {{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}
                            @if($fu->follow_up_time)
                                at {{ \Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                            @endif
                        </div>
                        @if($fu->notes)
                            <div class="fs-xs text-muted mt-1">{{ $fu->notes }}</div>
                        @endif
                        <div class="fs-xs text-muted mt-1">Assigned to: {{ $fu->assignee?->name ?? 'Unassigned' }}</div>
                    </div>
                    <span class="badge badge-soft-{{ $fu->status === 'completed' ? 'success' : ($fu->status === 'scheduled' ? 'warning' : 'secondary') }} flex-shrink-0">{{ ucfirst($fu->status) }}</span>
                </div>
            @empty
                <div class="text-muted fs-sm">No follow-ups recorded yet.</div>
            @endforelse
        </x-card>

        <x-card title="Activity timeline" class="mt-3" body-class="p-3">
            @forelse($lead->activities as $activity)
                <div class="border-start ps-3 pb-3">
                    <div class="fw-600 fs-sm">{{ $activity->subject }}</div>
                    <div class="fs-xs text-muted">{{ $activity->created_at->diffForHumans() }} &middot; {{ $activity->author?->name }}</div>
                </div>
            @empty
                <div class="text-muted fs-sm">Activity will appear here as the lead progresses.</div>
            @endforelse
        </x-card>
    </div>

    <div class="col-lg-4">
        <x-card title="Schedule follow-up" body-class="p-3">
            <form method="POST" action="{{ route('crm.leads.followups.store', $lead) }}">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-7">
                        <label class="form-label fs-xs text-muted mb-1">Date <span class="text-danger">*</span></label>
                        <input type="date" name="follow_up_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 col-sm-5">
                        <label class="form-label fs-xs text-muted mb-1">Time</label>
                        <input type="time" name="follow_up_time" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fs-xs text-muted mb-1">Type</label>
                    <select name="type" class="form-select text-capitalize">
                        @foreach(['call', 'email', 'meeting', 'other'] as $t)
                            <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label fs-xs text-muted mb-1">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Add follow-up details..."></textarea>
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-calendar-plus me-1"></i>Add Follow-up</button>
            </form>
        </x-card>

        <x-card title="Convert" class="mt-3" body-class="p-3">
            @if(!$lead->converted_client_id)
                <p class="fs-sm text-muted">Creates a client record while preserving this complete lead history.</p>
                <form method="POST" action="{{ route('crm.leads.convert', $lead) }}">
                    @csrf
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to convert this lead to a Client?');"><i class="bi bi-person-check me-1"></i>Convert to Client</button>
                </form>
            @else
                <a href="{{ route('crm.clients.show', $lead->client) }}" class="btn btn-soft-primary w-100"><i class="bi bi-person-lines-fill me-1"></i>View Client</a>
            @endif
        </x-card>
    </div>
</div>
@endsection