<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\Proposal;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ProposalsList extends AdminComponent
{

    use WithPagination;

    public $search = '';
    private $pageLength = 6;

    #[On('refresh-proposals')]
    public function loadProposals(): void
    {
        $this->resetPage();
    }

    public function delete(int $proposalId): void
    {
        $proposal = Proposal::find($proposalId);
        if ($proposal) {
            $proposal->delete();
            $this->dispatch('toast', ...$this->success(['text' => 'Proposal deleted successfully']));
            $this->dispatch('refresh-clients');
        } else {
            $reason = 'Unable to delete. Proposal cannot be found';
            $this->dispatch('toast', ...$this->warning(['text' => $reason]));
        }
    }


    public function render()
    {
        $proposals = Proposal::with(['client'])
            ->when($this->search != '', fn ($query) => $query
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('client.name', 'like', '%' . $this->search . '%')
                ->orWhere('client.contact', 'like', '%' . $this->search . '%')
                ->orWhere('client.contact_email', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name')
            ->paginate($this->pageLength);
        return view('livewire.admin.proposals.proposals-list', [
            'proposals' => $proposals,
        ]);
    }
}
