@extends('layouts.app')
@section('title', 'Project Financial Report')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.reports.index') }}">Financial Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Project Profitability</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Project Profitability</h1>
        <p class="text-muted mb-0">Financial performance of every project</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th class="text-end">Budget</th>
                    <th class="text-end">Invoiced</th>
                    <th class="text-end">Received</th>
                    <th class="text-end">Project Expenses</th>
                    <th class="text-end">Receivable</th>
                    <th class="text-end">Est. Profit</th>
                    <th class="text-end">Actual Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                @php
                    $invoiced = (float) ($project->total_invoiced ?? 0);
                    $received = (float) ($project->total_received ?? 0);
                    $expenses = (float) ($project->total_expenses ?? 0);
                    $receivable = max(0, $invoiced - $received);
                    $estProfit = $invoiced - $expenses;
                    $actualProfit = $received - $expenses;
                @endphp
                <tr>
                    <td data-label="Project">
                        <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-decoration-none">{{ $project->name }}</a>
                        <div class="text-muted small">{{ $project->project_code }}</div>
                    </td>
                    <td data-label="Client" class="text-muted small">{{ $project->client?->company_name ?? '—' }}</td>
                    <td data-label="Budget" class="text-end">{{ $project->budget ? setting('general.currency','PKR').' '.number_format($project->budget, 0) : '—' }}</td>
                    <td data-label="Invoiced" class="text-end fw-medium">{{ number_format($invoiced, 0) }}</td>
                    <td data-label="Received" class="text-end fin-amount-in">{{ number_format($received, 0) }}</td>
                    <td data-label="Project Expenses" class="text-end fin-amount-out">{{ number_format($expenses, 0) }}</td>
                    <td data-label="Receivable" class="text-end {{ $receivable > 0 ? 'fw-semibold text-warning' : 'text-muted' }}">{{ number_format($receivable, 0) }}</td>
                    <td data-label="Est. Profit" class="text-end {{ $estProfit >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">{{ number_format($estProfit, 0) }}</td>
                    <td data-label="Actual Profit" class="text-end {{ $actualProfit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ number_format($actualProfit, 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-kanban fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No projects found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
    <div class="card-footer">{{ $projects->links() }}</div>
    @endif
</div>
@endsection
