<?php

namespace App\Livewire\Admin\Proposals;

use App\Models\FinalFeature;
use Livewire\Component;

class ProposalFeatureForm extends Component
{
    public FinalFeature $finalFeature;

    public string $name = '';

    public $price = 0;

    public int $quantity = 1;

    public bool $optional = false;

    public function mount(int $finalFeatureId): void
    {
        $this->finalFeature = FinalFeature::findOrFail($finalFeatureId);
        $this->name = $this->finalFeature->name;
        $this->price = $this->finalFeature->price;
        $this->quantity = $this->finalFeature->quantity;
        $this->optional = $this->finalFeature->optional;
    }

    public function updated($name, $value): void
    {
        if (! in_array($name, ['name', 'price', 'quantity', 'optional'], true)) {
            return;
        }

        $this->finalFeature->{$name} = $value;
        $this->finalFeature->save();
        $this->dispatch('refreshFeatureProposalEdit');
    }

    public function removeFinalFeature(): void
    {
        $this->finalFeature->delete();
        $this->dispatch('refreshFeatureProposalEdit');
        $this->dispatch('finalFeatureRemoved', id: $this->finalFeature->id);
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-feature-form');
    }
}
