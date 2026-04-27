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

    protected $fillable = [
        'currency',
        'tax_rate',
        'tax_name',
        'tax_inclusive',
        'default_share_expiry_days',
        'logo',
        'company_name',
    ];

    protected $casts = [
        'currency' => CurrencySymbol::class,
        'tax_inclusive' => 'boolean',
        'default_share_expiry_days' => 'integer',
    ];
}
