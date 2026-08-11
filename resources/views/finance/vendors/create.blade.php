@extends('layouts.app')
@section('title', 'Add Vendor')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.vendors.index') }}">Vendors</a></li>
            <li class="breadcrumb-item active">Add Vendor</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New Vendor</h1>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('finance.vendors.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Company or individual name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control" placeholder="Primary contact">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+92 3xx xxxxxxx">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" class="form-control" placeholder="+92 3xx xxxxxxx">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="vendor@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="e.g. Hardware, Services, Stationery" list="vendor-category-options">
                            <datalist id="vendor-category-options">
                                @foreach(['Hardware','Software','Services','Stationery','Utilities','Marketing','Transport','Other'] as $cat)
                                <option value="{{ $cat }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="2" class="form-control" placeholder="Office / shop address...">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Internal notes...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>Save Vendor
                        </button>
                        <a href="{{ route('finance.vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
