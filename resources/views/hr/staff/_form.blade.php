@php
    $employee = $employee ?? null;
    $isEdit = ! is_null($employee);
@endphp

<x-form
    :action="$isEdit ? route('hr.staff.update', $employee) : route('hr.staff.store')"
    :method="$isEdit ? 'PATCH' : 'POST'"
    :submit="$isEdit ? 'Update Staff' : 'Add Staff'"
    :cancel-url="route('hr.staff.index')"
>
    <div class="row g-3">
        <div class="col-12">
            <h6 class="section-label"><i class="bi bi-person me-1"></i>Personal Information</h6>
        </div>

        <div class="col-12 col-md-6">
            <x-form.input label="Full Name" name="name" :value="$employee?->name" required icon="bi-person" placeholder="e.g. John Smith" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Employee ID" name="employee_id" :value="$employee?->employee_id" icon="bi-person-badge" placeholder="EMP-0000" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Father Name" name="father_name" :value="$employee?->father_name" icon="bi-person-lines-fill" />
        </div>

        <div class="col-12 col-md-6">
            <x-form.input label="Email" name="email" type="email" :value="$employee?->email" required icon="bi-envelope" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Phone" name="phone" :value="$employee?->phone" icon="bi-telephone" placeholder="+92 300 0000000" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="WhatsApp" name="whatsapp" :value="$employee?->whatsapp" icon="bi-whatsapp" placeholder="+92 300 0000000" />
        </div>

        <div class="col-12 col-md-3">
            <x-form.select label="Gender" name="gender" :selected="$employee?->gender?->value" placeholder="Select gender" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Date of Birth" name="dob" type="date" :value="$employee?->dob?->format('Y-m-d')" icon="bi-calendar3" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="CNIC" name="cnic" :value="$employee?->cnic" icon="bi-card-text" placeholder="XXXXX-XXXXXXX-X" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="City" name="city" :value="$employee?->city" icon="bi-geo" />
        </div>

        <div class="col-12">
            <x-form.input label="Address" name="address" :value="$employee?->address" icon="bi-geo-alt" placeholder="Street, area, town" />
        </div>

        <div class="col-12">
            <hr class="my-1">
            <h6 class="section-label mt-2"><i class="bi bi-briefcase me-1"></i>Employment Information</h6>
        </div>

        <div class="col-12 col-md-4">
            <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :selected="$employee?->department_id" placeholder="Select department" select2 />
        </div>
        <div class="col-12 col-md-4">
            <x-form.select label="Designation" name="designation" :options="$designations->pluck('name', 'name')" :selected="$employee?->designation" placeholder="Select designation" select2 />
        </div>
        <div class="col-12 col-md-4">
            <x-form.select label="Reporting Manager" name="reporting_manager_id" :options="$managers" :selected="$employee?->reporting_manager_id" placeholder="Select manager" select2 />
        </div>

        <div class="col-12 col-md-3">
            <x-form.select label="Employment Type" name="employment_type" :options="$employmentTypes" :selected="$employee?->employment_type?->value" placeholder="Select type" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.select label="Work Shift" name="shift_id" :options="$shifts->pluck('name', 'id')" :selected="$employee?->shift_id" placeholder="Select shift" select2 />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Salary" name="salary" type="number" step="0.01" min="0" :value="$employee?->salary" icon="bi-cash" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Joining Date" name="joining_date" type="date" :value="$employee?->joining_date?->format('Y-m-d')" icon="bi-calendar-check" />
        </div>

        <div class="col-12 col-md-3">
            <x-form.select label="Employee Status" name="employee_status" :options="$statuses" :selected="$employee?->employee_status?->value ?? 'active'" />
        </div>

        <div class="col-12 col-md-9">
            <x-form.input label="Emergency Contact" name="emergency_contact" :value="$employee?->emergency_contact" icon="bi-person-exclamation" placeholder="Name" />
        </div>
        <div class="col-12 col-md-3">
            <x-form.input label="Emergency Phone" name="emergency_phone" :value="$employee?->emergency_phone" icon="bi-telephone-forward" placeholder="+92 300 0000000" />
        </div>

        <div class="col-12">
            <x-form.input label="Notes" name="notes" type="textarea" :value="$employee?->notes" rows="3" placeholder="Internal notes about this employee..." />
        </div>

        <div class="col-12 col-md-6">
            @if ($isEdit)
                <x-form.input label="New Password (leave blank to keep current)" name="password" type="password" placeholder="••••••••" />
            @else
                <x-form.input label="Password" name="password" type="password" required placeholder="At least 8 characters with a letter and number" />
            @endif
        </div>
        <div class="col-12 col-md-6">
            <x-form.input label="Confirm Password" name="password_confirmation" type="password" placeholder="Re-enter password" />
        </div>
    </div>
</x-form>
