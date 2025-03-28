<?php

namespace App\Livewire\Admin\Shared;

use Livewire\Component;

class Progress extends Component
{
    public array $stages = [];
    public int $currentStage = 0;

    public function render()
    {
        return view('livewire.admin.shared.progress');
    }
}
