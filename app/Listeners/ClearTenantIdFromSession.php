<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

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
