<?php

namespace App\Models;

use App\Enums\CurrencySymbol;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory, BelongsToTenant;

    protected $casts = [
        'currency' => CurrencySymbol::class,
    ];
}
