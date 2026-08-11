@extends('layouts.app')
@section('title', 'Quotation ' . $quotation->quotation_number)
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('crm.quotations.index') }}">Quotations</a></li>
            <li class="breadcrumb-item active">{{ $quotation->quotation_number }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="page-title">{{ $quotation->quotation_number }}</h1>
            <p class="text-muted mb-0">Created {{ $quotation->created_at->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('crm.quotations.edit', $quotation) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('crm.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('crm.quotations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotation->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ $item->discount > 0 ? '-' . number_format($item->discount, 2) : '—' }}</td>
                                <td class="text-end">{{ $item->tax > 0 ? number_format($item->tax, 2) : '—' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No items</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Subtotal</td>
                                <td class="text-end fw-semibold">{{ number_format($quotation->subtotal, 2) }}</td>
                            </tr>
                            @if($quotation->discount > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Discount</td>
                                <td class="text-end fw-semibold text-danger">-{{ number_format($quotation->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($quotation->tax > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Tax</td>
                                <td class="text-end fw-semibold">{{ number_format($quotation->tax, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-active">
                                <td colspan="5" class="text-end fw-bold fs-5">Grand Total</td>
                                <td class="text-end fw-bold fs-5">{{ number_format($quotation->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($quotation->notes || $quotation->terms)
        <div class="card mt-4">
            <div class="card-body p-4">
                <div class="row g-4">
                    @if($quotation->notes)
                    <div class="col-12 col-sm-6">
                        <h6 class="fw-semibold mb-2">Notes</h6>
                        <p class="text-muted mb-0">{{ $quotation->notes }}</p>
                    </div>
                    @endif
                    @if($quotation->terms)
                    <div class="col-12 col-sm-6">
                        <h6 class="fw-semibold mb-2">Terms & Conditions</h6>
                        <p class="text-muted mb-0">{{ $quotation->terms }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Details</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status</span>
                    @php
                        $statusColors = ['draft'=>'secondary','sent'=>'primary','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
                    @endphp
                    <span class="badge badge-soft-{{ $statusColors[$quotation->status] ?? 'secondary' }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Date</span>
                    <span>{{ $quotation->quotation_date->format('M d, Y') }}</span>
                </div>
                @if($quotation->valid_until)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Valid Until</span>
                    <span>{{ $quotation->valid_until->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Client</h6>
                <div class="mb-1 fw-semibold">{{ $quotation->client->company_name }}</div>
                @if($quotation->client->contact_person)
                    <div class="text-muted small">{{ $quotation->client->contact_person }}</div>
                @endif
                @if($quotation->client->email)
                    <div class="text-muted small">{{ $quotation->client->email }}</div>
                @endif
                @if($quotation->client->phone)
                    <div class="text-muted small">{{ $quotation->client->phone }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
