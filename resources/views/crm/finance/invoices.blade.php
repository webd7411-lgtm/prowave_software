@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Invoices</li>
            </ol>
        </nav>
        <h1 class="page-title">Invoices</h1>
        <p class="page-subtitle">Track invoice status, due dates and collections.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('crm.invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>New Invoice
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
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Due</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>
                        <a href="{{ route('crm.invoices.show', $record) }}" class="fw-semibold text-decoration-none">
                            {{ $record->invoice_number }}
                        </a>
                    </td>
                    <td>{{ $record->client->company_name }}</td>
                    <td>{{ $record->invoice_date->format('d M, Y') }}</td>
                    <td>{{ $record->due_date?->format('d M, Y') ?? '—' }}</td>
                    <td class="text-end fw-semibold">{{ number_format($record->total, 2) }}</td>
                    <td class="text-end text-success fw-semibold">{{ number_format($record->paid_amount, 2) }}</td>
                    <td>
                        @php
                            $sc = ['unpaid'=>'danger','partially_paid'=>'warning','paid'=>'success','cancelled'=>'secondary'];
                        @endphp
                        <span class="badge badge-soft-{{ $sc[$record->status] ?? 'secondary' }}">
                            {{ ucwords(str_replace('_',' ',$record->status)) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1 flex-nowrap">
                            <a href="{{ route('crm.invoices.show', $record) }}"
                               class="btn btn-sm btn-soft-primary" title="View">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                            <a href="{{ route('crm.invoices.print', $record) }}"
                               class="btn btn-sm btn-soft-success" title="Print Slip" target="_blank">
                                <i class="bi bi-printer me-1"></i>Print
                            </a>
                            <a href="{{ route('crm.invoices.edit', $record) }}"
                               class="btn btn-sm btn-soft-warning" title="Edit">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('crm.invoices.destroy', $record) }}"
                                  onsubmit="return confirm('Delete invoice {{ $record->invoice_number }}?')">
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
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-receipt display-6 d-block mb-2 opacity-25"></i>
                        No invoices created yet.
                        <br>
                        <a href="{{ route('crm.invoices.create') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Create First Invoice
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
