@extends('layouts.app')
@section('title', $lead->exists ? 'Edit Lead' : 'New Lead')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.leads.index') }}">Leads</a></li>
                    <li class="breadcrumb-item active">{{ $lead->exists ? 'Edit' : 'New' }}</li>
                </ol>
            </nav>
            <h1 class="page-title">{{ $lead->exists ? 'Edit Lead' : 'New Sales Lead' }}</h1>
            <p class="page-subtitle">Fill in the details below. Fields marked with * are required.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-8">
            <x-card body-class="p-4">
                <form method="POST" action="{{ $action }}" id="lead-form">
                    @csrf
                    @if ($lead->exists) @method('PATCH') @endif

                    <div class="row g-3">
                        {{-- Phone Number (Important) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $lead->phone) }}" required maxlength="50"
                                       placeholder="+92 300 1234567">
                            </div>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Business / Service (Optional) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Business / Service</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                <input type="text" name="service_interested" class="form-control @error('service_interested') is-invalid @enderror"
                                       value="{{ old('service_interested', $lead->service_interested) }}" maxlength="255"
                                       placeholder="e.g. Web Development, Mobile App, SEO">
                            </div>
                            @error('service_interested') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Name (Optional) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Contact Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                                       value="{{ old('contact_person', $lead->contact_person) }}" maxlength="255"
                                       placeholder="John Doe">
                            </div>
                            @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Company Name (Required) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-buildings"></i></span>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                       value="{{ old('company_name', $lead->company_name) }}" required maxlength="255"
                                       placeholder="Acme Corp">
                            </div>
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Address (Optional) --}}
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address', $lead->address) }}" maxlength="2000"
                                       placeholder="Office address, city">
                            </div>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Extra fields (collapsible) --}}
                        <div class="col-12">
                            <div class="accordion" id="leadExtra">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fs-sm fw-semibold text-muted" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#leadExtraBody">
                                            <i class="bi bi-plus-circle me-2"></i>More Details (optional)
                                        </button>
                                    </h2>
                                    <div id="leadExtraBody" class="accordion-collapse collapse" data-bs-parent="#leadExtra">
                                        <div class="accordion-body pt-0">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">WhatsApp</label>
                                                    <input type="text" name="whatsapp" class="form-control"
                                                           value="{{ old('whatsapp', $lead->whatsapp) }}" placeholder="+92 300 1234567">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                           value="{{ old('email', $lead->email) }}" placeholder="john@example.com">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Website</label>
                                                    <input type="url" name="website" class="form-control"
                                                           value="{{ old('website', $lead->website) }}" placeholder="https://example.com">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Estimated Budget</label>
                                                    <input type="number" name="estimated_budget" class="form-control"
                                                           value="{{ old('estimated_budget', $lead->estimated_budget) }}" min="0" step="0.01">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Expected Closing Date</label>
                                                    <input type="date" name="expected_closing_date" class="form-control"
                                                           value="{{ old('expected_closing_date', $lead->expected_closing_date?->format('Y-m-d')) }}">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Assigned To</label>
                                                    @can('leads.view_all')
                                                    <select name="owner_id" class="form-select">
                                                        <option value="">Unassigned</option>
                                                        @foreach ($owners as $owner)
                                                            <option value="{{ $owner->id }}" @selected(old('owner_id', $lead->owner_id) == $owner->id)>{{ $owner->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @else
                                                    <input type="text" class="form-control" value="{{ $lead->owner?->name ?? auth()->user()->name }}" disabled>
                                                    <input type="hidden" name="owner_id" value="{{ $lead->owner_id ?? auth()->id() }}">
                                                    @endcan
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="3" maxlength="5000">{{ old('notes', $lead->notes) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2 pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i>{{ $lead->exists ? 'Update Lead' : 'Save Lead' }}
                            </button>
                            <a href="{{ route('crm.leads.index') }}" class="btn btn-outline">Cancel</a>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar: Pipeline status --}}
        <div class="col-12 col-lg-4">
            <x-card title="Pipeline" subtitle="Lead status and priority" icon="bi-funnel">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" form="lead-form" class="form-select" id="lead-status">
                            @foreach ($statuses as $v => $l)
                                <option value="{{ $v }}" @selected(old('status', $lead->status ?: 'new') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <div class="form-text mt-1">Choose <strong>Meeting Scheduled</strong> to set the meeting date &amp; time — Sales Manager and Admin are notified automatically.</div>
                    </div>
                    <div class="col-12 meeting-fields {{ old('status', $lead->status ?: 'new') === 'meeting_scheduled' ? '' : 'd-none' }}">
                        <div class="p-3 rounded border border-success border-opacity-50" style="background: rgba(25,135,84,.06);">
                            <label class="form-label fw-semibold text-success"><i class="bi bi-calendar-event me-1"></i>Meeting Schedule</label>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small">Date</label>
                                    <input type="date" name="follow_up_date" form="lead-form" class="form-control" value="{{ old('follow_up_date') }}" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small">Time</label>
                                    <input type="time" name="follow_up_time" form="lead-form" class="form-control" value="{{ old('follow_up_time') }}">
                                </div>
                            </div>
                            <div class="form-text mt-2">A reminder alert (in-app + WhatsApp, if configured) goes to you, the Sales Manager and Admin at this time.</div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sel = document.getElementById('lead-status');
        var box = document.querySelector('.meeting-fields');
        if (!sel || !box) return;
        function sync() { box.classList.toggle('d-none', sel.value !== 'meeting_scheduled'); }
        sel.addEventListener('change', sync);
        sync();
    });
</script>
@endpush
