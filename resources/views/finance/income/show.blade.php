@extends('layouts.app')
@section('title', 'Income Details')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.income.index') }}">Income</a></li>
            <li class="breadcrumb-item active">{{ $income->income_number }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="page-title mb-0">{{ $income->income_number }}</h1>
        <div class="d-flex gap-2 flex-wrap">
            @can('income.edit')
            <a href="{{ route('finance.income.edit', $income) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            @can('income.delete')
            <form method="POST" action="{{ route('finance.income.destroy', $income) }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this income record? This cannot be undone.')">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
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
                        <div class="text-muted small">Income Number</div>
                        <div class="fw-semibold">{{ $income->income_number }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Date</div>
                        <div class="fw-semibold">{{ $income->income_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Amount</div>
                        <div class="fw-bold fs-5 text-success">{{ setting('general.currency', 'PKR') }} {{ number_format($income->amount, 2) }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Category</div>
                        <div>
                            @if($income->category)
                            <span class="badge rounded-pill" style="background:{{ $income->category->color }}20;color:{{ $income->category->color }}">{{ $income->category->name }}</span>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Deposited Into</div>
                        <div>{{ $income->accountLabel() }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Payment Method</div>
                        <div>{{ $income->paymentMethod?->name ?? '—' }}</div>
                    </div>
                    @if($income->client)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Client</div>
                        <div><a href="{{ route('crm.clients.show', $income->client) }}" class="text-decoration-none">{{ $income->client->company_name }}</a></div>
                    </div>
                    @endif
                    @if($income->project)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Project</div>
                        <div><a href="{{ route('projects.show', $income->project) }}" class="text-decoration-none">{{ $income->project->name }}</a></div>
                    </div>
                    @endif
                    @if($income->invoice)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Invoice</div>
                        <div>{{ $income->invoice->invoice_number }}</div>
                    </div>
                    @endif
                    @if($income->reference_number)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Reference Number</div>
                        <div>{{ $income->reference_number }}</div>
                    </div>
                    @endif
                    @if($income->description)
                    <div class="col-12">
                        <div class="text-muted small">Description</div>
                        <div>{{ $income->description }}</div>
                    </div>
                    @endif
                    @if($income->notes)
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div class="bg-light rounded p-2 small">{{ $income->notes }}</div>
                    </div>
                    @endif
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Added By</div>
                        <div>{{ $income->addedBy?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Added On</div>
                        <div>{{ $income->created_at?->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        @if($income->attachment_path)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Attachment</h6></div>
            <div class="card-body text-center">
                @if(str_ends_with($income->attachment_path, '.pdf'))
                <a href="{{ asset('storage/'.$income->attachment_path) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-file-pdf fs-4 d-block mb-1"></i>View PDF Attachment
                </a>
                @else
                <a href="{{ asset('storage/'.$income->attachment_path) }}" target="_blank">
                    <img src="{{ asset('storage/'.$income->attachment_path) }}" class="img-fluid rounded" alt="Attachment">
                </a>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('finance.income.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to Income
                </a>
                <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
