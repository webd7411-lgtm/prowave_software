@extends('layouts.app')
@section('title', $invoice ? 'Edit Invoice' : 'Create Invoice')
@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('crm.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">{{ $invoice ? 'Edit' : 'Create' }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="page-title">{{ $invoice ? 'Edit Invoice ' . $invoice->invoice_number : 'Create Invoice' }}</h1>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="{{ $invoice ? route('crm.invoices.update', $invoice) : route('crm.invoices.store') }}" id="invoiceForm">
    @csrf
    @if($invoice) @method('PATCH') @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-card title="Invoice Items" class="mb-4">
                <div class="table-responsive">
                    <table class="table align-middle" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%">Description <span class="text-danger">*</span></th>
                                <th style="width: 15%">Qty <span class="text-danger">*</span></th>
                                <th style="width: 20%">Price <span class="text-danger">*</span></th>
                                <th style="width: 15%">Discount</th>
                                <th style="width: 15%">Tax</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $items = old('items', $invoice ? $invoice->items->toArray() : [[]]);
                            @endphp
                            @foreach($items as $index => $item)
                            <tr class="item-row">
                                <td data-label="Description">
                                    <input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $item['description'] ?? '' }}" required>
                                </td>
                                <td data-label="Qty">
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control qty-input" value="{{ $item['quantity'] ?? '1' }}" min="0.01" step="0.01" required>
                                </td>
                                <td data-label="Price">
                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item['unit_price'] ?? '0' }}" min="0" step="0.01" required>
                                </td>
                                <td data-label="Discount">
                                    <input type="number" name="items[{{ $index }}][discount]" class="form-control disc-input" value="{{ $item['discount'] ?? '0' }}" min="0" step="0.01">
                                </td>
                                <td data-label="Tax">
                                    <input type="number" name="items[{{ $index }}][tax]" class="form-control tax-input" value="{{ $item['tax'] ?? '0' }}" min="0" step="0.01">
                                </td>
                                <td data-label="Action" class="fin-cell-actions">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    <button type="button" class="btn btn-sm btn-soft-primary" id="addItemBtn">
                                        <i class="bi bi-plus-circle me-1"></i>Add Row
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row justify-content-end mt-3">
                    <div class="col-12 col-md-5">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-end fw-semibold">Subtotal:</td>
                                <td class="text-end" id="subtotalDisplay">0.00</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-semibold">Discount:</td>
                                <td class="text-end text-danger" id="discountDisplay">0.00</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-semibold">Tax:</td>
                                <td class="text-end" id="taxDisplay">0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-end fw-bold fs-5 pt-2">Total:</td>
                                <td class="text-end fw-bold fs-5 pt-2" id="totalDisplay">0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </x-card>
            
            <x-card title="Terms & Notes">
                <div class="mb-0">
                    <label class="form-label">Notes for Client</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-card title="Invoice Details" class="sticky-lg-top" style="top: 20px;">
                <div class="mb-3">
                    <label class="form-label">Client <span class="text-danger">*</span></label>
                    <select name="client_id" class="form-select" required>
                        <option value="">Select Client</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id', $invoice->client_id ?? '') == $c->id)>
                                {{ $c->company_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                    @error('invoice_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? '') }}">
                    @error('due_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                @if($invoice)
                <div class="mb-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="unpaid" @selected(old('status', $invoice->status) == 'unpaid')>Unpaid</option>
                        <option value="partially_paid" @selected(old('status', $invoice->status) == 'partially_paid')>Partially Paid</option>
                        <option value="paid" @selected(old('status', $invoice->status) == 'paid')>Paid</option>
                        <option value="cancelled" @selected(old('status', $invoice->status) == 'cancelled')>Cancelled</option>
                    </select>
                </div>
                @endif

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ $invoice ? 'Update Invoice' : 'Create Invoice' }}
                    </button>
                    <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
                </div>
            </x-card>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ count($items) }};
    const tbody = document.querySelector('#itemsTable tbody');

    function calculateTotals() {
        let subtotal = 0, discount = 0, tax = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const disc = parseFloat(row.querySelector('.disc-input').value) || 0;
            const t = parseFloat(row.querySelector('.tax-input').value) || 0;
            
            subtotal += (qty * price);
            discount += disc;
            tax += t;
        });

        document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
        document.getElementById('discountDisplay').textContent = discount.toFixed(2);
        document.getElementById('taxDisplay').textContent = tax.toFixed(2);
        document.getElementById('totalDisplay').textContent = (subtotal - discount + tax).toFixed(2);
    }

    document.getElementById('addItemBtn').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td data-label="Description"><input type="text" name="items[${itemIndex}][description]" class="form-control" required></td>
            <td data-label="Qty"><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="1" min="0.01" step="0.01" required></td>
            <td data-label="Price"><input type="number" name="items[${itemIndex}][unit_price]" class="form-control price-input" value="0" min="0" step="0.01" required></td>
            <td data-label="Discount"><input type="number" name="items[${itemIndex}][discount]" class="form-control disc-input" value="0" min="0" step="0.01"></td>
            <td data-label="Tax"><input type="number" name="items[${itemIndex}][tax]" class="form-control tax-input" value="0" min="0" step="0.01"></td>
            <td data-label="Action" class="fin-cell-actions"><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        itemIndex++;
        calculateTotals();
    });

    tbody.addEventListener('click', (e) => {
        if(e.target.closest('.remove-item')) {
            if(tbody.querySelectorAll('.item-row').length > 1) {
                e.target.closest('tr').remove();
                calculateTotals();
            } else {
                alert('At least one item is required.');
            }
        }
    });

    tbody.addEventListener('input', (e) => {
        if(e.target.matches('input[type="number"]')) {
            calculateTotals();
        }
    });

    calculateTotals();
});
</script>
@endpush
