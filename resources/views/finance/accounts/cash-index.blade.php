@extends('layouts.app')
@section('title', 'Cash Accounts')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Cash Accounts</h1>
        <p class="text-muted mb-0">Office cash, petty cash and other cash holdings</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('accounts.create')
        <a href="{{ route('finance.accounts.cash.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Cash Account
        </a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-cash">
            <div class="fin-kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="fin-kpi-label">Total Cash Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum('current_balance'), 2) }}</div>
            <div class="fin-kpi-sub">{{ $accounts->count() }} account(s)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Cash Received</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum(fn($a) => $a->totalIncome()), 2) }}</div>
            <div class="fin-kpi-sub">All transactions</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Cash Paid</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum(fn($a) => $a->totalExpense()), 2) }}</div>
            <div class="fin-kpi-sub">All transactions</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-bank">
            <div class="fin-kpi-icon"><i class="bi bi-bank2"></i></div>
            <div class="fin-kpi-label">Net Position</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum(fn($a) => $a->totalIncome() - $a->totalExpense()), 2) }}</div>
            <div class="fin-kpi-sub">Income − Expenses</div>
        </div>
    </div>
</div>

<div class="row g-3">
    @forelse($accounts as $account)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card fin-account-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="fin-account-icon"><i class="bi bi-cash-coin"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">{{ $account->name }}</div>
                        <div class="text-muted small">{{ $account->account_code }}</div>
                    </div>
                    @if(!$account->is_active)
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>
                    @endif
                </div>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="fin-account-label">Balance</div>
                        <div class="fw-bold">{{ setting('general.currency','PKR') }} {{ number_format($account->current_balance, 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fin-account-label">Received</div>
                        <div class="fw-bold text-success">{{ number_format($account->totalIncome(), 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fin-account-label">Paid</div>
                        <div class="fw-bold text-danger">{{ number_format($account->totalExpense(), 0) }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="text-muted small">Opening: {{ setting('general.currency','PKR') }} {{ number_format($account->opening_balance, 0) }}</div>
                    @can('accounts.edit')
                    <a href="{{ route('finance.accounts.cash.edit', $account) }}" class="btn btn-xs btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-cash-stack fs-1 d-block mb-2 opacity-25"></i>
                <p class="text-muted mb-0">No cash accounts yet</p>
                @can('accounts.create')
                <a href="{{ route('finance.accounts.cash.create') }}" class="btn btn-sm btn-primary mt-2">Add First Cash Account</a>
                @endcan
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
