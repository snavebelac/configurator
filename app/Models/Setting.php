<?php

namespace App\Models;

use App\Enums\CurrencySymbol;
use App\Traits\BelongsToTenant;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use BelongsToTenant, HasFactory;

    protected $casts = [
        'currency' => CurrencySymbol::class,
    ];
}
