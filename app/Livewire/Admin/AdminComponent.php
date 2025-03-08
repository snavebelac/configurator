<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class AdminComponent extends Component
{
    public static function success($params): array
    {
        $defaults = [
            'text' =>  'Action completed',
            'duration' => 4000,
            'close' =>  false,
            'gravity' => 'top',
            'position' => 'center',
        ];

        return array_merge($defaults, $params);
    }

    public static function warning($params): array
    {
        $defaults = [
            'text' =>  'Warning',
            'duration' => 5000,
            'close' =>  false,
            'gravity' => 'top',
            'position' => 'center',
            'className' => 'warning',
        ];

        return array_merge($defaults, $params);
    }

}
