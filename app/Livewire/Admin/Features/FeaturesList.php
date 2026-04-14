<?php

namespace App\Livewire\Admin\Features;

use App\Livewire\Admin\AdminComponent;
use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class FeaturesList extends AdminComponent
{
    use WithPagination;

    public $search = '';

    private $pageLength = 20;

    #[On('refresh-features')]
    public function loadFeatures(): void
    {
        $this->resetPage();
    }

    public function delete(int $featureId): void
    {
        $feature = Feature::find($featureId);
        if ($feature) {
            $feature->delete();
            $this->dispatch('toast', ...$this->success(['text' => 'Feature deleted successfully']));
            $this->dispatch('refresh-features');
        } else {
            $reason = 'Unable to delete. Feature cannot be found';
            $this->dispatch('toast', ...$this->warning(['text' => $reason]));
        }
    }

    public function render(): View
    {
        $features = Feature::when($this->search != '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate($this->pageLength);

        return view('livewire.admin.features.features-list', [
            'features' => $features,
        ]);
    }
}
