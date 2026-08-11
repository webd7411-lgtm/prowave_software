@extends('layouts.app')
@section('title', 'Add Cash Account')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.accounts.cash') }}">Cash Accounts</a></li>
            <li class="breadcrumb-item active">Add Cash Account</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New Cash Account</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.accounts.cash.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Office Cash, Petty Cash" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" value="{{ old('account_code') }}" class="form-control @error('account_code') is-invalid @enderror" placeholder="e.g. CASH-001" required>
                            @error('account_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Opening Balance <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('general.currency', 'PKR') }}</span>
                                <input type="number" name="opening_balance" step="0.01" min="0" value="{{ old('opening_balance', 0) }}" class="form-control @error('opening_balance') is-invalid @enderror" required>
                            </div>
                            <div class="form-text">The opening balance becomes the current balance.</div>
                            @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Purpose of this cash account...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>Create Account
                        </button>
                        <a href="{{ route('finance.accounts.cash') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
