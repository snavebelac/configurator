<?php

namespace App\Enums;

enum BillingPeriod: string
{
    case Monthly = 'monthly';
    case Annually = 'annually';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Annually => 'Annually',
        };
    }

    /**
     * How the period reads next to a price — "£50 / month".
     */
    public function suffix(): string
    {
        return match ($this) {
            self::Monthly => '/ month',
            self::Annually => '/ year',
        };
    }

    /**
     * Heading for the column this period's costs total into.
     */
    public function totalLabel(): string
    {
        return match ($this) {
            self::Monthly => 'Per month',
            self::Annually => 'Per year',
        };
    }
}
