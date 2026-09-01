<?php

use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

if (! function_exists('setting')) {
    function setting(?string $key = null, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('app_settings')) {
    function app_settings(): array
    {
        return app(SettingService::class)->all();
    }
}

if (! function_exists('active_route')) {
    function active_route(string $pattern, string $class = 'active'): string
    {
        if (Route::is($pattern)) {
            return $class;
        }

        foreach (explode('|', $pattern) as $segment) {
            if (Route::is($segment)) {
                return $class;
            }
        }

        return '';
    }
}

if (! function_exists('route_is')) {
    function route_is(string|array $patterns): bool
    {
        $patterns = is_array($patterns) ? $patterns : explode('|', $patterns);

        return collect($patterns)->contains(fn (string $pattern) => request()->routeIs($pattern));
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));

        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $initials ?: '?';
    }
}

if (! function_exists('format_money')) {
    function format_money(float|int|string|null $amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals);
    }
}

if (! function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M, Y'): string
    {
        if (! $date) {
            return '—';
        }

        try {
            return Carbon::parse($date)->format($format);
        } catch (Throwable) {
            return '—';
        }
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime(?string $date): string
    {
        return format_date($date, 'd M, Y h:i A');
    }
}

if (! function_exists('diff_for_humans')) {
    function diff_for_humans(?string $date): string
    {
        if (! $date) {
            return '—';
        }

        try {
            return Carbon::parse($date)->diffForHumans();
        } catch (Throwable) {
            return '—';
        }
    }
}

if (! function_exists('is_active_sidebar')) {
    function is_active_sidebar(array $routes, string $class = 'show'): string
    {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $class;
            }
        }

        return '';
    }
}

if (! function_exists('app_name')) {
    function app_name(): string
    {
        return config('app.name', 'Prowave Technologies');
    }
}

if (! function_exists('app_version')) {
    function app_version(): string
    {
        return config('app.version', '1.0.0');
    }
}

if (! function_exists('app_logo')) {
    function app_logo(): string
    {
        return asset(setting('general.logo', 'images/logo.png'));
    }
}

if (! function_exists('user_avatar')) {
    function user_avatar(?Authenticatable $user = null): string
    {
        $user = $user ?? auth()->user();

        if ($user && method_exists($user, 'avatar_url') && $user->avatar_url) {
            return $user->avatar_url;
        }

        return asset('images/avatar-default.svg');
    }
}

if (! function_exists('human_file_size')) {
    function human_file_size(int|float|null $bytes, int $precision = 1): string
    {
        if ($bytes === null || $bytes < 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = 0;

        while ($bytes >= 1024 && $factor < count($units) - 1) {
            $bytes /= 1024;
            $factor++;
        }

        return round($bytes, $precision).' '.$units[$factor];
    }
}

if (! function_exists('json_response')) {
    function json_response(bool $success, string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

if (! function_exists('public_path_from_asset')) {
    function public_path_from_asset(string $path): string
    {
        $path = ltrim(str_replace(config('app.url'), '', $path), '/');

        return public_path($path);
    }
}
