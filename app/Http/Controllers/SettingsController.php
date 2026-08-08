<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    public function general(): View
    {
        return view('settings.general', [
            'settings' => $this->settings->group('general'),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'allow_registration' => ['nullable', 'boolean'],
        ]);

        $this->settings->setMany([
            'general.name' => $data['company_name'],
            'general.email' => $data['company_email'] ?? '',
            'general.phone' => $data['company_phone'] ?? '',
            'general.address' => $data['company_address'] ?? '',
            'general.website' => $data['company_website'] ?? '',
            'general.currency' => $data['currency'],
            'general.allow_registration' => (bool) ($data['allow_registration'] ?? false),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'General settings saved successfully.',
        ]);
    }

    public function security(): View
    {
        return view('settings.security');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'session_lifetime' => ['required', 'integer', 'min:15', 'max:1440'],
            'lockout_threshold' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $this->settings->setMany([
            'security.session_lifetime' => (int) $data['session_lifetime'],
            'security.lockout_threshold' => (int) $data['lockout_threshold'],
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Security settings saved successfully.',
        ]);
    }
}
