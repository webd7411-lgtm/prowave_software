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

        Blade::directive('canany', function ($expression) {
            return "<?php if(auth()->user()?->canAny([{$expression}])): ?>";
        });

        Blade::directive('endcanany', function () {
            return '<?php endif; ?>';
        });
    }

    protected function shareGlobalSettings(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::allCached();

                view()->share('system_settings', $settings);

                if (isset($settings['general']['name'])) {
                    config()->set('app.name', $settings['general']['name']);
                }

                if (isset($settings['security']['session_lifetime'])) {
                    config()->set('session.lifetime', (int) $settings['security']['session_lifetime']);
                }
            }
        } catch (\Throwable) {
            // Database is not ready yet (e.g. during migrations).
        }
    }
}
