<?php

namespace App\Livewire\Admin\Proposals;

use Livewire\Component;
use App\Models\FinalFeature;

class ProposalFeatureForm extends Component
{
    public $finalFeature, $quantity, $price, $name;

    public function mount($finalFeatureId)
    {
        $this->finalFeature = FinalFeature::find($finalFeatureId);
        $this->quantity = $this->finalFeature->quantity;
        $this->price = $this->finalFeature->price;
        $this->name = $this->finalFeature->name;
    }

    public function updated($name, $value)
    {
        $this->finalFeature->$name = $value;
        $this->finalFeature->save();
        $this->dispatch('refreshFeatureProposalEdit');
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-feature-form');
    }
}
