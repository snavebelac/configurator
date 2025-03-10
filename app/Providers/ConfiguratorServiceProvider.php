<?php

namespace App\Providers;

use App\Helpers\FormatHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class ConfiguratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FormatHelper::class, fn() => new FormatHelper());
        $this->app->singleton(SettingsHelper::class, function() {
            $settingsHelper = new SettingsHelper();
            return $settingsHelper;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
