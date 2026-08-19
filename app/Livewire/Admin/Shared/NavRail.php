<?php

namespace App\Livewire\Admin\Shared;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The admin nav rail.
 *
 * Expands to show a text label beside each icon, or collapses to icons only
 * when screen space matters more. The choice is stored per user, so it
 * survives sessions and machines. New users start expanded — icons alone are
 * a guessing game until you already know the app.
 */
class NavRail extends Component
{
    public bool $collapsed = false;

    public function mount(): void
    {
        $this->collapsed = (bool) Auth::user()?->nav_collapsed;
    }

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;

        $user = Auth::user();

        if ($user !== null) {
            $user->nav_collapsed = $this->collapsed;
            $user->save();
        }
    }

    public function render(): View
    {
        return view('livewire.admin.shared.nav-rail', [
            'user' => Auth::user(),
        ]);
    }
}
