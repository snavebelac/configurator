<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Edit proposal')]
class ProposalEdit extends AdminComponent
{
    public ?int $proposalId = null;

    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $this->proposalId = $proposal->id;
        $this->proposal->load(['features', 'client', 'user']);
    }

    public function render(): View
    {
        return view('livewire.admin.proposals.proposal-edit');
    }
}
