@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>
            <h1 class="page-title">General Settings</h1>
            <p class="page-subtitle">Manage your company information and application defaults.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="Company Information" subtitle="Shown across the system, invoices and emails" icon="bi-building">
                <x-form :action="route('settings.general.update')" method="POST" cancel-url="{{ route('settings.general') }}" multipart>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Company Logo</label>
                            <input type="file" name="company_logo" class="form-control" accept="image/*">
                            <div class="form-text">Shown in sidebar and company documents — PNG, JPG, SVG (max 2MB).</div>
                            @if(setting('general.logo'))
                                <img src="{{ asset(setting('general.logo')) }}" alt="Company logo" class="mt-2" style="max-height:64px; border-radius:8px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Company Name"
                                name="company_name"
                                :value="setting('general.name', '')"
                                :required="true"
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Company Email"
                                name="company_email"
                                type="email"
                                :value="setting('general.email', '')"
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Company Phone"
                                name="company_phone"
                                :value="setting('general.phone', '')"
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Website"
                                name="company_website"
                                type="url"
                                :value="setting('general.website', '')"
                            />
                        </div>
                        <div class="col-12">
                            <x-form.input
                                label="Company Address"
                                name="company_address"
                                :value="setting('general.address', '')"
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input
                                label="Default Currency"
                                name="currency"
                                :value="setting('general.currency', 'PKR')"
                                :required="true"
                                help="ISO currency code, e.g. USD, EUR, BDT."
                            />
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <x-form.switch
                                label="Allow public registration"
                                name="allow_registration"
                                :checked="(bool) setting('general.allow_registration', false)"
                                help="When enabled, visitors can create their own accounts."
                            />
                        </div>
                    </div>
                </x-form>
            </x-card>
        </div>
    </div>
@endsection
