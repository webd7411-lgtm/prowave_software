@extends('layouts.app')
@section('title', 'Expense Details')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item active">{{ $expense->expense_number }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="page-title mb-0">{{ $expense->expense_number }}</h1>
        <div class="d-flex gap-2 flex-wrap">
            @can('expenses.edit')
            @if(!in_array($expense->status, ['approved','paid']))
            <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endif
            @endcan
            @can('expenses.approve')
            @if($expense->status === 'pending')
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-circle me-1"></i>Approve
            </button>
            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            @endif
            @endcan
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Expense Number</div>
                        <div class="fw-semibold">{{ $expense->expense_number }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Date</div>
                        <div class="fw-semibold">{{ $expense->expense_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Status</div>
                        <span class="badge bg-{{ $expense->statusBadge() }}-subtle text-{{ $expense->statusBadge() }} rounded-pill">{{ ucfirst($expense->status) }}</span>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Category</div>
                        <div>
                            @if($expense->category)
                            <span class="badge rounded-pill" style="background:{{ $expense->category->color }}20;color:{{ $expense->category->color }}">{{ $expense->category->name }}</span>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Amount</div>
                        <div class="fw-bold fs-5 text-danger">{{ setting('general.currency', 'PKR') }} {{ number_format($expense->amount, 2) }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Payment Account</div>
                        <div>{{ $expense->accountLabel() }}</div>
                    </div>
                    @if($expense->paymentMethod)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Payment Method</div>
                        <div>{{ $expense->paymentMethod->name }}</div>
                    </div>
                    @endif
                    @if($expense->reference_number)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Reference</div>
                        <div>{{ $expense->reference_number }}</div>
                    </div>
                    @endif
                    @if($expense->vendor)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Vendor</div>
                        <div><a href="{{ route('finance.vendors.show', $expense->vendor) }}">{{ $expense->vendor->name }}</a></div>
                    </div>
                    @endif
                    @if($expense->project)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Project</div>
                        <div><a href="{{ route('projects.show', $expense->project) }}">{{ $expense->project->name }}</a></div>
                    </div>
                    @endif
                    @if($expense->description)
                    <div class="col-12">
                        <div class="text-muted small">Description</div>
                        <div>{{ $expense->description }}</div>
                    </div>
                    @endif
                    @if($expense->notes)
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div class="bg-light rounded p-2 small">{{ $expense->notes }}</div>
                    </div>
                    @endif
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Added By</div>
                        <div>{{ $expense->addedBy?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Added On</div>
                        <div>{{ $expense->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($expense->status === 'approved' || $expense->status === 'rejected')
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Approval Information</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">{{ ucfirst($expense->status) }} By</div>
                        <div>{{ $expense->approvedBy?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">{{ ucfirst($expense->status) }} On</div>
                        <div>{{ $expense->approved_at?->format('d M Y H:i') ?? '—' }}</div>
                    </div>
                    @if($expense->approval_remarks)
                    <div class="col-12">
                        <div class="text-muted small">Remarks</div>
                        <div class="bg-light rounded p-2 small">{{ $expense->approval_remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        @if($expense->receipt_path)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Receipt</h6></div>
            <div class="card-body text-center">
                @if(str_ends_with($expense->receipt_path, '.pdf'))
                <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-file-pdf fs-4 d-block mb-1"></i>View PDF Receipt
                </a>
                @else
                <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank">
                    <img src="{{ asset('storage/'.$expense->receipt_path) }}" class="img-fluid rounded" alt="Receipt">
                </a>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to Expenses
                </a>
                @can('expenses.delete')
                @if($expense->status !== 'paid')
                <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Delete this expense?')">
                        <i class="bi bi-trash me-1"></i>Delete Expense
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
@can('expenses.approve')
@if($expense->status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Approve Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}">
                @csrf
                <div class="modal-body">
                    <p>Approve expense <strong>{{ $expense->expense_number }}</strong> for <strong>{{ setting('general.currency','PKR') }} {{ number_format($expense->amount,2) }}</strong>?</p>
                    <label class="form-label">Remarks (optional)</label>
                    <textarea name="approval_remarks" rows="2" class="form-control" placeholder="Approval remarks..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reject Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('finance.expenses.reject', $expense) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea name="approval_remarks" rows="3" class="form-control" placeholder="Explain why..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection
