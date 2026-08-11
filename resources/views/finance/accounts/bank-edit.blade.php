@extends('layouts.app')
@section('title', 'Edit Bank Account')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.accounts.bank') }}">Bank Accounts</a></li>
            <li class="breadcrumb-item active">{{ $bankAccount->bank_name }}</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit Bank Account</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="text-muted small">Opening Balance</div>
                        <div class="fw-semibold">{{ setting('general.currency','PKR') }} {{ number_format($bankAccount->opening_balance, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Current Balance</div>
                        <div class="fw-semibold">{{ setting('general.currency','PKR') }} {{ number_format($bankAccount->current_balance, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Status</div>
                        <span class="badge {{ $bankAccount->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill">{{ ucfirst($bankAccount->status) }}</span>
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
                <form method="POST" action="{{ route('finance.accounts.bank.update', $bankAccount) }}">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $bankAccount->bank_name) }}" class="form-control @error('bank_name') is-invalid @enderror" required>
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Account Title <span class="text-danger">*</span></label>
                            <input type="text" name="account_title" value="{{ old('account_title', $bankAccount->account_title) }}" class="form-control @error('account_title') is-invalid @enderror" required>
                            @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Branch</label>
                            <input type="text" name="branch" value="{{ old('branch', $bankAccount->branch) }}" class="form-control">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">SWIFT Code</label>
                            <input type="text" name="swift_code" value="{{ old('swift_code', $bankAccount->swift_code) }}" class="form-control">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                @foreach(['PKR','USD','EUR','GBP','AED','SAR'] as $cur)
                                <option value="{{ $cur }}" {{ old('currency', $bankAccount->currency) === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $bankAccount->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $bankAccount->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $bankAccount->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>Update Account
                        </button>
                        <a href="{{ route('finance.accounts.bank') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
