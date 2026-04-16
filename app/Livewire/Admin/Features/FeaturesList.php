<?php

namespace App\Livewire\Admin\Features;

use App\Livewire\Admin\AdminComponent;
use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class FeaturesList extends AdminComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    private int $pageLength = 20;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

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
        $searching = $this->search !== '';

        if ($searching) {
            $features = Feature::with('parent')
                ->where('name', 'like', '%'.$this->search.'%')
                ->orderBy('name')
                ->paginate($this->pageLength);
        } else {
            $features = Feature::roots()
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->paginate($this->pageLength);
        }

        return view('livewire.admin.features.features-list', [
            'features' => $features,
            'searching' => $searching,
            'total' => Feature::count(),
            'optionalCount' => Feature::where('optional', true)->count(),
            'parentCount' => Feature::roots()->whereHas('children')->count(),
        ]);
    }
}
