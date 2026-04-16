<?php

namespace App\Observers;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\Client;

class ClientObserver
{
    public function created(Client $client): void
    {
        Activity::log(ActivityAction::ClientCreated, $client);
    }
}
