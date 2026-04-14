<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminComposer
{
    public function compose(View $view)
    {
        $view->with('user', Auth::user());
    }
}
