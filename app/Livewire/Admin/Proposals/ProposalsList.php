<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Livewire\Admin\AdminComponent;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ProposalsList extends AdminComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $filter = 'all';

    private int $pageLength = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

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

    public function render(): View
    {
        $counts = Proposal::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = [
            'all' => (int) $counts->sum(),
            'draft' => (int) ($counts[Status::DRAFT->value] ?? 0),
            'delivered' => (int) ($counts[Status::DELIVERED->value] ?? 0),
            'accepted' => (int) ($counts[Status::ACCEPTED->value] ?? 0),
            'rejected' => (int) ($counts[Status::REJECTED->value] ?? 0),
            'archived' => (int) ($counts[Status::ARCHIVED->value] ?? 0),
        ];

        $proposals = Proposal::with(['client', 'user', 'features'])
            ->when($this->filter !== 'all', fn ($query) => $query->where('status', $this->filter))
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('contact', 'like', $term)
                            ->orWhere('contact_email', 'like', $term)
                        );
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($this->pageLength);

        return view('livewire.admin.proposals.proposals-list', [
            'proposals' => $proposals,
            'statusCounts' => $statusCounts,
        ]);
    }
}
