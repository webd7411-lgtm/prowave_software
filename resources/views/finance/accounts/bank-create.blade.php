@extends('layouts.app')
@section('title', 'Add Bank Account')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.accounts.bank') }}">Bank Accounts</a></li>
            <li class="breadcrumb-item active">Add Bank Account</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New Bank Account</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.accounts.bank.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control @error('bank_name') is-invalid @enderror" placeholder="e.g. Meezan Bank" required>
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Account Title <span class="text-danger">*</span></label>
                            <input type="text" name="account_title" value="{{ old('account_title') }}" class="form-control @error('account_title') is-invalid @enderror" placeholder="Account holder name" required>
                            @error('account_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" value="{{ old('account_number') }}" class="form-control @error('account_number') is-invalid @enderror" placeholder="IBAN / account no">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">IBAN</label>
                            <input type="text" name="iban" value="{{ old('iban') }}" class="form-control" placeholder="e.g. PK36MEZN0000000000000000">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Branch</label>
                            <input type="text" name="branch" value="{{ old('branch') }}" class="form-control" placeholder="Branch name / city">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">SWIFT Code</label>
                            <input type="text" name="swift_code" value="{{ old('swift_code') }}" class="form-control" placeholder="e.g. MEZNPKKA">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                @foreach(['PKR','USD','EUR','GBP','AED','SAR'] as $cur)
                                <option value="{{ $cur }}" {{ old('currency', 'PKR') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>Create Account
                        </button>
                        <a href="{{ route('finance.accounts.bank') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
