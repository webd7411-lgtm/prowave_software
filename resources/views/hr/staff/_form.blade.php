@php
    $employee = $employee ?? null;
    $isEdit = ! is_null($employee);
@endphp

<x-form
    :action="$isEdit ? route('hr.staff.update', $employee) : route('hr.staff.store')"
    :method="$isEdit ? 'PATCH' : 'POST'"
    :multipart="true"
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
            <x-form.select label="Gender" name="gender" :selected="$employee?->gender?->value" placeholder="Select gender" :options="['male' => 'Male', 'female' => 'Female']" />
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

        <div class="col-12 col-md-6">
            <div class="mb-3">
                <label for="avatar-input" class="form-label">Employee Profile Image</label>
                <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" class="form-control @error('avatar') is-invalid @enderror">
                <div class="form-text">Recommended: square image (JPG, PNG, WEBP, max 2MB).</div>
                @if ($isEdit && $employee->avatar_url)
                    <div class="mt-2">
                        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="rounded-circle object-fit-cover" style="width: 48px; height: 48px;">
                    </div>
                @endif
                @error('avatar')
                    <div class="invalid-feedback d-flex"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="mb-3">
                <label for="cover_photo-input" class="form-label">Employee Cover Photo</label>
                <input type="file" id="cover_photo-input" name="cover_photo" accept="image/jpeg,image/png,image/webp" class="form-control @error('cover_photo') is-invalid @enderror">
                <div class="form-text">Recommended: wide banner image (JPG, PNG, WEBP, max 4MB).</div>
                @if ($isEdit && $employee->cover_photo_url)
                    <div class="mt-2">
                        <img src="{{ $employee->cover_photo_url }}" alt="Cover Photo" class="rounded object-fit-cover" style="width: 96px; height: 48px;">
                    </div>
                @endif
                @error('cover_photo')
                    <div class="invalid-feedback d-flex"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12">
            <hr class="my-1">
            <h6 class="section-label mt-2"><i class="bi bi-briefcase me-1"></i>Employment Information</h6>
        </div>

        <div class="col-12 col-md-4">
            <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :selected="$employee?->department_id" placeholder="Select department" select2 />
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-3">
                <label for="designation" class="form-label">
                    Designation
                </label>
                <select
                    id="designation"
                    name="designation"
                    class="form-select select2"
                    data-placeholder="Select designation"
                >
                    <option value="">Select designation</option>
                    @foreach ($designations as $designation)
                        <option 
                            value="{{ $designation->name }}" 
                            data-department-id="{{ $designation->department_id }}"
                            data-role-name="{{ $designation->role?->name ?? '' }}"
                            @selected(old('designation', $employee?->designation) === $designation->name)
                        >
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text" id="designation-role-hint">
                    <i class="bi bi-shield-check me-1"></i>System Role: <span id="designation-role-name" class="fw-600">Auto-assigned from designation</span>
                </div>
            </div>
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

        <div class="col-12 col-md-4">
            <x-form.select label="Employee Status" name="employee_status" :options="$statuses" :selected="$employee?->employee_status?->value ?? 'active'" />
        </div>
        <div class="col-12 col-md-4">
            <x-form.input label="Emergency Contact" name="emergency_contact" :value="$employee?->emergency_contact" icon="bi-person-exclamation" placeholder="Name" />
        </div>
        <div class="col-12 col-md-4">
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

        <div class="col-12 mt-2">
            <div class="alert alert-soft-info border-0 mb-0 d-flex align-items-start gap-2">
                <i class="bi bi-info-circle flex-shrink-0 mt-0.5"></i>
                <div class="fs-sm">
                    <strong>Password Requirements:</strong>
                    <ul class="mb-0 ps-3 mt-1 small">
                        <li>Minimum <strong>8 characters</strong></li>
                        <li>At least <strong>one letter</strong> (a-z or A-Z)</li>
                        <li>At least <strong>one number</strong> (0-9)</li>
                        <li>Must match <strong>Confirm Password</strong> field exactly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const designationSelect = document.getElementById('designation');
    const roleNameEl = document.getElementById('designation-role-name');
    if (!designationSelect || !roleNameEl) return;

    function updateDesignationRoleHint() {
        const selected = designationSelect.options[designationSelect.selectedIndex];
        const roleName = selected ? selected.getAttribute('data-role-name') : '';
        roleNameEl.textContent = roleName ? roleName : 'Auto-assigned from designation (none mapped)';
    }

    updateDesignationRoleHint();
    designationSelect.addEventListener('change', updateDesignationRoleHint);
    if (window.jQuery) {
        window.jQuery(designationSelect).on('change.select2 select2:select', updateDesignationRoleHint);
    }
});
</script>
@endpush
