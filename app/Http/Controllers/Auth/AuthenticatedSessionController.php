<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use App\Services\LoginLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();
        $request->ensureAccountIsNotLocked();

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->hitRateLimiter();

            $user = $request->userByEmail();

            if ($user) {
                $user->incrementLoginAttempts();

                if ($user->isLocked()) {
                    app(LoginLogService::class)->recordLocked($user->email);

                    app(AuditService::class)->logAuth('Account locked after failed login attempts', [
                        'email' => $user->email,
                        'ip' => $request->ip(),
                    ]);

                    return back()
                        ->withErrors(['email' => 'Your account has been temporarily locked due to too many failed attempts. Please try again later.'])
                        ->onlyInput('email');
                }
            }

            app(LoginLogService::class)->recordFailed($request->string('email'));

            app(AuditService::class)->logAuth('Failed login attempt', [
                'email' => $request->string('email'),
                'ip' => $request->ip(),
            ]);

            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->clearRateLimiter();

        $user = Auth::user();

        $statusValue = $user->status instanceof UserStatus
            ? $user->status->value
            : (string) $user->status;

        if (! $user->isActive() || ! in_array($statusValue, [UserStatus::Active->value], true)) {
            Auth::logout();

            app(LoginLogService::class)->recordFailed($user->email);

            app(AuditService::class)->logAuth('Blocked login for inactive user', [
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return back()
                ->withErrors(['email' => 'Your account is not active. Please contact the administrator.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user->resetLoginAttempts();
        $user->updateLastLogin();

        app(LoginLogService::class)->recordSuccess($user->id, $user->email);

        app(AuditService::class)->logAuth('User logged in', [
            'ip' => $request->ip(),
            'browser' => app(LoginLogService::class)->parseUserAgent($request->userAgent() ?? '')['browser'],
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        app(LoginLogService::class)->recordLogout($user->id);

        app(AuditService::class)->logAuth('User logged out', ['ip' => $request->ip()]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
