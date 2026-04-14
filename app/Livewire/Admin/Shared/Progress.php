<?php

namespace App\Livewire\Admin\Shared;

use Livewire\Attributes\Modelable;
use Livewire\Component;

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
