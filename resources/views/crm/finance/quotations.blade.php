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
            <i class="bi bi-plus-circle me-1"></i>Add Quotation
        </a>
        <a href="{{ route('crm.dashboard') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Back to CRM
        </a>
    </div>
</div>

<x-card body-class="p-0">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Quotation</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Valid Until</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
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
                    <td>{{ $record->quotation_date->format('M d, Y') }}</td>
                    <td>{{ $record->valid_until?->format('M d, Y') ?? '—' }}</td>
                    <td class="fw-semibold">{{ number_format($record->total, 2) }}</td>
                    <td>
                        @php
                            $statusColors = ['draft'=>'secondary','sent'=>'primary','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
                        @endphp
                        <span class="badge badge-soft-{{ $statusColors[$record->status] ?? 'secondary' }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('crm.quotations.show', $record) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="{{ route('crm.quotations.edit', $record) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('crm.quotations.destroy', $record) }}" onsubmit="return confirm('Delete this quotation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No quotations created yet.
                        <br>
                        <a href="{{ route('crm.quotations.create') }}" class="btn btn-sm btn-primary mt-2">
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
