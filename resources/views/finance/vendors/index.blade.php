@extends('layouts.app')
@section('title', 'Vendors & Suppliers')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Vendors & Suppliers</h1>
        <p class="text-muted mb-0">Manage vendors, suppliers and their payments</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('vendors.create')
        <a href="{{ route('finance.vendors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Vendor
        </a>
        @endcan
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-shop"></i></div>
            <div class="fin-kpi-label">Total Vendors</div>
            <div class="fin-kpi-value">{{ $records->total() }}</div>
            <div class="fin-kpi-sub">Registered suppliers</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-cash">
            <div class="fin-kpi-icon"><i class="bi bi-bag-check"></i></div>
            <div class="fin-kpi-label">Active Vendors</div>
            <div class="fin-kpi-value">{{ $records->where('status', 'active')->count() }}</div>
            <div class="fin-kpi-sub">Currently active</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="fin-kpi-label">Vendor Expenses</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($records->sum('total_expenses_sum'), 0) }}</div>
            <div class="fin-kpi-sub">All vendors</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="fin-kpi-label">Vendor Payments</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($records->sum(fn($v) => $v->totalPaid()), 0) }}</div>
            <div class="fin-kpi-sub">Paid to vendors</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-5">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, phone, email...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request()->hasAny(['status','category','search']))
                    <a href="{{ route('finance.vendors.index') }}" class="btn btn-outline-secondary">×</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table table-cards-lg">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Category</th>
                    <th>Contact</th>
                    <th class="text-end">Expenses</th>
                    <th class="text-end">Paid</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $vendor)
                <tr>
                    <td data-label="Vendor">
                        <a href="{{ route('finance.vendors.show', $vendor) }}" class="fw-semibold text-decoration-none">{{ $vendor->name }}</a>
                        @if($vendor->contact_person)
                        <div class="text-muted small">{{ $vendor->contact_person }}</div>
                        @endif
                    </td>
                    <td data-label="Category">
                        @if($vendor->category)
                        <span class="badge bg-primary-soft rounded-pill">{{ $vendor->category }}</span>
                        @else —
                        @endif
                    </td>
                    <td data-label="Contact" class="text-muted small">
                        @if($vendor->phone)<div><i class="bi bi-telephone me-1"></i>{{ $vendor->phone }}</div>@endif
                        @if($vendor->email)<div class="text-truncate" style="max-width:180px"><i class="bi bi-envelope me-1"></i>{{ $vendor->email }}</div>@endif
                        @if(!$vendor->phone && !$vendor->email) — @endif
                    </td>
                    <td data-label="Expenses" class="text-end fin-amount-out">{{ setting('general.currency','PKR') }} {{ number_format($vendor->total_expenses_sum ?? 0, 0) }}</td>
                    <td data-label="Paid" class="text-end fin-amount-in">{{ setting('general.currency','PKR') }} {{ number_format($vendor->totalPaid(), 0) }}</td>
                    <td data-label="Status">
                        <span class="badge {{ $vendor->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill">{{ ucfirst($vendor->status) }}</span>
                    </td>
                    <td data-label="Actions" class="text-end fin-cell-actions">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('finance.vendors.show', $vendor) }}" class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @can('vendors.edit')
                            <a href="{{ route('finance.vendors.edit', $vendor) }}" class="btn btn-xs btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('vendors.delete')
                            <form method="POST" action="{{ route('finance.vendors.destroy', $vendor) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Delete {{ $vendor->name }}? Existing records will be kept but the vendor will be removed.')"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-shop fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No vendors found</p>
                        @can('vendors.create')
                        <a href="{{ route('finance.vendors.create') }}" class="btn btn-sm btn-primary mt-2">Add First Vendor</a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer">{{ $records->links() }}</div>
    @endif
</div>
@endsection
