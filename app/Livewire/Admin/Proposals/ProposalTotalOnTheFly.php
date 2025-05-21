<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use Livewire\Component;
use App\Models\Feature;
use App\Models\Proposal;
use App\Facades\Formatter;
use Livewire\Attributes\On;
use App\Models\FinalFeature;

class ProposalTotalOnTheFly extends Component
{

    public $proposalId = null;
    public $totalForHumans = '';
    public Status $status;

    public function mount($proposalId): void
    {
        $this->proposalId = $proposalId;
        $this->updateProposalTotal();
    }

    #[On('refreshFeatureProposalEdit')]
    public function updateProposalTotal(): void
    {
        $features = FinalFeature::where('proposal_id', $this->proposalId)->get();
        $total = $features->sum(function($feature) {
            return $feature->quantity * $feature->price;
        });
        $this->totalForHumans = Formatter::currency($total);
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-total-on-the-fly');
    }
}
