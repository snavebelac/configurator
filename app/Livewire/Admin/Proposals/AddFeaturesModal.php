<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use LivewireUI\Modal\ModalComponent;

class AddFeaturesModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }

    public int $proposalId;

    public Proposal $proposal;

    public function mount(int $proposalId): void
    {
        $this->proposalId = $proposalId;
        $this->proposal = Proposal::findOrFail($proposalId);
    }

    #[On('feature-picked')]
    public function addFeature(int $featureId): void
    {
        $feature = Feature::find($featureId);
        if (! $feature) {
            return;
        }

        $alreadyPresent = $this->existingSourceFeatureIds();
        if (in_array($feature->id, $alreadyPresent, true)) {
            return;
        }

        $parentFinalId = null;
        if ($feature->parent_id) {
            $parentFinalId = $this->ensureParentSnapshotted($feature->parent_id);
        }

        $order = $feature->parent_id
            ? 0
            : ((int) $this->proposal->features()->whereNull('parent_id')->max('order')) + 1;

        $finalFeature = new FinalFeature([
            'name' => $feature->name,
            'description' => $feature->description,
            'price' => $feature->price,
            'quantity' => $feature->quantity,
            'optional' => $feature->optional,
            'parent_id' => $parentFinalId,
            'source_feature_id' => $feature->id,
            'order' => $order,
        ]);
        $finalFeature->proposal()->associate($this->proposal);
        $finalFeature->save();

        $this->proposal->load('features');

        $this->dispatch('refreshFeatureProposalEdit');
        $this->dispatch('toast', ...AdminComponent::success([
            'text' => "Added \"{$feature->name}\"",
        ]));
    }

    private function ensureParentSnapshotted(int $parentFeatureId): int
    {
        $existingParent = $this->proposal->features()
            ->whereNull('parent_id')
            ->where('source_feature_id', $parentFeatureId)
            ->first();

        if ($existingParent) {
            return $existingParent->id;
        }

        $parentFeature = Feature::findOrFail($parentFeatureId);
        $order = ((int) $this->proposal->features()->whereNull('parent_id')->max('order')) + 1;

        $parentFinal = new FinalFeature([
            'name' => $parentFeature->name,
            'description' => $parentFeature->description,
            'price' => $parentFeature->price,
            'quantity' => $parentFeature->quantity,
            'optional' => $parentFeature->optional,
            'parent_id' => null,
            'source_feature_id' => $parentFeature->id,
            'order' => $order,
        ]);
        $parentFinal->proposal()->associate($this->proposal);
        $parentFinal->save();

        $this->dispatch('toast', ...AdminComponent::success([
            'text' => "Added \"{$parentFeature->name}\" — required parent",
        ]));

        return $parentFinal->id;
    }

    /**
     * @return array<int>
     */
    private function existingSourceFeatureIds(): array
    {
        return $this->proposal->features()
            ->whereNotNull('source_feature_id')
            ->pluck('source_feature_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin.proposals.add-features-modal', [
            'disabledIds' => $this->existingSourceFeatureIds(),
        ]);
    }
}
