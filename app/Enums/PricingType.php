<?php

namespace App\Enums;

enum PricingType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case Recurring = 'recurring';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed price',
            self::Percentage => 'Percentage of total',
            self::Recurring => 'Recurring cost',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Fixed => 'A set amount, optionally times a quantity',
            self::Percentage => 'A share of the one-off work on the proposal',
            self::Recurring => 'An ongoing charge, billed monthly or annually',
        };
    }

    public function isPercentage(): bool
    {
        return $this === self::Percentage;
    }

    public function isRecurring(): bool
    {
        return $this === self::Recurring;
    }
}
