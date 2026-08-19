<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Livewire\Admin\AdminComponent;
use App\Models\FinalFeature;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Edit proposal')]
class ProposalEdit extends AdminComponent
{
    public ?int $proposalId = null;

    public Proposal $proposal;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:4000')]
    public ?string $description = null;

    #[Validate('nullable|string|max:4000')]
    public ?string $additional = null;

    public function mount(Proposal $proposal): void
    {
        $this->proposalId = $proposal->id;
        $this->name = $proposal->name;
        $this->description = $proposal->description;
        $this->additional = $proposal->additional;
    }

    /**
     * The proposal's own copy — the title, the opening paragraph and the
     * closing notes. All three render on the client-facing view; until now
     * none of them could be edited after creation.
     */
    public function saveDetails(): void
    {
        $this->validate();

        $this->proposal->name = $this->name;
        $this->proposal->description = $this->description ?: null;
        $this->proposal->additional = $this->additional ?: null;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success(['text' => 'Proposal details saved']));
    }

    #[On('refreshFeatureProposalEdit')]
    public function refresh(): void
    {
        $this->proposal->load(['features', 'client', 'user']);
    }

    /**
     * Send the proposal: move it out of draft so the client's public link can
     * be answered. Until this happens accept/reject is not offered.
     */
    public function markAsDelivered(): void
    {
        if (! $this->proposal->canBeDelivered()) {
            $this->dispatch('toast', ...$this->warning([
                'text' => 'Add at least one feature before marking this proposal as delivered.',
            ]));

            return;
        }

        $this->proposal->status = Status::DELIVERED;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => 'Marked as delivered. Your client can now respond to the share link.',
        ]));
    }

    /**
     * Clear a client's response so they can answer again — the escape hatch for
     * an accidental accept or decline.
     */
    public function reopen(): void
    {
        $this->proposal->response()->delete();

        $this->proposal->status = Status::DELIVERED;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => 'Reopened. The client can respond again.',
        ]));
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
        $this->proposal->load(['features', 'client', 'user', 'response']);

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
