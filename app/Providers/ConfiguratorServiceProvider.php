<?php

namespace App\Providers;

use App\Helpers\FormatHelper;
use App\Helpers\HtmlSanitiser;
use App\Helpers\SettingsHelper;
use Illuminate\Support\ServiceProvider;

class ConfiguratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FormatHelper::class, fn () => new FormatHelper);
        $this->app->singleton(SettingsHelper::class, fn () => new SettingsHelper);

        // Building the allowlist config isn't free; the sanitiser is stateless
        // so one instance per request is plenty.
        $this->app->singleton(HtmlSanitiser::class, fn () => new HtmlSanitiser);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
