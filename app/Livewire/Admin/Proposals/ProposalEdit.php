<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\FinalFeature;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

#[Title('Edit proposal')]
class ProposalEdit extends AdminComponent
{
    public ?int $proposalId = null;

    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $this->proposalId = $proposal->id;
    }

    #[On('refreshFeatureProposalEdit')]
    public function refresh(): void
    {
        $this->proposal->load(['features', 'client', 'user']);
    }

    public function reorderParents(int $finalFeatureId, int $position): void
    {
        $parentIds = $this->proposal->features()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('name')
            ->pluck('id')
            ->all();

        $currentIndex = array_search($finalFeatureId, $parentIds, true);
        if ($currentIndex === false) {
            return;
        }

        array_splice($parentIds, $currentIndex, 1);
        array_splice($parentIds, $position, 0, [$finalFeatureId]);

        foreach ($parentIds as $index => $id) {
            FinalFeature::where('id', $id)->update(['order' => $index + 1]);
        }

        $this->proposal->load('features');
    }

    public function render(): View
    {
        $this->proposal->load(['features', 'client', 'user']);

        return view('livewire.admin.proposals.proposal-edit', [
            'featureGroups' => $this->groupFeatures($this->proposal->features),
        ]);
    }

    /**
     * @return Collection<int, array{root: FinalFeature, children: Collection<int, FinalFeature>}>
     */
    private function groupFeatures(Collection $features): Collection
    {
        $roots = $features->whereNull('parent_id')
            ->sortBy([['order', 'asc'], ['name', 'asc']])
            ->values();

        return $roots->map(fn (FinalFeature $root) => [
            'root' => $root,
            'children' => $features->where('parent_id', $root->id)
                ->sortBy('name')
                ->values(),
        ]);
    }
}
