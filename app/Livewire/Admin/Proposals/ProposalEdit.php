<?php

namespace App\Livewire\Admin\Proposals;

use App\Models\Proposal;
use Livewire\Attributes\Title;
use App\Livewire\Admin\AdminComponent;

#[Title('Edit a new proposal')]
class ProposalEdit extends AdminComponent
{

    public ?int $proposalId = null;
    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        if ($proposal) {
            $this->proposalId = $proposal->id;
            $this->proposal->load(['features']);
        }
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-edit');
    }
}
