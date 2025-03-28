<?php

namespace App\Livewire\Admin\Proposals;

use App\Models\Feature;
use App\Facades\Formatter;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Livewire\Admin\AdminComponent;

#[Title('Create a new proposal')]
class ProposalCreate extends AdminComponent
{
    use WithPagination;

    public string $featureSearch = '';
    public int $pageLength = 16;
    public array $selectedFeatureIds = [];

    public array $stages = [
        1 => 'Add Features',
        2 => 'Customise',
        3 => 'Preview',
        4 => 'Complete'
    ];

    public int $stage = 1;

    public function setStage(int $stage)
    {
        $this->stage = $stage;
    }

    public function selectFeature(int $featureId): void
    {
        $this->selectedFeatureIds[] = $featureId;
    }

    public function removeFeature(int $featureId): void
    {
        $this->selectedFeatureIds = array_diff($this->selectedFeatureIds, [$featureId]);
    }

    public function render()
    {
        $selectedFeatures = Feature::whereIn('id', $this->selectedFeatureIds)
            ->orderBy('name')
            ->get();
        $totalForSelectedFeatures = empty($selectedFeatures) ? 0 : Formatter::currency($selectedFeatures->sum(fn ($feature) => $feature->price * $feature->quantity));
        $features = Feature::when($this->featureSearch != '', fn ($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->pageLength, pageName: 'features-page');

        return view('livewire.admin.proposals.proposal-create', [
            'features' => $features,
            'selectedFeatures' => $selectedFeatures,
            'totalForSelectedFeatures' => $totalForSelectedFeatures
        ]);
    }
}
