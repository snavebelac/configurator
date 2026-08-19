<?php

namespace App\Enums;

enum PricingType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed price',
            self::Percentage => 'Percentage of total',
        };
    }

    public function isPercentage(): bool
    {
        return $this === self::Percentage;
    }
}
