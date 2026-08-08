<?php

namespace App\Services;

use App\Models\LoginLog;
use Illuminate\Support\Str;

class LoginLogService
{
    public function recordSuccess(int $userId, ?string $email = null): LoginLog
    {
        return $this->record($userId, 'success', $email, request()->boolean('remember', false));
    }

    public function recordFailed(?string $email = null): LoginLog
    {
        return $this->record(null, 'failed', $email);
    }

    public function recordLocked(?string $email = null): LoginLog
    {
        return $this->record(null, 'locked', $email);
    }

    public function recordLogout(int $userId): void
    {
        LoginLog::query()
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->whereNull('logout_at')
            ->latest()
            ->first()
            ?->update(['logout_at' => now()]);
    }

    public function parseUserAgent(string $userAgent): array
    {
        $userAgent = $userAgent ?: '';

        return [
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'device' => $this->detectDevice($userAgent),
        ];
    }

    private function detectBrowser(string $ua): string
    {
        if (Str::contains($ua, 'Edg/')) {
            return 'Edge';
        }

        if (Str::contains($ua, 'OPR/') || Str::contains($ua, 'Opera')) {
            return 'Opera';
        }

        if (Str::contains($ua, 'Chrome/') && ! Str::contains($ua, 'Chromium')) {
            return 'Chrome';
        }

        if (Str::contains($ua, 'Firefox/')) {
            return 'Firefox';
        }

        if (Str::contains($ua, 'Safari/')) {
            return 'Safari';
        }

        if (Str::contains($ua, 'MSIE') || Str::contains($ua, 'Trident/')) {
            return 'Internet Explorer';
        }

        return 'Unknown';
    }

    private function detectPlatform(string $ua): string
    {
        if (Str::contains($ua, 'Windows')) {
            return 'Windows';
        }

        if (Str::contains($ua, 'Mac OS X') || Str::contains($ua, 'Macintosh')) {
            return 'macOS';
        }

        if (Str::contains($ua, 'Linux')) {
            return 'Linux';
        }

        if (Str::contains($ua, 'Android')) {
            return 'Android';
        }

        if (Str::contains($ua, 'iPhone') || Str::contains($ua, 'iPad') || Str::contains($ua, 'iPod')) {
            return 'iOS';
        }

        return 'Unknown';
    }

    private function detectDevice(string $ua): string
    {
        if (Str::contains($ua, 'iPad')) {
            return 'Tablet';
        }

        if (Str::contains($ua, 'Tablet') || Str::contains($ua, 'Kindle') || Str::contains($ua, 'Silk')) {
            return 'Tablet';
        }

        if (Str::contains($ua, 'Mobile') || Str::contains($ua, 'Android') || Str::contains($ua, 'iPhone')) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    private function record(?int $userId, string $status, ?string $email = null, bool $remember = false): LoginLog
    {
        $parsed = $this->parseUserAgent(request()->userAgent() ?? '');

        return LoginLog::create(array_merge($parsed, [
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent() ?? '', 500),
            'status' => $status,
            'remember' => $remember,
            'login_at' => now(),
        ]));
    }
}
