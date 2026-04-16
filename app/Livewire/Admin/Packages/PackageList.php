<?php

namespace App\Livewire\Admin\Packages;

use App\Livewire\Admin\AdminComponent;
use App\Models\Package;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Packages')]
class PackageList extends AdminComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    private int $pageLength = 20;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $packageId): void
    {
        $package = Package::find($packageId);
        if ($package) {
            $package->delete();
            $this->dispatch('toast', ...$this->success(['text' => 'Package deleted successfully']));
        } else {
            $this->dispatch('toast', ...$this->warning(['text' => 'Unable to delete. Package cannot be found']));
        }
    }

    public function render(): View
    {
        $packages = Package::query()
            ->withCount('features')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate($this->pageLength);

        return view('livewire.admin.packages.package-list', [
            'packages' => $packages,
            'total' => Package::count(),
        ]);
    }
}
