@extends('layouts.app')
@section('title', $client->company_name)
@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('crm.clients.index') }}">Clients</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $client->company_name }}</li>
            </ol>
        </nav>
        <div class="fs-xs text-muted">{{ $client->client_number }}</div>
        <h1 class="page-title">{{ $client->company_name }}</h1>
        <p class="page-subtitle text-break">{{ $client->contact_person }} &middot; {{ $client->email }} &middot; {{ $client->phone ?: 'No phone' }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('crm.clients.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Clients</a>
        <button type="button" onclick="printUrlInHiddenFrame('{{ route('crm.clients.ledger', $client) }}')" class="btn btn-outline-primary btn-sm"><i class="bi bi-journal-text me-1"></i>Print Statement / Ledger</button>
        @can('clients.edit')
            <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit Client</a>
        @endcan
        @can('clients.delete')
            <form action="{{ route('crm.clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Delete Client
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <x-card body-class="p-3 shadow-sm border-0">
            <div class="fs-xs text-muted text-uppercase fw-semibold">Total Billable / Budgets</div>
            <div class="fs-5 fs-sm-4 fw-bold text-dark mt-1 text-break">{{ number_format($client->ledger->sum('debit'), 2) }} <span class="fs-xs fw-normal text-muted">PKR</span></div>
        </x-card>
    </div>
    <div class="col-6 col-lg-3">
        <x-card body-class="p-3 shadow-sm border-0">
            <div class="fs-xs text-muted text-uppercase fw-semibold">Total Paid</div>
            <div class="fs-5 fs-sm-4 fw-bold text-success mt-1 text-break">{{ number_format($client->payments->sum('amount'), 2) }} <span class="fs-xs fw-normal text-muted">PKR</span></div>
        </x-card>
    </div>
    <div class="col-6 col-lg-3">
        <x-card body-class="p-3 shadow-sm border-0">
            <div class="fs-xs text-muted text-uppercase fw-semibold">Net Outstanding</div>
            <div class="fs-5 fs-sm-4 fw-bold {{ $client->outstanding() > 0 ? 'text-danger' : 'text-success' }} mt-1 text-break">{{ number_format($client->outstanding(), 2) }} <span class="fs-xs fw-normal text-muted">PKR</span></div>
        </x-card>
    </div>
    <div class="col-6 col-lg-3">
        <x-card body-class="p-3 shadow-sm border-0">
            <div class="fs-xs text-muted text-uppercase fw-semibold">Account Manager</div>
            <div class="fs-6 fs-sm-5 fw-bold text-dark mt-1 text-break">{{ $client->manager?->name ?? '—' }}</div>
        </x-card>
    </div>
</div>

{{-- Associated Projects Overview --}}
@if($client->projects->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban text-primary me-2"></i>Client Projects</h5>
            <span class="badge bg-light text-dark border">{{ $client->projects->count() }} Projects</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 fs-sm">
                <thead class="table-light">
                    <tr>
                        <th>Project Code</th>
                        <th>Project Name</th>
                        <th>Status</th>
                        <th class="text-end">Budget</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Remaining Balance</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->projects as $proj)
                        <tr>
                            <td data-label="Project Code" class="fw-semibold text-primary">{{ $proj->project_code }}</td>
                            <td data-label="Project Name" class="fw-bold">{{ $proj->name }}</td>
                            <td data-label="Status"><span class="badge {{ $proj->status_badge_class }}">{{ $proj->status }}</span></td>
                            <td data-label="Budget" class="text-end fw-semibold">{{ number_format($proj->budget, 2) }} {{ $proj->currency ?: 'PKR' }}</td>
                            <td data-label="Paid" class="text-end text-success fw-semibold">{{ number_format($proj->paid_amount, 2) }} {{ $proj->currency ?: 'PKR' }}</td>
                            <td data-label="Remaining" class="text-end text-danger fw-bold">{{ number_format($proj->remaining_balance, 2) }} {{ $proj->currency ?: 'PKR' }}</td>
                            <td data-label="Action" class="text-end fin-cell-actions">
                                <a href="{{ route('projects.show', $proj) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Client Financial Ledger --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-check text-success me-2"></i>Client Financial Ledger</h5>
            <span class="fs-xs text-muted">Complete financial transaction log (Projects, Invoices & Payments)</span>
        </div>
        <button type="button" onclick="printUrlInHiddenFrame('{{ route('crm.clients.ledger', $client) }}')" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i> Print Statement</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-sm">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-end">Debit (+)</th>
                    <th class="text-end">Credit (-)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->ledger as $entry)
                    <tr>
                        <td data-label="Date" class="fw-semibold">{{ $entry->transaction_date->format('M d, Y') }}</td>
                        <td data-label="Reference"><span class="badge bg-light text-dark border">{{ $entry->reference }}</span></td>
                        <td data-label="Description">{{ $entry->description }}</td>
                        <td data-label="Debit (+)" class="text-end fw-semibold text-danger">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}</td>
                        <td data-label="Credit (-)" class="text-end fw-semibold text-success">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}</td>
                        <td data-label="Balance" class="text-end fw-bold fs-6 {{ $entry->balance > 0 ? 'text-dark' : 'text-success' }}">{{ number_format($entry->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No ledger transactions recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="3" class="text-end text-uppercase">Totals:</td>
                    <td data-label="Total Debit" class="text-end text-danger fs-6">{{ number_format($client->ledger->sum('debit'), 2) }}</td>
                    <td data-label="Total Credit" class="text-end text-success fs-6">{{ number_format($client->ledger->sum('credit'), 2) }}</td>
                    <td data-label="Outstanding" class="text-end text-primary fs-5">{{ number_format($client->outstanding(), 2) }} PKR</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

