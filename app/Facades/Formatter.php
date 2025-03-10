<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Formatter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'App\Helpers\FormatHelper';
    }
}
