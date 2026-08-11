@extends('layouts.app')
@section('title', 'Edit Expense')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finance.expenses.show', $expense) }}">{{ $expense->expense_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit Expense</h1>
</div>
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.expenses.update', $expense) }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Select Category —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('general.currency', 'PKR') }}</span>
                                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $expense->amount) }}" class="form-control @error('amount') is-invalid @enderror" required>
                            </div>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">— Select Method —</option>
                                @foreach($paymentMethods as $m)
                                <option value="{{ $m->id }}" {{ old('payment_method_id', $expense->payment_method_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Payment Account <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap mb-2">
                                <div class="form-check">
                                    <input type="radio" name="account_type" id="acc_cash" value="cash" class="form-check-input" {{ old('account_type', $expense->account_type) === 'cash' ? 'checked' : '' }}>
                                    <label for="acc_cash" class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="account_type" id="acc_bank" value="bank" class="form-check-input" {{ old('account_type', $expense->account_type) === 'bank' ? 'checked' : '' }}>
                                    <label for="acc_bank" class="form-check-label">Bank</label>
                                </div>
                            </div>
                            <div id="cash-accounts-section">
                                <select name="cash_account_id" class="form-select">
                                    <option value="">— Select Cash Account —</option>
                                    @foreach($cashAccounts as $a)
                                    <option value="{{ $a->id }}" {{ old('cash_account_id', $expense->cash_account_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="bank-accounts-section" style="display:none">
                                <select name="bank_account_id" class="form-select">
                                    <option value="">— Select Bank Account —</option>
                                    @foreach($bankAccounts as $a)
                                    <option value="{{ $a->id }}" {{ old('bank_account_id', $expense->bank_account_id) == $a->id ? 'selected' : '' }}>{{ $a->bank_name }} – {{ $a->account_title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select">
                                <option value="">— No Vendor —</option>
                                @foreach($vendors as $v)
                                <option value="{{ $v->id }}" {{ old('vendor_id', $expense->vendor_id) == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">— No Project —</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $expense->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number', $expense->reference_number) }}" class="form-control">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Receipt Upload</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                            @if($expense->receipt_path)
                            <div class="mt-1 small text-muted"><i class="bi bi-paperclip"></i> Receipt already uploaded</div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control">{{ old('description', $expense->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        <a href="{{ route('finance.expenses.show', $expense) }}" class="btn btn-outline-secondary">Cancel</a>
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
