<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Package;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LivewireUI\Modal\ModalComponent;

class AddPackageModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }

    public int $proposalId;

    public Proposal $proposal;

    public string $search = '';

    public function mount(int $proposalId): void
    {
        $this->proposalId = $proposalId;
        $this->proposal = Proposal::findOrFail($proposalId);
    }

    public function addPackage(int $packageId): void
    {
        $package = Package::with('features')->find($packageId);
        if (! $package) {
            return;
        }

        $existingSourceIds = $this->existingSourceFeatureIds();
        $addedCount = 0;

        $members = $package->features->sortBy('name')->values();

        foreach ($members as $feature) {
            if (in_array($feature->id, $existingSourceIds, true)) {
                continue;
            }

            $parentFinalId = null;
            if ($feature->parent_id) {
                $parentFinalId = $this->ensureParentSnapshotted($feature->parent_id);
                $existingSourceIds[] = $feature->parent_id;
            }

            $this->snapshotFeatureWithOverrides($feature, $parentFinalId);
            $existingSourceIds[] = $feature->id;
            $addedCount++;
        }

        $this->proposal->load('features');

        $this->dispatch('refreshFeatureProposalEdit');
        $this->dispatch('closeModal');

        if ($addedCount === 0) {
            $this->dispatch('toast', ...AdminComponent::warning([
                'text' => "All features from \"{$package->name}\" are already on this proposal.",
            ]));

            return;
        }

        $this->dispatch('toast', ...AdminComponent::success([
            'text' => "Added {$addedCount} ".Str::plural('feature', $addedCount)." from \"{$package->name}\"",
        ]));
    }

    private function snapshotFeatureWithOverrides(Feature $feature, ?int $parentFinalId): FinalFeature
    {
        $pivot = $feature->pivot;

        $quantity = $pivot->quantity ?? $feature->quantity;
        $optional = $pivot->optional ?? (bool) $feature->optional;
        $price = $pivot->price ?? $feature->price;

        $order = $feature->parent_id
            ? 0
            : ((int) $this->proposal->features()->whereNull('parent_id')->max('order')) + 1;

        $finalFeature = new FinalFeature([
            'name' => $feature->name,
            'description' => $feature->description,
            'price' => $price,
            'quantity' => $quantity,
            'optional' => $optional,
            'parent_id' => $parentFinalId,
            'source_feature_id' => $feature->id,
            'order' => $order,
        ]);
        $finalFeature->proposal()->associate($this->proposal);
        $finalFeature->save();

        return $finalFeature;
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
        $packages = Package::query()
            ->withCount('features')
            ->with('features')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.proposals.add-package-modal', [
            'packages' => $packages,
        ]);
    }
}
