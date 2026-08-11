<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        $user->load(['roles.permissions', 'department', 'reportingManager']);

        return view('profile.show', [
            'user' => $user,
            'loginLogs' => $user->loginLogs()->latest()->paginate(10),
            'activities' => $user->activities()->with('causer')->latest()->paginate(10),
        ]);
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        app(AuditService::class)->log('Profile updated', 'profile', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if (! Hash::check($request->string('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->string('password'))]);

        app(AuditService::class)->log('Password changed', 'security', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();

        if ($user->avatar && file_exists(public_path($user->avatar))) {
            unlink(public_path($user->avatar));
        }

        $file = $request->file('avatar');
        $name = 'avatars/'.$user->id.'-'.time().'.'.$file->getClientOriginalExtension();

        $file->move(public_path('avatars'), basename($name));

        $user->update(['avatar' => $name]);

        app(AuditService::class)->log('Avatar updated', 'profile', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Avatar updated successfully.',
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'language' => ['required', 'string', 'in:en'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'date_format' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();
        $user->update($request->only(['theme', 'language', 'timezone', 'date_format']));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Preferences updated successfully.',
        ]);
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $categories = ['lead', 'followup', 'client', 'project', 'task', 'leave', 'update', 'invoice', 'payment', 'expense', 'meeting', 'document', 'system'];

        $prefs = [];
        foreach ($categories as $category) {
            $prefs[$category] = (bool) $request->boolean("notify_{$category}");
        }

        auth()->user()->update(['notification_preferences' => $prefs]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Notification preferences saved.',
        ]);
    }

    public function sessions(): View
    {
        $sessions = collect();

        if (config('session.driver') === 'database' && \Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            $sessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', auth()->id())
                ->get()
                ->map(function ($session) {
                    $payload = @unserialize(base64_decode($session->payload));

                    return [
                        'id' => $session->id,
                        'ip' => $session->ip_address,
                        'user_agent' => $session->user_agent,
                        'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                        'is_current' => $session->id === session()->getId(),
                        'browser' => $this->parseUserAgent($session->user_agent),
                    ];
                });
        }

        return view('profile.sessions', ['sessions' => $sessions]);
    }

    public function revokeSession(string $sessionId): RedirectResponse
    {
        if (config('session.driver') !== 'database' || ! \Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Session management is not enabled for this installation.']);
        }

        if ($sessionId === session()->getId()) {
            return back()->with('toast', ['type' => 'error', 'message' => 'You cannot revoke your current session here.']);
        }

        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->delete();

        app(AuditService::class)->log('Revoked a remote session', 'security', auth()->user());

        return back()->with('toast', ['type' => 'success', 'message' => 'Session revoked.']);
    }

    public function revokeOthers(): RedirectResponse
    {
        if (config('session.driver') !== 'database' || ! \Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Session management is not enabled for this installation.']);
        }

        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', session()->getId())
            ->delete();

        app(AuditService::class)->log('Revoked all other sessions', 'security', auth()->user());

        return back()->with('toast', ['type' => 'success', 'message' => 'All other sessions were signed out.']);
    }

    protected function parseUserAgent(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
            return 'macOS';
        }
        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown device';
    }
}
