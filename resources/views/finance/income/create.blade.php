@extends('layouts.app')
@section('title', 'Add Income')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.income.index') }}">Income</a></li>
            <li class="breadcrumb-item active">Add Income</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New Income</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.income.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Income Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">— Select Category —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('general.currency', 'PKR') }}</span>
                                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00" required>
                            </div>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Income Date <span class="text-danger">*</span></label>
                            <input type="date" name="income_date" value="{{ old('income_date', date('Y-m-d')) }}" class="form-control @error('income_date') is-invalid @enderror" required>
                            @error('income_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">— Select Method —</option>
                                @foreach($paymentMethods as $m)
                                <option value="{{ $m->id }}" {{ old('payment_method_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deposit Into Account <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap mb-2">
                                <div class="form-check">
                                    <input type="radio" name="account_type" id="acc_cash" value="cash" class="form-check-input" {{ old('account_type', 'cash') === 'cash' ? 'checked' : '' }}>
                                    <label for="acc_cash" class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="account_type" id="acc_bank" value="bank" class="form-check-input" {{ old('account_type') === 'bank' ? 'checked' : '' }}>
                                    <label for="acc_bank" class="form-check-label">Bank</label>
                                </div>
                            </div>
                            <div id="cash-accounts-section">
                                <select name="cash_account_id" class="form-select @error('cash_account_id') is-invalid @enderror">
                                    <option value="">— Select Cash Account —</option>
                                    @foreach($cashAccounts as $a)
                                    <option value="{{ $a->id }}" {{ old('cash_account_id') == $a->id ? 'selected' : '' }}>{{ $a->name }} (Balance: {{ number_format($a->current_balance,2) }})</option>
                                    @endforeach
                                </select>
                                @error('cash_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div id="bank-accounts-section" style="display:none">
                                <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                                    <option value="">— Select Bank Account —</option>
                                    @foreach($bankAccounts as $a)
                                    <option value="{{ $a->id }}" {{ old('bank_account_id') == $a->id ? 'selected' : '' }}>{{ $a->bank_name }} – {{ $a->account_title }}</option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select">
                                <option value="">— No Client —</option>
                                @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">— No Project —</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="form-control" placeholder="Invoice/Receipt No">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Brief description...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-circle me-1"></i>Save Income
                        </button>
                        <a href="{{ route('finance.income.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[name="account_type"]').forEach(r => {
    r.addEventListener('change', function() {
        document.getElementById('cash-accounts-section').style.display = this.value === 'cash' ? '' : 'none';
        document.getElementById('bank-accounts-section').style.display = this.value === 'bank' ? '' : 'none';
    });
    if (r.checked) r.dispatchEvent(new Event('change'));
});
</script>
@endpush
