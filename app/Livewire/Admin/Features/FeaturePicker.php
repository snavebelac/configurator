<?php

namespace App\Livewire\Admin\Features;

use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

class FeaturePicker extends Component
{
    use WithPagination;

    /** @var array<int> */
    #[Reactive]
    public array $disabledIds = [];

    public string $search = '';

    public int $pageLength = 12;

    public string $pageName = 'features-page';

    public function updatedSearch(): void
    {
        $this->resetPage(pageName: $this->pageName);
    }

    public function pick(int $featureId): void
    {
        if (in_array($featureId, $this->disabledIds, true)) {
            return;
        }

        $this->dispatch('feature-picked', featureId: $featureId);
    }

    #[On('refresh-features')]
    public function onLibraryChanged(): void
    {
        // Triggers a re-render so new or deleted features show up.
    }

    public function render(): View
    {
        $searching = $this->search !== '';

        $features = $searching
            ? Feature::with('parent')
                ->where('name', 'like', '%'.$this->search.'%')
                ->orderBy('name')
                ->paginate($this->pageLength, pageName: $this->pageName)
            : Feature::roots()
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->paginate($this->pageLength, pageName: $this->pageName);

        return view('livewire.admin.features.feature-picker', [
            'features' => $features,
            'searching' => $searching,
        ]);
    }
}
