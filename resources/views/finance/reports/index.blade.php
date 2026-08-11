@extends('layouts.app')
@section('title', 'Financial Reports')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Financial Reports</li>
        </ol>
    </nav>
    <h1 class="page-title mb-1">Financial Reports</h1>
    <p class="text-muted mb-0">Professional reports for income, expenses, profitability and cash flow</p>
</div>

@php
$yearStart = now()->startOfYear()->toDateString();
$monthStart = now()->startOfMonth()->toDateString();
$today = now()->toDateString();
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.profit-loss') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <h6 class="mb-1">Profit & Loss</h6>
                    <p class="text-muted small mb-2">Total income, expenses, net profit and monthly comparison.</p>
                    <span class="badge bg-primary-soft rounded-pill">This year</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.expenses') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-receipt-cutoff"></i></div>
                <div>
                    <h6 class="mb-1">Expense Report</h6>
                    <p class="text-muted small mb-2">Detailed expense list with category breakdown and totals.</p>
                    <span class="badge bg-danger-soft rounded-pill">This month</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.income') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div>
                    <h6 class="mb-1">Income Report</h6>
                    <p class="text-muted small mb-2">All income records with source and category breakdown.</p>
                    <span class="badge bg-emerald-soft rounded-pill">This month</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.cash-flow') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-arrow-left-right"></i></div>
                <div>
                    <h6 class="mb-1">Cash Flow</h6>
                    <p class="text-muted small mb-2">Money in vs money out with cash and bank balances.</p>
                    <span class="badge bg-cyan-soft rounded-pill">This month</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.receivables.index') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <h6 class="mb-1">Client Receivables</h6>
                    <p class="text-muted small mb-2">Outstanding invoices, overdue days and client balances.</p>
                    <span class="badge bg-warning-soft rounded-pill">Live</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.project-profitability') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-kanban"></i></div>
                <div>
                    <h6 class="mb-1">Project Financial Report</h6>
                    <p class="text-muted small mb-2">Budget, invoiced, received and profitability per project.</p>
                    <span class="badge bg-primary-soft rounded-pill">All projects</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.office-expenses') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h6 class="mb-1">Office Expense Report</h6>
                    <p class="text-muted small mb-2">Non-project expenses with monthly comparison.</p>
                    <span class="badge bg-danger-soft rounded-pill">This month</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('crm.payments.index') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <h6 class="mb-1">Payment Report</h6>
                    <p class="text-muted small mb-2">All client payments recorded against invoices.</p>
                    <span class="badge bg-emerald-soft rounded-pill">CRM</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.vendors.index') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-shop"></i></div>
                <div>
                    <h6 class="mb-1">Vendor Payment Report</h6>
                    <p class="text-muted small mb-2">Vendor expenses, payments made and outstanding.</p>
                    <span class="badge bg-indigo-soft rounded-pill">Vendors</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.profit-loss', ['from_date' => $monthStart, 'to_date' => $today]) }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-calendar-month"></i></div>
                <div>
                    <h6 class="mb-1">Monthly Financial Summary</h6>
                    <p class="text-muted small mb-2">Income, expenses and profit for the current month.</p>
                    <span class="badge bg-cyan-soft rounded-pill">This month</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('finance.reports.profit-loss', ['from_date' => $yearStart, 'to_date' => $today]) }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-calendar-range"></i></div>
                <div>
                    <h6 class="mb-1">Yearly Financial Summary</h6>
                    <p class="text-muted small mb-2">Full year income, expenses and profit overview.</p>
                    <span class="badge bg-warning-soft rounded-pill">This year</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('crm.invoices.index') }}" class="card report-card text-decoration-none h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="report-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <h6 class="mb-1">Invoice Report</h6>
                    <p class="text-muted small mb-2">All invoices with totals, payments and balances.</p>
                    <span class="badge bg-primary-soft rounded-pill">Invoices</span>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
