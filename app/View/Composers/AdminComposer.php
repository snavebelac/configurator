<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminComposer
{
    public function compose(View $view)
    {
        $view->with('user', Auth::user());
    }
}
