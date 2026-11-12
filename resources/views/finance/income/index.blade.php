@extends('layouts.app')
@section('title', 'Income Records')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Income</h1>
        <p class="text-muted mb-0">Track all income and revenue records</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('income.export')
        <a href="{{ route('finance.income.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a>
        @endcan
        @can('income.create')
        <a href="{{ route('finance.income.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Income</a>
        @endcan
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($filters['categories'] as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($filters['clients'] as $c)
                    <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-3 col-md-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm"></div>
            <div class="col-6 col-sm-3 col-md-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm"></div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request()->hasAny(['category','client','from_date','to_date','search']))
                    <a href="{{ route('finance.income.index') }}" class="btn btn-outline-secondary">×</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-light border d-flex align-items-center gap-3 py-2 mb-3 flex-wrap">
    <span><strong>{{ $records->total() }}</strong> records</span>
    <span class="text-muted">|</span>
    <span>Total: <strong class="text-success">{{ setting('general.currency', 'PKR') }} {{ number_format($totalAmount, 2) }}</strong></span>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table table-cards-lg">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Income No</th>
                    <th>Category</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Reference</th>
                    <th>Added By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $income)
                <tr>
                    <td data-label="#" class="text-muted small">{{ $records->firstItem() + $loop->index }}</td>
                    <td data-label="Income No">
                        <a href="{{ route('finance.income.show', $income) }}" class="fw-semibold text-decoration-none">{{ $income->income_number }}</a>
                        @if($income->description)
                        <div class="text-muted small text-break">{{ \Illuminate\Support\Str::limit($income->description, 70) }}</div>
                        @endif
                    </td>
                    <td data-label="Category">
                        <span class="badge rounded-pill" style="background:{{ $income->category?->color ?? '#198754' }}20;color:{{ $income->category?->color ?? '#198754' }}">
                            {{ $income->category?->name ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td data-label="Client" class="text-muted small">{{ $income->client?->company_name ?? '—' }}</td>
                    <td data-label="Amount" class="fw-bold text-success">{{ setting('general.currency', 'PKR') }} {{ number_format($income->amount, 2) }}</td>
                    <td data-label="Date" class="text-muted small">{{ $income->income_date->format('d M Y') }}</td>
                    <td data-label="Account" class="text-muted small">{{ $income->accountLabel() }}</td>
                    <td data-label="Reference" class="text-muted small">{{ $income->reference_number ?? '—' }}</td>
                    <td data-label="Added By" class="text-muted small">{{ $income->addedBy?->name ?? '—' }}</td>
                    <td data-label="" class="text-end fin-cell-actions">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('finance.income.show', $income) }}" class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @can('income.edit')
                            <a href="{{ route('finance.income.edit', $income) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('income.delete')
                            <form method="POST" action="{{ route('finance.income.destroy', $income) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this income record?')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-arrow-down-circle fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No income records found</p>
                        @can('income.create')
                        <a href="{{ route('finance.income.create') }}" class="btn btn-sm btn-success mt-2">Add First Income</a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer">{{ $records->links() }}</div>
    @endif
</div>
@endsection
