<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ClearTenantIdFromSession
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        session()->forget('tenant_id');
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        //
    }
}
