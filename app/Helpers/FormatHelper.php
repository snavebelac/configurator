<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Facades\Settings;

class FormatHelper
{
    public function currency($price): string
    {
        return Settings::getCurrency()->toHtml() . $price;
    }

    public function date(string|Carbon $date, $format = 'd M Y'): string
    {
        if (gettype($date) === 'string') {
            $date = Carbon::parse($date);
        }
        return $date->format($format);
    }
}
