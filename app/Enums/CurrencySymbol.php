<?php

namespace App\Enums;

enum CurrencySymbol: string
{
    case GBP = 'pound';
    case USD = 'dollar';
    case EUR = 'euro';

    public function toHtml(): string
    {
        return match($this) {
            CurrencySymbol::GBP, CurrencySymbol::USD, CurrencySymbol::EUR => '&' . $this->value . ';',
        };
    }

    public function toSymbol(): string
    {
        return match($this) {
            CurrencySymbol::GBP => '£',
            CurrencySymbol::USD => '$',
            CurrencySymbol::EUR => '€',
        };
    }
}
