@extends('layouts.app')

@section('title', $client->exists ? 'Edit Client' : 'New Client')

@push('styles')
<style>
    .form-label, label {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }
    html[data-theme="dark"] .form-label,
    html[data-theme="dark"] label,
    html[data-bs-theme="dark"] .form-label,
    html[data-bs-theme="dark"] label,
    body[data-user-theme="dark"] .form-label,
    body[data-user-theme="dark"] label,
    .client-form .form-label,
    .client-form label {
        color: #ffffff !important;
    }
    html[data-theme="light"] .form-label,
    html[data-theme="light"] label {
        color: #1e293b !important;
    }
    html[data-theme="dark"] .form-control,
    html[data-theme="dark"] .form-select,
    html[data-bs-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-select,
    body[data-user-theme="dark"] .form-control,
    body[data-user-theme="dark"] .form-select,
    body[data-user-theme="dark"] input,
    body[data-user-theme="dark"] textarea,
    body[data-user-theme="dark"] select {
        color: #ffffff !important;
        background-color: #1a233a !important;
        border-color: #2e3e62 !important;
    }
    html[data-theme="dark"] .form-control::placeholder,
    html[data-bs-theme="dark"] .form-control::placeholder,
    body[data-user-theme="dark"] .form-control::placeholder {
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] select option,
    html[data-bs-theme="dark"] select option,
    body[data-user-theme="dark"] select option {
        background-color: #1a233a !important;
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.clients.index') }}">Clients</a></li>
                    @if($client->exists)
                        <li class="breadcrumb-item"><a href="{{ route('crm.clients.show', $client) }}">{{ $client->company_name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ $client->exists ? 'Edit' : 'New Client' }}</li>
                </ol>
            </nav>
            <h1 class="page-title">{{ $client->exists ? 'Edit Client' : 'New Client' }}</h1>
        </div>

        <a href="{{ $client->exists ? route('crm.clients.show', $client) : route('crm.clients.index') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <x-card body-class="p-4">
        <form method="POST" action="{{ $action }}" class="client-form">
            @csrf
            @if($client->exists)
                @method('PATCH')
            @endif

            <div class="row g-3">
                @foreach([
                    'company_name' => 'Company name *',
                    'contact_person' => 'Contact person',
                    'phone' => 'Phone',
                    'email' => 'Email',
                    'website' => 'Website',
                ] as $field => $label)
                    <div class="col-md-6">
                        <label class="form-label">{{ $label }}</label>
                        <input
                            name="{{ $field }}"
                            type="{{ $field === 'email' ? 'email' : ($field === 'website' ? 'url' : 'text') }}"
                            class="form-control"
                            value="{{ old($field, $client->$field) }}"
                            @if($field === 'company_name') required @endif
                        >
                    </div>
                @endforeach

                <div class="col-md-6">
                    <label class="form-label">Account manager</label>
                    <select name="account_manager_id" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('account_manager_id', $client->account_manager_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active', 'inactive', 'prospect'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $client->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $client->address) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>

            <button class="btn btn-primary mt-4">Save Client</button>
        </form>
    </x-card>
@endsection
