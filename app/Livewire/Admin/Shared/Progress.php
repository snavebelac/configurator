<?php

namespace App\Livewire\Admin\Shared;

use Livewire\Component;
use Livewire\Attributes\Modelable;

class Progress extends Component
{
    public array $stages = [];
    #[Modelable]
    public int $currentStage = 1;

    public function render()
    {
        return view('livewire.admin.shared.progress');
    }
}
