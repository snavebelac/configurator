<?php

namespace App\Livewire\Admin\Proposals;

use App\Models\Package;
use Illuminate\Contracts\View\View;
use LivewireUI\Modal\ModalComponent;

class PackagePickerModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }

    public string $search = '';

    public function pick(int $packageId): void
    {
        $this->dispatch('package-picked', packageId: $packageId);
        $this->dispatch('closeModal');
    }

    public function render(): View
    {
        $packages = Package::query()
            ->withCount('features')
            ->with('features')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.proposals.package-picker-modal', [
            'packages' => $packages,
        ]);
    }
}
