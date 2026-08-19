<?php

namespace App\Facades;

use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Helpers\SettingsHelper forTenant(?int $tenantId)
 * @method static \App\Helpers\SettingsHelper forget()
 * @method static string getTaxName()
 * @method static float getTaxRate()
 * @method static \App\Enums\CurrencySymbol getCurrency()
 * @method static bool isTaxInclusive()
 * @method static string|null getCompanyName()
 * @method static string|null getLogo()
 *
 * @see SettingsHelper
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsHelper::class;
    }
}
