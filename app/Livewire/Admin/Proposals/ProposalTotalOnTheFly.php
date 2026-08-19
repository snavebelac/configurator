<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Facades\Formatter;
use App\Helpers\ProposalPricing;
use App\Models\FinalFeature;
use Livewire\Attributes\On;
use Livewire\Component;

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

        // Everything on, percentage lines included — the admin is looking at
        // the proposal's full value, not a client's selection.
        $pricing = app(ProposalPricing::class)->calculate(
            $features,
            $features->where('optional', true)->pluck('id')->map(fn ($id) => (int) $id)->all(),
        );

        $this->totalForHumans = Formatter::currency($pricing['subtotal'] / 100);
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-total-on-the-fly');
    }
}
