@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
            <h1 class="page-title">Account Settings</h1>
            <p class="page-subtitle">Update your profile, password, and preferences.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            <nav class="nav flex-column settings-nav gap-1">
                <a class="nav-link {{ request()->is('profile/edit') && ! request()->has('tab') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person"></i> Profile
                </a>
                <a class="nav-link" href="{{ route('profile.edit', ['tab' => 'security']) }}">
                    <i class="bi bi-shield-lock"></i> Security
                </a>
                <a class="nav-link" href="{{ route('profile.edit', ['tab' => 'preferences']) }}">
                    <i class="bi bi-sliders"></i> Preferences
                </a>
            </nav>
        </div>

        <div class="col-12 col-lg-9">
            @if (request('tab') === 'security')
                <x-card title="Change Password" subtitle="Use a strong password you don't use elsewhere" icon="bi-shield-lock">
                    <x-form :action="route('profile.password')" method="PATCH" cancel-url="{{ route('profile.edit') }}">
                        <x-form.input
                            label="Current Password"
                            name="current_password"
                            type="password"
                            :required="true"
                        />
                        <x-form.input
                            label="New Password"
                            name="password"
                            type="password"
                            :required="true"
                            help="Minimum 8 characters with at least one letter and one number."
                        />
                        <x-form.input
                            label="Confirm New Password"
                            name="password_confirmation"
                            type="password"
                            :required="true"
                        />
                    </x-form>
                </x-card>
            @elseif (request('tab') === 'preferences')
                <x-card title="Preferences" subtitle="Personalize how the system works for you" icon="bi-sliders">
                    <x-form :action="route('profile.preferences')" method="PATCH" cancel-url="{{ route('profile.edit') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.select
                                    label="Theme"
                                    name="theme"
                                    :options="\App\Enums\Theme::options()"
                                    :selected="$user->theme"
                                    :required="true"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.select
                                    label="Language"
                                    name="language"
                                    :options="['en' => 'English']"
                                    :selected="$user->language"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.select
                                    label="Timezone"
                                    name="timezone"
                                    :options="\DateTimeZone::listIdentifiers(\DateTimeZone::ALL)"
                                    :selected="$user->timezone ?? config('app.timezone')"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.select
                                    label="Date Format"
                                    name="date_format"
                                    :options="[
                                        'd M, Y' => now()->format('d M, Y'),
                                        'M d, Y' => now()->format('M d, Y'),
                                        'd/m/Y' => now()->format('d/m/Y'),
                                        'Y-m-d' => now()->format('Y-m-d'),
                                    ]"
                                    :selected="$user->date_format"
                                />
                            </div>
                        </div>
                    </x-form>
                </x-card>
            @else
                <x-card title="Profile Information" subtitle="Update your personal details" icon="bi-person">
                    <x-form :action="route('profile.update')" method="PATCH" cancel-url="{{ route('profile.show') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.input
                                    label="Full Name"
                                    name="name"
                                    :value="$user->name"
                                    :required="true"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input
                                    label="Email Address"
                                    name="email"
                                    type="email"
                                    :value="$user->email"
                                    :required="true"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input
                                    label="Phone Number"
                                    name="phone"
                                    :value="$user->phone"
                                    placeholder="+1 555 000 0000"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input
                                    label="Job Title"
                                    name="job_title"
                                    :value="$user->job_title"
                                    placeholder="e.g. Senior Developer"
                                />
                            </div>
                            <div class="col-12">
                                <x-form.input
                                    label="Department"
                                    name="department_label"
                                    :value="$user->department_label"
                                    placeholder="e.g. Engineering"
                                />
                            </div>
                            <div class="col-12">
                                <x-form.input
                                    label="Bio"
                                    name="bio"
                                    type="textarea"
                                    :value="$user->bio"
                                    rows="4"
                                    placeholder="Tell us a little about yourself..."
                                />
                            </div>
                        </div>
                    </x-form>
                </x-card>

                <div class="mt-4">
                    <x-card title="Profile Photo" subtitle="Upload a profile picture" icon="bi-camera">
                        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" data-loading>
                            @csrf

                            <div class="d-flex align-items-center gap-3">
                                @if ($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" class="avatar-lg rounded-circle" alt="">
                                @else
                                    <span class="avatar-fallback lg">{{ initials($user->name) }}</span>
                                @endif
                                <div class="flex-grow-1">
                                    <input
                                        type="file"
                                        class="form-control"
                                        name="avatar"
                                        accept="image/jpeg,image/png,image/webp"
                                        @error('avatar') is-invalid @enderror
                                    >
                                    @error('avatar')
                                        <div class="invalid-feedback d-flex"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
                                </div>
                                <button type="submit" class="btn btn-soft-primary" data-loading-text="Uploading...">
                                    <i class="bi bi-upload me-1"></i>Upload
                                </button>
                            </div>
                        </form>
                    </x-card>
                </div>
            @endif
        </div>
    </div>
@endsection
