<?php

namespace App\Observers;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\Package;

class PackageObserver
{
    public function created(Package $package): void
    {
        Activity::log(ActivityAction::PackageCreated, $package);
    }
}
