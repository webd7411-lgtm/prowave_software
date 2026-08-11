@extends('layouts.app')

@section('title', 'Client Revenue Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Client Revenue</li>
                </ol>
            </nav>
            <h1 class="page-title">Client Revenue</h1>
            <p class="page-subtitle">Invoiced versus received amounts per client in the selected period.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['report' => 'client-revenue', 'from' => $from?->toDateString(), 'to' => $to?->toDateString()]) }}"
               class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.client-revenue') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from?->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to?->toDateString() }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ route('reports.client-revenue') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <x-card>
        @if ($clients->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Client</th>
                        <th class="text-center">Invoices</th>
                        <th class="text-end">Invoiced</th>
                        <th class="text-end">Received</th>
                        <th class="text-end">Outstanding</th>
                        <th style="width: 20%;">Collection</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($clients as $client)
                        @php
                            $pct = $client->invoiced > 0 ? min(round(($client->received / $client->invoiced) * 100), 100) : 0;
                        @endphp
                        <tr>
                            <td class="fw-medium">
                                <a href="{{ route('crm.clients.show', $client) }}" class="text-decoration-none">{{ $client->company_name }}</a>
                            </td>
                            <td class="text-center">{{ $client->invoice_count }}</td>
                            <td class="text-end">{{ setting('general.currency', 'USD') }} {{ format_money($client->invoiced) }}</td>
                            <td class="text-end text-success">{{ setting('general.currency', 'USD') }} {{ format_money($client->received) }}</td>
                            <td class="text-end {{ ($client->invoiced - $client->received) > 0 ? 'text-danger' : '' }}">{{ setting('general.currency', 'USD') }} {{ format_money($client->invoiced - $client->received) }}</td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : '' }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $clients->links() }}</div>
        @else
            <x-empty-state icon="bi-currency-dollar" title="No client revenue in this period" />
        @endif
    </x-card>
@endsection
