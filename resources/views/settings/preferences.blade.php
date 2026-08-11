@extends('layouts.app')

@section('title', 'System Preferences')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.general') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">System Preferences</li>
                </ol>
            </nav>
            <h1 class="page-title">System Preferences</h1>
            <p class="page-subtitle">Global defaults for formatting, working schedule and calculations.</p>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-12 col-lg-3">
            @include('settings.partials.nav')
        </div>

        <div class="col-12 col-lg-9">
            <x-card title="System Preferences" subtitle="Defaults applied across the whole system" icon="bi-gear-wide-connected">
                <form method="POST" action="{{ route('settings.preferences.update') }}" data-loading>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', setting('general.currency', 'USD')) }}" required maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                @foreach (['UTC', 'Africa/Cairo', 'Asia/Dubai', 'Asia/Riyadh', 'Europe/London', 'America/New_York', 'Asia/Kolkata', 'Asia/Karachi', 'Asia/Tehran', 'Africa/Lagos', 'Africa/Nairobi'] as $tz)
                                    <option value="{{ $tz }}" {{ setting('general.timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select">
                                <option value="en" selected>English</option>
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
                        <div class="col-md-4">
                            <label class="form-label">Default Theme</label>
                            <select name="default_theme" class="form-select">
                                @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'Follow system'] as $value => $label)
                                    <option value="{{ $value }}" {{ setting('general.default_theme', 'light') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default Tax (%)</label>
                            <input type="number" name="default_tax" class="form-control" value="{{ old('default_tax', setting('general.default_tax', 0)) }}" min="0" max="100" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Working Hours / Day</label>
                            <input type="number" name="company_working_hours" class="form-control" value="{{ old('company_working_hours', setting('general.working_hours', 8)) }}" min="1" max="24">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Records Per Page</label>
                            <select name="pagination_size" class="form-select">
                                @foreach ([10, 15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) setting('general.pagination_size', 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block mb-2">Working Days</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                    @php $selected = in_array($day, setting('general.working_days', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']), true); @endphp
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" id="wd_{{ $day }}" value="{{ $day }}" {{ $selected ? 'checked' : '' }}>
                                        <label class="form-check-label" for="wd_{{ $day }}">{{ $day }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block mb-2">Weekend Days</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach (['Sat', 'Sun', 'Fri'] as $day)
                                    @php $selected = in_array($day, setting('general.weekend_days', ['Sat', 'Sun']), true); @endphp
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="weekend_days[]" id="we_{{ $day }}" value="{{ $day }}" {{ $selected ? 'checked' : '' }}>
                                        <label class="form-check-label" for="we_{{ $day }}">{{ $day }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Preferences</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
