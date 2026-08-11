@extends('layouts.app')

@section('title', 'Company Profile')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Company Profile</li>
                </ol>
            </nav>
            <h1 class="page-title">Company Profile</h1>
            <p class="page-subtitle">Official company information used across the system, invoices and emails.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="Company Information" subtitle="Shown across the system, invoices and emails" icon="bi-building">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" data-loading>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                   value="{{ old('company_name', setting('general.name', '')) }}" required maxlength="255">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', setting('general.email', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', setting('general.phone', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="company_whatsapp" class="form-control" value="{{ old('company_whatsapp', setting('general.whatsapp', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="company_website" class="form-control" value="{{ old('company_website', setting('general.website', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', setting('general.tax_number', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', setting('general.registration_number', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', setting('general.address', '')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="company_city" class="form-control" value="{{ old('company_city', setting('general.city', '')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="company_country" class="form-control" value="{{ old('company_country', setting('general.country', '')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', setting('general.currency', 'USD')) }}" required maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select select2" data-placeholder="Select timezone">
                                <option value=""></option>
                                @foreach (timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ setting('general.timezone', config('app.timezone')) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Format</label>
                            <select name="date_format" class="form-select">
                                @foreach (['d M, Y' => '15 Aug, 2026', 'Y-m-d' => '2026-08-15', 'd/m/Y' => '15/08/2026', 'M d, Y' => 'Aug 15, 2026', 'F j, Y' => 'August 15, 2026'] as $value => $label)
                                    <option value="{{ $value }}" {{ setting('general.date_format', 'd M, Y') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Time Format</label>
                            <select name="time_format" class="form-select">
                                @foreach (['h:i A' => '3:30 PM', 'H:i' => '15:30'] as $value => $label)
                                    <option value="{{ $value }}" {{ setting('general.time_format', 'h:i A') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fiscal Year Start</label>
                            <input type="text" name="fiscal_year_start" class="form-control" value="{{ old('fiscal_year_start', setting('general.fiscal_year_start', '01-01')) }}" placeholder="MM-DD">
                            <div class="form-text">Month and day your fiscal year begins, e.g. 01-01 or 07-01.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Logo</label>
                            <input type="file" name="company_logo" class="form-control" accept="image/*">
                            <div class="form-text">PNG, JPG, SVG or WebP — max 2 MB.</div>
                            @if (setting('general.logo'))
                                <img src="{{ asset(setting('general.logo')) }}" alt="Company logo" class="mt-2" style="max-height: 56px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="company_favicon" class="form-control" accept="image/*">
                            <div class="form-text">PNG, ICO, SVG or WebP — max 1 MB.</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Profile</button>
                            <a href="{{ route('settings.general') }}" class="btn btn-outline">Cancel</a>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
