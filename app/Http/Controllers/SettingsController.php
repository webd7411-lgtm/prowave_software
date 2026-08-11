<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\AuditService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    // ─── General ───────────────────────────────────────────────────────────────

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
            'company_whatsapp' => ['nullable', 'string', 'max:30'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'time_format' => ['nullable', 'string', 'max:10'],
            'fiscal_year_start' => ['nullable', 'string', 'max:10'],
            'allow_registration' => ['nullable', 'boolean'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'company_favicon' => ['nullable', 'image', 'mimes:png,ico,svg,webp', 'max:1024'],
        ]);

        $this->settings->setMany([
            'general.name' => $data['company_name'],
            'general.email' => $data['company_email'] ?? '',
            'general.phone' => $data['company_phone'] ?? '',
            'general.whatsapp' => $data['company_whatsapp'] ?? '',
            'general.website' => $data['company_website'] ?? '',
            'general.address' => $data['company_address'] ?? '',
            'general.city' => $data['company_city'] ?? '',
            'general.country' => $data['company_country'] ?? '',
            'general.tax_number' => $data['tax_number'] ?? '',
            'general.registration_number' => $data['registration_number'] ?? '',
            'general.currency' => $data['currency'],
            'general.timezone' => $data['timezone'] ?? config('app.timezone'),
            'general.date_format' => $data['date_format'] ?? 'd M, Y',
            'general.time_format' => $data['time_format'] ?? 'h:i A',
            'general.fiscal_year_start' => $data['fiscal_year_start'] ?? '01-01',
            'general.allow_registration' => (bool) ($data['allow_registration'] ?? false),
        ]);

        if ($request->hasFile('company_logo')) {
            $this->settings->set('general.logo', $this->storeUploaded($request->file('company_logo'), 'branding', 'logo'));
        }

        if ($request->hasFile('company_favicon')) {
            $this->settings->set('general.favicon', $this->storeUploaded($request->file('company_favicon'), 'branding', 'favicon'));
        }

        $this->audit('General settings updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'General settings saved successfully.',
        ]);
    }

    // ─── Company Profile ───────────────────────────────────────────────────────

    public function profile(): View
    {
        return view('settings.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_whatsapp' => ['nullable', 'string', 'max:30'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_country' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'time_format' => ['nullable', 'string', 'max:10'],
            'fiscal_year_start' => ['nullable', 'string', 'max:10'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'company_favicon' => ['nullable', 'image', 'mimes:png,ico,svg,webp', 'max:1024'],
        ]);

        $this->settings->setMany([
            'general.name' => $data['company_name'],
            'general.email' => $data['company_email'] ?? '',
            'general.phone' => $data['company_phone'] ?? '',
            'general.whatsapp' => $data['company_whatsapp'] ?? '',
            'general.website' => $data['company_website'] ?? '',
            'general.address' => $data['company_address'] ?? '',
            'general.city' => $data['company_city'] ?? '',
            'general.country' => $data['company_country'] ?? '',
            'general.tax_number' => $data['tax_number'] ?? '',
            'general.registration_number' => $data['registration_number'] ?? '',
            'general.currency' => $data['currency'],
            'general.timezone' => $data['timezone'] ?? config('app.timezone'),
            'general.date_format' => $data['date_format'] ?? 'd M, Y',
            'general.time_format' => $data['time_format'] ?? 'h:i A',
            'general.fiscal_year_start' => $data['fiscal_year_start'] ?? '01-01',
        ]);

        if ($request->hasFile('company_logo')) {
            $this->settings->set('general.logo', $this->storeUploaded($request->file('company_logo'), 'branding', 'logo'));
        }

        if ($request->hasFile('company_favicon')) {
            $this->settings->set('general.favicon', $this->storeUploaded($request->file('company_favicon'), 'branding', 'favicon'));
        }

        $this->audit('Company profile updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Company profile saved successfully.',
        ]);
    }

    // ─── Branding ──────────────────────────────────────────────────────────────

    public function branding(): View
    {
        return view('settings.branding');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'email_footer' => ['nullable', 'string', 'max:1000'],
            'login_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'invoice_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg,webp', 'max:1024'],
        ]);

        $this->settings->setMany([
            'branding.primary_color' => $data['primary_color'] ?? '#2563EB',
            'branding.secondary_color' => $data['secondary_color'] ?? '#6366F1',
            'branding.email_footer' => $data['email_footer'] ?? '',
        ]);

        if ($request->hasFile('login_logo')) {
            $this->settings->set('branding.login_logo', $this->storeUploaded($request->file('login_logo'), 'branding', 'login-logo'));
        }

        if ($request->hasFile('invoice_logo')) {
            $this->settings->set('branding.invoice_logo', $this->storeUploaded($request->file('invoice_logo'), 'branding', 'invoice-logo'));
        }

        if ($request->hasFile('favicon')) {
            $this->settings->set('general.favicon', $this->storeUploaded($request->file('favicon'), 'branding', 'favicon'));
        }

        $this->audit('Branding updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Branding saved successfully.',
        ]);
    }

    // ─── Email / SMTP ──────────────────────────────────────────────────────────

    public function email(): View
    {
        $smtp = $this->settings->group('email');

        if (! empty($smtp['password'])) {
            try {
                $smtp['password'] = Crypt::decryptString($smtp['password']);
            } catch (\Throwable) {
                $smtp['password'] = '';
            }
        }

        return view('settings.email', ['smtp' => $smtp]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
        ]);

        $values = [
            'email.host' => $data['mail_host'] ?? '',
            'email.port' => (int) ($data['mail_port'] ?? 587),
            'email.username' => $data['mail_username'] ?? '',
            'email.encryption' => $data['mail_encryption'] ?? 'tls',
            'email.from_name' => $data['mail_from_name'] ?? '',
            'email.from_address' => $data['mail_from_address'] ?? '',
        ];

        if (! blank($data['mail_password'])) {
            $values['email.password'] = Crypt::encryptString($data['mail_password']);
        }

        $this->settings->setMany($values);
        $this->audit('Email settings updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Email settings saved successfully.',
        ]);
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email_to' => ['required', 'email'],
        ]);

        $smtp = $this->settings->group('email');

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['host'] ?? env('MAIL_HOST', ''),
                'mail.mailers.smtp.port' => (int) ($smtp['port'] ?? env('MAIL_PORT', 587)),
                'mail.mailers.smtp.username' => $smtp['username'] ?? env('MAIL_USERNAME', ''),
                'mail.mailers.smtp.password' => $this->decryptSmtpPassword($smtp['password'] ?? ''),
                'mail.mailers.smtp.encryption' => ($smtp['encryption'] ?? 'tls') === 'none' ? null : ($smtp['encryption'] ?? 'tls'),
                'mail.from.address' => $smtp['from_address'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
                'mail.from.name' => $smtp['from_name'] ?? config('app.name'),
            ]);

            Mail::raw(
                'This is a test email from '.config('app.name').'. If you received this, your email configuration is working correctly. Sent at '.now()->format('Y-m-d H:i:s').'.',
                function ($message) use ($data) {
                    $message->to($data['test_email_to'])
                        ->subject('Test Email — '.config('app.name'));
                }
            );

            $this->audit('Test email sent to '.$data['test_email_to'], 'settings');

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Test email sent successfully to '.$data['test_email_to'].'.',
            ]);
        } catch (\Throwable $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Test email failed: '.$e->getMessage(),
            ]);
        }
    }

    // WhatsApp Notifications

    public function whatsapp(): View
    {
        return view('settings.whatsapp', ['wa' => $this->settings->group('whatsapp')]);
    }

    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'api_url' => ['nullable', 'string', 'max:255'],
            'api_token' => ['nullable', 'string', 'max:255'],
            'default_number' => ['nullable', 'string', 'max:30'],
        ]);

        $this->settings->setMany([
            'whatsapp.api_url' => trim((string) ($data['api_url'] ?? '')),
            'whatsapp.api_token' => trim((string) ($data['api_token'] ?? '')),
            'whatsapp.default_number' => trim((string) ($data['default_number'] ?? '')),
        ]);
        $this->audit('WhatsApp settings updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'WhatsApp settings saved successfully.',
        ]);
    }

    public function testWhatsapp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_wa_number' => ['required', 'string', 'max:30'],
        ]);

        $ok = app(\App\Services\WhatsAppService::class)->send(
            $data['test_wa_number'],
            'Test WhatsApp message from '.app_name().'. Sent at '.now()->format('Y-m-d H:i:s').'.'
        );

        return back()->with('toast', [
            'type' => $ok ? 'success' : 'warning',
            'message' => $ok ? 'Test message sent successfully to '.$data['test_wa_number'].'.' : 'WhatsApp gateway is not configured or unreachable.',
        ]);
    }

    protected function decryptSmtpPassword(?string $encrypted): string
    {
        if (blank($encrypted)) {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    // ─── Notifications ─────────────────────────────────────────────────────────

    public function notifications(): View
    {
        $categories = [
            'lead' => 'Leads & CRM',
            'followup' => 'Follow-ups',
            'client' => 'Clients',
            'project' => 'Projects',
            'task' => 'Tasks',
            'leave' => 'Leave',
            'update' => 'Daily Updates',
            'invoice' => 'Invoices',
            'payment' => 'Payments',
            'expense' => 'Expenses',
            'meeting' => 'Meetings',
            'document' => 'Documents',
            'system' => 'System',
        ];

        $defaults = $this->settings->group('notifications');

        return view('settings.notifications', compact('categories', 'defaults'));
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $categories = ['lead', 'followup', 'client', 'project', 'task', 'leave', 'update', 'invoice', 'payment', 'expense', 'meeting', 'document', 'system'];

        $values = [];
        foreach ($categories as $category) {
            $values["notifications.{$category}"] = (bool) $request->boolean("notify_{$category}");
        }

        $this->settings->setMany($values);
        $this->audit('Notification preferences updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Notification preferences saved.',
        ]);
    }

    // ─── System Preferences ────────────────────────────────────────────────────

    public function preferences(): View
    {
        return view('settings.preferences');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:100'],
            'date_format' => ['required', 'string', 'max:20'],
            'time_format' => ['required', 'string', 'max:10'],
            'pagination_size' => ['required', 'integer', 'min:5', 'max:100'],
            'language' => ['required', 'in:en'],
            'default_theme' => ['required', 'in:light,dark,system'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'default_tax' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'company_working_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $this->settings->setMany([
            'general.currency' => $data['currency'],
            'general.timezone' => $data['timezone'],
            'general.date_format' => $data['date_format'],
            'general.time_format' => $data['time_format'],
            'general.pagination_size' => (int) $data['pagination_size'],
            'general.language' => $data['language'],
            'general.default_theme' => $data['default_theme'],
            'general.working_days' => $data['working_days'],
            'general.weekend_days' => $data['weekend_days'] ?? [],
            'general.default_tax' => (float) ($data['default_tax'] ?? 0),
            'general.working_hours' => (int) ($data['company_working_hours'] ?? 8),
        ]);

        $this->audit('System preferences updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'System preferences saved.',
        ]);
    }

    // ─── Leave Types ───────────────────────────────────────────────────────────

    public function leaveTypes(): View
    {
        return view('settings.leave-types', [
            'leaveTypes' => LeaveType::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function storeLeaveType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        LeaveType::create($data + [
            'code' => $data['code'] ?: strtoupper(substr(str_replace(' ', '_', $data['name']), 0, 10)),
            'is_paid' => (bool) ($data['is_paid'] ?? true),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        $this->audit('Leave type created: '.$data['name'], 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Leave type added.',
        ]);
    }

    public function destroyLeaveType(LeaveType $leaveType): RedirectResponse
    {
        if ($leaveType->leaveRequests()->exists()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'This leave type is already in use and cannot be deleted.',
            ]);
        }

        $leaveType->delete();

        $this->audit('Leave type deleted: '.$leaveType->name, 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Leave type deleted.',
        ]);
    }

    // ─── Security ──────────────────────────────────────────────────────────────

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

        $this->audit('Security settings updated', 'settings');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Security settings saved successfully.',
        ]);
    }

    // ─── Shared ────────────────────────────────────────────────────────────────

    protected function storeUploaded(\Illuminate\Http\UploadedFile $file, string $dir, string $name): string
    {
        $filename = $name.'-'.time().'.'.$file->getClientOriginalExtension();
        $path = "uploads/{$dir}/{$filename}";

        $file->move(public_path("uploads/{$dir}"), $filename);

        return $path;
    }

    protected function audit(string $description, string $logName = 'settings'): void
    {
        app(AuditService::class)->log($description, $logName, event: 'updated');
    }
}
