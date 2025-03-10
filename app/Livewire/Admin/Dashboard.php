<?php

namespace App\Livewire\Admin;

use App\Models\Proposal;
use Illuminate\Contracts\View\View;

class Dashboard extends AdminComponent
{

    public function render(): View
    {
        $drafts= Proposal::with('user')->orderBy('created_at', 'desc')->draft()->get();
        $delivered= Proposal::with('user')->orderBy('created_at', 'desc')->delivered()->get();
        $accepted= Proposal::with('user')->orderBy('created_at', 'desc')->accepted()->get();
        $rejected= Proposal::with('user')->orderBy('created_at', 'desc')->rejected()->get();
        $archived= Proposal::with('user')->orderBy('created_at', 'desc')->archived()->get();
        return view('livewire.admin.dashboard', [
            'drafts' => $drafts,
            'delivered' => $delivered,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'archived' => $archived,
        ]);
    }
}
