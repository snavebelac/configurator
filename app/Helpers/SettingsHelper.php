<?php

namespace App\Helpers;

use App\Enums\CurrencySymbol;
use App\Models\Setting;

class SettingsHelper
{
    private string $taxName;

    private float $taxRate;

    private bool $taxInclusive;

    private CurrencySymbol $currency;

    private ?int $defaultShareExpiryDays;

    public function __construct(?int $tenantId = null)
    {
        $setting = $tenantId === null
            ? Setting::first()
            : Setting::forTenant($tenantId);

        $this->taxName = $setting->tax_name;
        $this->taxRate = $setting->tax_rate;
        $this->taxInclusive = (bool) $setting->tax_inclusive;
        $this->currency = $setting->currency;
        $this->defaultShareExpiryDays = $setting->default_share_expiry_days;
    }

    public function getTaxName(): string
    {
        return $this->taxName;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getTaxInclusive(): bool
    {
        return $this->taxInclusive;
    }

    public function getCurrency(): CurrencySymbol
    {
        return $this->currency;
    }

    public function getDefaultShareExpiryDays(): ?int
    {
        return $this->defaultShareExpiryDays;
    }
}
