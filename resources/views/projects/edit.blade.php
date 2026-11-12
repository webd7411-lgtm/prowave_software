@extends('layouts.app')

@section('title', 'Edit Project ' . $project->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4">
        <div>
            <span class="badge bg-light text-muted border mb-1">{{ $project->project_code }}</span>
            <h1 class="h3 fw-bold text-dark mb-1">Edit Project: {{ $project->name }}</h1>
            <p class="text-muted mb-0 fs-sm">Update core project configuration, dates, manager, budget, and priority.</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check form input errors:</div>
            <ul class="mb-0 fs-sm ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Project Details</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Project Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold fs-sm">Client</label>
                                <select name="client_id" class="form-select">
                                    <option value="">-- Internal / No Client --</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}" {{ old('client_id', $project->client_id) == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold fs-sm">Project Type <span class="text-danger">*</span></label>
                                <select name="project_type" class="form-select" required>
                                    @foreach(['Website', 'Web Application', 'Mobile Application', 'Desktop Software', 'ERP', 'CRM', 'E-commerce', 'Maintenance', 'SEO', 'Digital Marketing', 'Other'] as $type)
                                        <option value="{{ $type }}" {{ old('project_type', $project->project_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Description & Scope</label>
                                <textarea name="description" class="form-control" rows="5">{{ old('description', $project->description) }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Internal Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Private internal notes for PMs & Admins...">{{ old('notes', $project->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-gear text-primary me-2"></i>Status & Allocation</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled', 'Overdue'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $project->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach(['Low', 'Medium', 'High', 'Urgent'] as $pr)
                                    <option value="{{ $pr }}" {{ old('priority', $project->priority) === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Project Manager</label>
                            <select name="project_manager_id" class="form-select">
                                <option value="">-- Unassigned --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('project_manager_id', $project->project_manager_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Deadline</label>
                                <input type="date" name="deadline" class="form-control" value="{{ old('deadline', $project->deadline ? $project->deadline->format('Y-m-d') : '') }}">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-sm">Billing Model <span class="text-danger">*</span></label>
                                <select name="billing_type" id="billingTypeSelect" class="form-select" required>
                                    <option value="one_time" {{ old('billing_type', $project->billing_type ?? 'one_time') === 'one_time' ? 'selected' : '' }}>One-Time (Fixed Price)</option>
                                    <option value="monthly" {{ old('billing_type', $project->billing_type) === 'monthly' ? 'selected' : '' }}>Monthly (Retainer / Subscription)</option>
                                    <option value="annually" {{ old('billing_type', $project->billing_type) === 'annually' ? 'selected' : '' }}>Annually (Yearly Contract / Renewal)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Recurring Billing Extra Options (Shown for Monthly/Annually) --}}
                        <div id="recurringBillingBox" class="p-3 mb-3 rounded border bg-light" style="display: none;">
                            <div class="fw-bold fs-xs text-uppercase text-primary mb-2">
                                <i class="bi bi-arrow-repeat me-1"></i><span id="recurringBoxTitle">Recurring Billing Settings</span>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold fs-xs">Recurring Fee / Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="recurring_amount" id="recurringAmountInput" class="form-control form-control-sm" placeholder="0.00" value="{{ old('recurring_amount', $project->recurring_amount) }}">
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold fs-xs">Cycle Start Date</label>
                                    <input type="date" name="billing_start_date" class="form-control form-control-sm" value="{{ old('billing_start_date', $project->billing_start_date ? $project->billing_start_date->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold fs-xs" id="nextBillingDateLabel">Next Invoice Date</label>
                                    <input type="date" name="next_billing_date" class="form-control form-control-sm" value="{{ old('next_billing_date', $project->next_billing_date ? $project->next_billing_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold fs-xs" id="alertDayLabel">Monthly Alert Day (Day of Month 1-31)</label>
                                <input type="number" min="1" max="31" name="billing_alert_day" id="billingAlertDayInput" class="form-control form-control-sm" placeholder="5" value="{{ old('billing_alert_day', $project->billing_alert_day ?? 5) }}">
                                <div class="form-text fs-xs" id="alertDayHelp">Is date ko unpaid client ka alert/notification generate hoga.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-sm">Currency</label>
                            <select name="currency" class="form-select" id="currencySelect">
                                @php
                                    $currencies = ['PKR','USD','EUR','GBP','AED','SAR','INR','CAD','AUD','JPY','CNY','CHF'];
                                    $selectedCurrency = old('currency', $project->currency ?? setting('general.currency', 'PKR'));
                                @endphp
                                @foreach($currencies as $cur)
                                    <option value="{{ $cur }}" {{ $selectedCurrency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Budget <span class="text-muted fs-xs" id="budgetCurrencyLabel">({{ old('currency', $project->currency ?? setting('general.currency','PKR')) }})</span></label>
                                <input type="number" step="0.01" min="0" name="budget" class="form-control" value="{{ old('budget', $project->budget) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold fs-sm">Est. Hours</label>
                                <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control" value="{{ old('estimated_hours', $project->estimated_hours) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-2 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Update Project
                        </button>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-light w-100 text-muted">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currencySelect = document.getElementById('currencySelect');
        const budgetCurrencyLabel = document.getElementById('budgetCurrencyLabel');
        if (currencySelect && budgetCurrencyLabel) {
            currencySelect.addEventListener('change', function () {
                budgetCurrencyLabel.textContent = '(' + this.value + ')';
            });
        }

        // Dynamic Recurring Billing Settings
        const billingTypeSelect = document.getElementById('billingTypeSelect');
        const recurringBox = document.getElementById('recurringBillingBox');
        const recurringTitle = document.getElementById('recurringBoxTitle');
        const nextBillingLabel = document.getElementById('nextBillingDateLabel');
        const alertDayLabel = document.getElementById('alertDayLabel');
        const alertDayHelp = document.getElementById('alertDayHelp');
        const alertDayInput = document.getElementById('billingAlertDayInput');

        function toggleBillingFields() {
            if (!billingTypeSelect || !recurringBox) return;
            const val = billingTypeSelect.value;
            if (val === 'monthly') {
                recurringBox.style.display = 'block';
                recurringTitle.textContent = 'Monthly Retainer / Subscription Settings';
                nextBillingLabel.textContent = 'Next Monthly Bill Date';
                alertDayLabel.textContent = 'Monthly Alert Day (Day of Month, e.g. 5)';
                alertDayHelp.textContent = 'Har mahine is date ko unpaid hone par notification aayegi.';
                if (!alertDayInput.value || alertDayInput.value > 31) alertDayInput.value = 5;
            } else if (val === 'annually') {
                recurringBox.style.display = 'block';
                recurringTitle.textContent = 'Annual Contract / Renewal Settings';
                nextBillingLabel.textContent = 'Year Completion / Renewal Date';
                alertDayLabel.textContent = 'Alert Days Before Expiry (e.g. 7 or 15 days)';
                alertDayHelp.textContent = 'Year complete hone se itne din pehle expiry/payment reminder aayega.';
                if (!alertDayInput.value) alertDayInput.value = 7;
            } else {
                recurringBox.style.display = 'none';
            }
        }

        if (billingTypeSelect) {
            billingTypeSelect.addEventListener('change', toggleBillingFields);
            toggleBillingFields();
        }
    });
</script>
@endpush
@endsection
