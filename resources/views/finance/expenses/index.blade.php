@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Expenses</h1>
        <p class="text-muted mb-0">Manage and track all business expenses</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('expenses.export')
        <a href="{{ route('finance.expenses.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        @endcan
        @can('expenses.create')
        <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Expense
        </a>
        @endcan
    </div>
</div>

{{-- Filters --}}
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
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['pending','approved','rejected','paid'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-6 col-md-2 col-lg-2">
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm" placeholder="From">
            </div>
            <div class="col-6 col-sm-6 col-md-2 col-lg-2">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm" placeholder="To">
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request()->hasAny(['category','status','from_date','to_date','search']))
                    <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">×</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary stat --}}
<div class="alert alert-light border d-flex align-items-center gap-3 py-2 mb-3 flex-wrap">
    <span><strong>{{ $records->total() }}</strong> records</span>
    <span class="text-muted">|</span>
    <span>Total: <strong class="text-danger">{{ setting('general.currency', 'PKR') }} {{ number_format($totalAmount, 2) }}</strong></span>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Expense No</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Vendor/Project</th>
                    <th>Status</th>
                    <th>Added By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $expense)
                <tr>
                    <td class="text-muted small">{{ $records->firstItem() + $loop->index }}</td>
                    <td>
                        <a href="{{ route('finance.expenses.show', $expense) }}" class="fw-semibold text-decoration-none">{{ $expense->expense_number }}</a>
                        @if($expense->description)
                        <div class="text-muted small text-truncate" style="max-width:150px">{{ $expense->description }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:{{ $expense->category?->color ?? '#6c757d' }}20;color:{{ $expense->category?->color ?? '#6c757d' }}">
                            {{ $expense->category?->name ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td class="fw-bold text-danger">{{ setting('general.currency', 'PKR') }} {{ number_format($expense->amount, 2) }}</td>
                    <td class="text-muted small">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="text-muted small">{{ $expense->accountLabel() }}</td>
                    <td class="text-muted small">
                        @if($expense->vendor) <div>{{ $expense->vendor->name }}</div> @endif
                        @if($expense->project) <div class="text-primary">{{ $expense->project->name }}</div> @endif
                        @if(!$expense->vendor && !$expense->project) — @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $expense->statusBadge() }}-subtle text-{{ $expense->statusBadge() }} rounded-pill">
                            {{ ucfirst($expense->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $expense->addedBy?->name ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('finance.expenses.show', $expense) }}" class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @can('expenses.edit')
                            @if(!in_array($expense->status, ['approved', 'paid']))
                            <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endif
                            @endcan
                            @can('expenses.approve')
                            @if($expense->status === 'pending')
                            <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}" class="d-inline" id="approve-{{ $expense->id }}">
                                @csrf
                                <button class="btn btn-xs btn-outline-success" title="Approve" onclick="return confirm('Approve this expense?')"><i class="bi bi-check-circle"></i></button>
                            </form>
                            @endif
                            @endcan
                            @can('expenses.delete')
                            @if($expense->status !== 'paid')
                            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this expense? This cannot be undone.')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No expenses found</p>
                        @can('expenses.create')
                        <a href="{{ route('finance.expenses.create') }}" class="btn btn-sm btn-primary mt-2">Add First Expense</a>
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
