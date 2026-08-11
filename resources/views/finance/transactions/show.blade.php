@extends('layouts.app')
@section('title', 'Transaction #'.$transaction->id)
@section('content')
<div class="page-header mb-4 animate-fade-in-up">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.transactions.index') }}">Transactions</a></li>
            <li class="breadcrumb-item active">Transaction #{{ $transaction->id }}</li>
        </ol>
    </nav>
    <h1 class="page-title">Transaction #{{ $transaction->id }}</h1>
</div>

<div class="row g-4 animate-fade-in-up stagger-1">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-600"><i class="bi bi-receipt me-2 text-primary"></i>Transaction Details</span>
                @if(auth()->user()->can('transactions.edit') || auth()->user()->can('transactions.delete'))
                <div class="d-flex gap-2">
                    @can('transactions.edit')
                    <a href="{{ route('finance.transactions.edit', $transaction) }}" class="btn btn-sm btn-soft-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    @endcan
                    @can('transactions.delete')
                    <form method="POST" action="{{ route('finance.transactions.destroy', $transaction) }}" data-confirm data-confirm-title="Delete transaction?" data-confirm-text="This will revert the account balance. This cannot be undone.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-soft-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                    @endcan
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="ps-4 text-muted" style="width: 40%">Type</td>
                            <td class="fw-500">
                                <span class="badge badge-soft-{{ $transaction->typeBadge() }}">{{ ucfirst($transaction->type) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-muted">Account</td>
                            <td class="fw-500">{{ ucfirst($transaction->account_type) }}
                                @if($transaction->cashAccount) · {{ $transaction->cashAccount->name }}
                                @elseif($transaction->bankAccount) · {{ $transaction->bankAccount->bank_name }} – {{ $transaction->bankAccount->account_title }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-muted">Amount</td>
                            <td class="fw-700 {{ $transaction->debit > 0 ? 'text-danger' : 'text-success' }}">
                                @if($transaction->debit > 0)
                                <i class="bi bi-arrow-down-left me-1"></i>{{ number_format($transaction->debit, 2) }} (Debit)
                                @else
                                <i class="bi bi-arrow-up-right me-1"></i>{{ number_format($transaction->credit, 2) }} (Credit)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-muted">Date</td>
                            <td class="fw-500">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                        </tr>
                        @if($transaction->reference)
                        <tr>
                            <td class="ps-4 text-muted">Reference</td>
                            <td class="fw-500">{{ $transaction->reference }}</td>
                        </tr>
                        @endif
                        @if($transaction->description)
                        <tr>
                            <td class="ps-4 text-muted">Description</td>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="ps-4 text-muted">Recorded By</td>
                            <td class="fw-500">{{ $transaction->createdBy?->name ?? 'System' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card bg-soft border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-wallet2 fs-1 text-primary mb-3 d-block"></i>
                <div class="fs-xs text-uppercase text-muted">Balance effect</div>
                <div class="display-6 fw-700 {{ $transaction->credit > 0 ? 'text-success' : 'text-danger' }}">
                    {{ $transaction->credit > 0 ? '+' : '−' }}{{ number_format(max($transaction->debit, $transaction->credit), 2) }}
                </div>
                <div class="text-muted fs-sm mt-2">{{ $transaction->credit > 0 ? 'Added to' : 'Deducted from' }} account balance</div>
                <a href="{{ route('finance.transactions.index') }}" class="btn btn-outline mt-4">
                    <i class="bi bi-arrow-left me-1"></i>Back to Transactions
                </a>
            </div>
        </div>
    </div>
</div>
@endsection