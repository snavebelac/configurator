<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Package;
use App\Models\Proposal;
use App\Observers\ClientObserver;
use App\Observers\PackageObserver;
use App\Observers\ProposalObserver;
use App\View\Composers\AdminComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('authentication', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        View::composer('components.layouts.admin', AdminComposer::class);

        Proposal::observe(ProposalObserver::class);
        Client::observe(ClientObserver::class);
        Package::observe(PackageObserver::class);
    }
}
