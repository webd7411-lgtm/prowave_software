@extends('layouts.app')

@section('title', 'Invoice Aging Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Aging</li>
                </ol>
            </nav>
            <h1 class="page-title">Invoice Aging</h1>
            <p class="page-subtitle">Outstanding receivables grouped by how long they have been overdue.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['report' => 'invoice-aging']) }}" class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach ($aging as $bucket)
            @php
                $tone = match ($bucket['label']) {
                    'current' => 'bg-primary-subtle text-primary-emphasis',
                    '1-30' => 'bg-success-subtle text-success-emphasis',
                    '31-60' => 'bg-warning-subtle text-warning-emphasis',
                    '61-90' => 'bg-orange-subtle text-dark',
                    default => 'bg-danger-subtle text-danger-emphasis',
                };
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('reports.invoice-aging', ['aging' => $bucket['label']]) }}" class="text-decoration-none">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-label">{{ $bucket['label'] === 'current' ? 'Current' : $bucket['label'].' days' }}</div>
                            <div class="stat-value">{{ setting('general.currency', 'USD') }} {{ format_money($bucket['amount']) }}</div>
                            <span class="badge {{ $tone }}">{{ $bucket['count'] }} invoice(s)</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <x-card>
        @if ($invoices->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Due Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-center">Overdue</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invoices as $invoice)
                        @php
                            $daysOverdue = $invoice->due_date && $invoice->due_date->isPast() ? $invoice->due_date->diffInDays(now()) : 0;
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client?->company_name ?? '—' }}</td>
                            <td>{{ format_date($invoice->due_date?->toDateString()) }}</td>
                            <td class="text-end">{{ format_money($invoice->total) }}</td>
                            <td class="text-end text-success">{{ format_money($invoice->paid_amount) }}</td>
                            <td class="text-end fw-semibold text-danger">{{ format_money($invoice->total - $invoice->paid_amount) }}</td>
                            <td class="text-center">
                                @if ($daysOverdue > 0)
                                    <span class="badge bg-danger-subtle text-danger-emphasis">{{ $daysOverdue }}d</span>
                                @else
                                    <span class="badge bg-success-subtle text-success-emphasis">On time</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $invoices->links() }}</div>
        @else
            <x-empty-state icon="bi-hourglass-split" title="No outstanding invoices" subtitle="Nothing matches this aging bucket. Great work!" />
        @endif
    </x-card>
@endsection
