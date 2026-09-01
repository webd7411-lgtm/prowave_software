@extends('layouts.app')
@section('title', 'Quotations')
@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quotations</li>
            </ol>
        </nav>
        <h1 class="page-title">Quotations</h1>
        <p class="page-subtitle">Create proposals that move opportunities forward.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('crm.quotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>New Quotation
        </a>
        <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to CRM
        </a>
    </div>
</div>

<x-card body-class="p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Quotation #</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Valid Until</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>
                        <a href="{{ route('crm.quotations.show', $record) }}" class="fw-semibold text-decoration-none">
                            {{ $record->quotation_number }}
                        </a>
                    </td>
                    <td>{{ $record->client->company_name }}</td>
                    <td>{{ $record->quotation_date->format('d M, Y') }}</td>
                    <td>{{ $record->valid_until?->format('d M, Y') ?? '—' }}</td>
                    <td class="text-end fw-semibold">{{ number_format($record->total, 2) }} PKR</td>
                    <td>
                        @php
                            $statusColors = ['draft'=>'secondary','sent'=>'primary','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
                        @endphp
                        <span class="badge badge-soft-{{ $statusColors[$record->status] ?? 'secondary' }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1 flex-nowrap">
                            <a href="{{ route('crm.quotations.show', $record) }}"
                               class="btn btn-sm btn-soft-primary" title="View">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                            <a href="{{ route('crm.quotations.print', $record) }}"
                               class="btn btn-sm btn-soft-success" title="Print Slip" target="_blank">
                                <i class="bi bi-printer me-1"></i>Print
                            </a>
                            <a href="{{ route('crm.quotations.edit', $record) }}"
                               class="btn btn-sm btn-soft-warning" title="Edit">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('crm.quotations.destroy', $record) }}"
                                  onsubmit="return confirm('Delete quotation {{ $record->quotation_number }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-text display-6 d-block mb-2 opacity-25"></i>
                        No quotations created yet.
                        <br>
                        <a href="{{ route('crm.quotations.create') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Create First Quotation
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer">
        {{ $records->links() }}
    </div>
    @endif
</x-card>
@endsection
