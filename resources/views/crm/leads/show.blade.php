@extends('layouts.app')
@section('title', $lead->company_name)
@section('content')
<div class="page-header">
    <div>
        <div class="fs-xs text-muted">{{ $lead->lead_number }}</div>
        <h1 class="page-title">{{ $lead->company_name }}</h1>
        <p class="page-subtitle">{{ $lead->contact_person ?: 'No contact person' }} · {{ $lead->email ?: $lead->phone }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('crm.leads.index') }}" class="btn btn-outline"><i class="bi bi-arrow-left me-1"></i>Back to Leads</a>
        @can('leads.edit')
            <a href="{{ route('crm.leads.edit', $lead) }}" class="btn btn-soft-primary">Edit</a>
        @endcan
        @if($lead->phone)
            <a href="tel:{{ $lead->phone }}" class="btn btn-soft-primary"><i class="bi bi-telephone"></i></a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <x-card title="Lead information" body-class="p-3">
            <div class="row g-3 fs-sm">
                <div class="col-6">
                    <span class="text-muted d-block">Status</span>
                    <span class="badge" style="background:{{ $statusColors[$lead->status] ?? '#6c757d' }}">{{ ucwords(str_replace('_', ' ', $lead->status)) }}</span>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Priority</span>{{ ucfirst($lead->priority) }}
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Source</span>{{ ucfirst($lead->source) }}
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Estimated budget</span>{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Owner</span>{{ $lead->owner?->name ?? 'Unassigned' }}
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Expected close</span>{{ $lead->expected_closing_date?->format('M d, Y') ?? '—' }}
                </div>
                <div class="col-12">
                    <span class="text-muted d-block">Notes</span>{{ $lead->notes ?: 'No notes added.' }}
                </div>
            </div>
        </x-card>

        <x-card title="Activity timeline" class="mt-3" body-class="p-3">
            @forelse($lead->activities as $activity)
                <div class="border-start ps-3 pb-3">
                    <div class="fw-600 fs-sm">{{ $activity->subject }}</div>
                    <div class="fs-xs text-muted">{{ $activity->created_at->diffForHumans() }} · {{ $activity->author?->name }}</div>
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
                <input type="date" name="follow_up_date" class="form-control mb-2" required>
                <select name="type" class="form-select mb-2">
                    @foreach(['call', 'email', 'meeting', 'other'] as $t)
                        <option>{{ $t }}</option>
                    @endforeach
                </select>
                <textarea name="notes" class="form-control mb-2" placeholder="Notes"></textarea>
                <button class="btn btn-primary w-100">Add Follow-up</button>
            </form>
        </x-card>

        <x-card title="Convert" class="mt-3" body-class="p-3">
            @if(!$lead->converted_client_id)
                <p class="fs-sm text-muted">Creates a client record while preserving this complete lead history.</p>
                <form method="POST" action="{{ route('crm.leads.convert', $lead) }}">
                    @csrf
                    <button class="btn btn-soft-success w-100" data-confirm data-confirm-title="Convert this lead?">Convert to Client</button>
                </form>
            @else
                <a href="{{ route('crm.clients.show', $lead->client) }}" class="btn btn-soft-primary w-100">View Client</a>
            @endif
        </x-card>
    </div>
</div>
@endsection