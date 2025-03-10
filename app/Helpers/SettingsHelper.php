<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Enums\CurrencySymbol;

class SettingsHelper
{
    private string $taxName;
    private float $taxRate;
    private CurrencySymbol $currency;

    public function __construct() {
        // gets the setting values for the current tenant (utilising the global scope)
        $setting = Setting::first();
        $this->taxName = $setting->tax_name;
        $this->taxRate = $setting->tax_rate;
        $this->currency = $setting->currency;
    }

    public function getTaxName(): string
    {
        return $this->taxName;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getCurrency(): CurrencySymbol
    {
        return $this->currency;
    }

}
