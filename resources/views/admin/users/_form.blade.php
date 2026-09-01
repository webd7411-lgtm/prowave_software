@props([
    'user' => null,
    'roles' => collect(),
    'departments' => collect(),
    'managers' => [],
    'statuses' => [],
    'route' => null,
    'method' => 'POST',
])

<x-form :action="$route" :method="$method" cancel-url="{{ route('admin.users.index') }}">
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-account" type="button">Account</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button">Personal</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-work" type="button">Work</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-address" type="button">Address & Emergency</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-account" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.input label="Full Name" name="name" :value="$user?->name" :required="true" autofocus />
                </div>
                <div class="col-md-6">
                    <x-form.input label="Email Address" name="email" type="email" :value="$user?->email" :required="true" />
                </div>
                <div class="col-md-6">
                    <x-form.input label="Username" name="username" :value="$user?->username" help="Used for sign-in. Auto-generated if left blank." />
                </div>
                <div class="col-md-6">
                    <x-form.input label="Employee ID" name="employee_id" :value="$user?->employee_id" help="Auto-generated if left blank." />
                </div>
                <div class="col-md-6">
                    <x-form.input
                        label="{{ $user ? 'New Password' : 'Password' }}"
                        name="password"
                        type="password"
                        :required="! $user"
                        help="Minimum 8 characters with a letter and a number.{{ $user ? ' Leave blank to keep current.' : '' }}"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input label="Confirm Password" name="password_confirmation" type="password" :required="! $user" />
                </div>
                <div class="col-md-6">
                    <x-form.select
                        label="Roles"
                        name="roles"
                        :options="$roles instanceof \Illuminate\Support\Collection ? $roles->pluck('name', 'id')->all() : $roles"
                        :selected="$user ? $user->roles->pluck('id')->merge($user->roles->pluck('name'))->all() : []"
                        :multiple="true"
                        :select2="true"
                        placeholder="Select one or more roles"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.select
                        label="Status"
                        name="status"
                        :options="$statuses"
                        :selected="$user?->status->value ?? 'active'"
                        :required="true"
                    />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-personal" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.input label="Phone Number" name="phone" :value="$user?->phone" placeholder="+1 555 000 0000" />
                </div>
                <div class="col-md-6">
                    <x-form.input label="CNIC / National ID" name="cnic" :value="$user?->cnic" />
                </div>
                <div class="col-md-6">
                    <x-form.select
                        label="Gender"
                        name="gender"
                        :options="\App\Enums\Gender::options()"
                        :selected="$user?->gender?->value"
                        placeholder="Select gender"
                    />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" class="form-control" name="dob" data-flatpickr data-flatpickr-max="today" value="{{ $user?->dob?->format('Y-m-d') }}">
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-work" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.select
                        label="Department"
                        name="department_id"
                        :options="$departments->pluck('name', 'id')->all()"
                        :selected="$user?->department_id"
                        placeholder="Select department"
                        select2
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input label="Designation" name="designation" :value="$user?->designation" placeholder="e.g. Senior Software Engineer" />
                </div>
                <div class="col-md-6">
                    <x-form.select
                        label="Reporting Manager"
                        name="reporting_manager_id"
                        :options="$managers"
                        :selected="$user?->reporting_manager_id"
                        placeholder="Select manager"
                        select2
                    />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Joining Date</label>
                    <input type="text" class="form-control" name="joining_date" data-flatpickr value="{{ $user?->joining_date?->format('Y-m-d') }}">
                </div>
                <div class="col-12">
                    <x-form.input label="Bio" name="bio" type="textarea" :value="$user?->bio" rows="3" placeholder="Brief professional summary..." />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-address" role="tabpanel">
            <div class="row g-3">
                <div class="col-12">
                    <x-form.input label="Street Address" name="address" :value="$user?->address" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="City" name="city" :value="$user?->city" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="Province / State" name="province" :value="$user?->province" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="Country" name="country" :value="$user?->country" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="Postal Code" name="postal_code" :value="$user?->postal_code" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="Emergency Contact (Name)" name="emergency_contact" :value="$user?->emergency_contact" />
                </div>
                <div class="col-md-4">
                    <x-form.input label="Emergency Phone" name="emergency_phone" :value="$user?->emergency_phone" />
                </div>
                <div class="col-12">
                    <x-form.input label="Notes" name="notes" type="textarea" :value="$user?->notes" rows="3" />
                </div>
            </div>
        </div>
    </div>
</x-form>
