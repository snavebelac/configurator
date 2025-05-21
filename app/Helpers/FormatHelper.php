<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Facades\Settings;

class FormatHelper
{
    public function currency($price): string
    {
        return Settings::getCurrency()->toHtml() . number_format($price, 2, '.', ',');
    }

    public function date(string|Carbon $date, $format = 'd M Y'): string
    {
        if (gettype($date) === 'string') {
            $date = Carbon::parse($date);
        }
        return $date->format($format);
    }

    public function convertIntegerPrice(?int $value): ?float
    {
        if (!$value) return null;
        return round($value / 100, 2);
    }
}
