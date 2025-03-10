<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FeatureProposal extends Pivot
{
    use BelongsToTenant;
}
