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

    /**
     * Mirrors the column defaults in the create_settings_table migration so a
     * bare `new Setting` is a usable set of defaults. SettingsHelper leans on
     * this when a tenant has no row yet, which keeps the fallback values in
     * one place rather than duplicated across the helper and the migration.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => CurrencySymbol::GBP->value,
        'tax_rate' => 20.0,
        'tax_name' => 'VAT',
        'tax_inclusive' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => CurrencySymbol::class,
            'tax_rate' => 'float',
            'tax_inclusive' => 'boolean',
            'default_share_expiry_days' => 'integer',
        ];
    }
}
