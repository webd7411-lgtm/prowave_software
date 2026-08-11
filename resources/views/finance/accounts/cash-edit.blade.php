@extends('layouts.app')
@section('title', 'Edit Cash Account')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.accounts.cash') }}">Cash Accounts</a></li>
            <li class="breadcrumb-item active">{{ $cashAccount->name }}</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit Cash Account</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="text-muted small">Opening Balance</div>
                        <div class="fw-semibold">{{ setting('general.currency','PKR') }} {{ number_format($cashAccount->opening_balance, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Current Balance</div>
                        <div class="fw-semibold">{{ setting('general.currency','PKR') }} {{ number_format($cashAccount->current_balance, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Status</div>
                        <span class="badge {{ $cashAccount->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill">{{ $cashAccount->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Balances are updated automatically by financial transactions and cannot be edited manually to protect data integrity.
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.accounts.cash.update', $cashAccount) }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $cashAccount->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Account Code</label>
                            <input type="text" value="{{ $cashAccount->account_code }}" class="form-control" disabled>
                            <div class="form-text">Account codes cannot be changed.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $cashAccount->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $cashAccount->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Account is active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>Update Account
                        </button>
                        <a href="{{ route('finance.accounts.cash') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
