<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\AuditService;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class, fn () => new SettingService);
        $this->app->singleton(AuditService::class, fn () => new AuditService);
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerBladeDirectives();
        $this->shareGlobalSettings();
        $this->shareNavbarData();
    }

    protected function shareNavbarData(): void
    {
        View::composer('partials.navbar', function ($view) {
            $user = auth()->user();

            $unread = 0;
            $recent = collect();

            if ($user) {
                try {
                    $unread = $user->unreadNotifications()->count();
                    $recent = $user->notifications()->latest()->limit(6)->get();
                } catch (\Throwable) {
                    // Notifications table may be unavailable during setup.
                }
            }

            $view->with('unreadNotifications', $unread);
            $view->with('recentNotifications', $recent);
        });
    }

    protected function configureDefaults(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Schema::defaultStringLength(191);

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('money', fn ($expression) => "<?php echo format_money($expression); ?>");
        Blade::directive('setting', fn ($expression) => "<?php echo e(setting($expression)); ?>");
    }

    protected function shareGlobalSettings(): void
    {
        try {
            $settings = Setting::allCached();

            view()->share('system_settings', $settings);

            if (isset($settings['general']['name'])) {
                config()->set('app.name', $settings['general']['name']);
            }

            if (isset($settings['security']['session_lifetime'])) {
                config()->set('session.lifetime', (int) $settings['security']['session_lifetime']);
            }
        } catch (\Throwable) {
            // Database is not ready yet (e.g. during migrations).
        }
    }
}
