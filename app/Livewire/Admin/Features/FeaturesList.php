<?php

namespace App\Livewire\Admin\Features;

use App\Models\Feature;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use App\Livewire\Admin\AdminComponent;

class FeaturesList extends AdminComponent
{
    use WithPagination;

    public $search = '';
    private $pageLength = 5;

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
        $features = Feature::when($this->search != '', fn ($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->pageLength);

        return view('livewire.admin.features.features-list', [
            'features' => $features
        ]);
    }
}
