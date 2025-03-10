<?php

namespace App\Livewire\Admin\Proposals;

use Livewire\Component;
use App\Models\Proposal;
use App\Facades\Formatter;

class Preview extends Component
{
    public Proposal $proposal;
    public $features;
    public $totalForHumans = 0.00;

    public function mount(Proposal $proposal)
    {
        $proposal->load(['features', 'user']);
        $this->proposal = $proposal;
        $this->features = $proposal->features;
        $this->totalForHumans = $proposal->total_for_humans;
    }
    public function render()
    {
        return view('livewire.admin.proposals.preview');
    }
}
