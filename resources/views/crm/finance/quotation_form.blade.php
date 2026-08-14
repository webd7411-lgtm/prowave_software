@extends('layouts.app')
@section('title', $quotation ? 'Edit Quotation' : 'Add Quotation')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('crm.quotations.index') }}">Quotations</a></li>
            <li class="breadcrumb-item active">{{ $quotation ? 'Edit' : 'Add' }} Quotation</li>
        </ol>
    </nav>
    <h1 class="page-title">{{ $quotation ? 'Edit' : 'New' }} Quotation</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-9">
        <form method="POST" action="{{ $quotation ? route('crm.quotations.update', $quotation) : route('crm.quotations.store') }}" id="quotationForm">
            @csrf
            @if($quotation)
                @method('PATCH')
            @endif

            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id', $quotation?->client_id ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            @if($quotation)
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach(['draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted','rejected'=>'Rejected','expired'=>'Expired'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('status', $quotation->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="Draft" disabled>
                                <input type="hidden" name="status" value="draft">
                            @endif
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" value="{{ old('quotation_date', $quotation?->quotation_date?->format('Y-m-d') ?? date('Y-m-d')) }}" class="form-control @error('quotation_date') is-invalid @enderror" required>
                            @error('quotation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" value="{{ old('valid_until', $quotation?->valid_until?->format('Y-m-d') ?? '') }}" class="form-control @error('valid_until') is-invalid @enderror">
                            @error('valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Line Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:250px">Description <span class="text-danger">*</span></th>
                                    <th style="width:100px">Qty <span class="text-danger">*</span></th>
                                    <th style="width:130px">Unit Price <span class="text-danger">*</span></th>
                                    <th style="width:110px">Discount</th>
                                    <th style="width:100px">Tax</th>
                                    <th style="width:130px">Total</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">Subtotal</td>
                                    <td class="fw-semibold" id="subtotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">Total Discount</td>
                                    <td class="fw-semibold text-danger" id="totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">Total Tax</td>
                                    <td class="fw-semibold" id="totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="5" class="text-end fw-bold fs-5">Grand Total</td>
                                    <td class="fw-bold fs-5" id="grandTotal">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @error('items')<div class="text-danger small mt-2 px-3">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Additional notes for the client...">{{ old('notes', $quotation?->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" rows="3" class="form-control" placeholder="Payment terms, validity, etc...">{{ old('terms', $quotation?->terms ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i>{{ $quotation ? 'Update Quotation' : 'Save Quotation' }}
                </button>
                <a href="{{ route('crm.quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const tbody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    let rowIndex = 0;

    const oldItems = @json(old('items', $quotation?->items ?? []));

    function createRow(data = {}) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="Service or product description" value="${data.description || ''}" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm item-qty" step="0.01" min="0.01" value="${data.quantity || 1}" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="${data.unit_price || 0}" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][discount]" class="form-control form-control-sm item-discount" step="0.01" min="0" value="${data.discount || 0}">
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][tax]" class="form-control form-control-sm item-tax" step="0.01" min="0" value="${data.tax || 0}">
            </td>
            <td class="fw-semibold item-total">0.00</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        recalc();
    }

    function recalc() {
        let subtotal = 0, totalDiscount = 0, totalTax = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(tr.querySelector('.item-price')?.value) || 0;
            const disc = parseFloat(tr.querySelector('.item-discount')?.value) || 0;
            const tax = parseFloat(tr.querySelector('.item-tax')?.value) || 0;
            const line = (qty * price) - disc + tax;
            tr.querySelector('.item-total').textContent = line.toFixed(2);
            subtotal += (qty * price);
            totalDiscount += disc;
            totalTax += tax;
        });
        const grandTotal = subtotal - totalDiscount + totalTax;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('totalDiscount').textContent = totalDiscount.toFixed(2);
        document.getElementById('totalTax').textContent = totalTax.toFixed(2);
        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    }

    addItemBtn.addEventListener('click', () => createRow());

    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('tr').remove();
            recalc();
        }
    });

    tbody.addEventListener('input', recalc);

    if (oldItems.length) {
        oldItems.forEach(item => createRow(item));
    } else {
        createRow();
    }
})();
</script>
@endpush
