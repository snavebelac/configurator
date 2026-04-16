<?php

namespace App\Livewire\Admin\Packages;

use App\Livewire\Admin\AdminComponent;
use App\Models\Package;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Edit package')]
class PackageEdit extends AdminComponent
{
    public Package $package;

    #[Validate('required|max:255')]
    public string $name = '';

    #[Validate('nullable|max:2000')]
    public string $description = '';

    public function mount(Package $package): void
    {
        $this->package = $package;
        $this->name = $package->name;
        $this->description = $package->description ?? '';
    }

    public function updatedName(string $value): void
    {
        $this->validateOnly('name');
        $this->package->update(['name' => $value]);
    }

    public function updatedDescription(string $value): void
    {
        $this->validateOnly('description');
        $this->package->update(['description' => $value ?: null]);
    }

    #[On('feature-picked')]
    public function addFeature(int $featureId): void
    {
        if ($this->package->features()->where('feature_id', $featureId)->exists()) {
            return;
        }

        $this->package->features()->attach($featureId, [
            'tenant_id' => $this->package->tenant_id,
        ]);

        $this->package->load('features');

        $this->dispatch('toast', ...$this->success(['text' => 'Feature added to package']));
    }

    public function removeFeature(int $featureId): void
    {
        $this->package->features()->detach($featureId);
        $this->package->load('features');

        $this->dispatch('toast', ...$this->success(['text' => 'Feature removed from package']));
    }

    public function updatePivot(int $featureId, string $field, mixed $value): void
    {
        if (! in_array($field, ['quantity', 'price', 'optional'], true)) {
            return;
        }

        $payload = [];

        if ($field === 'quantity') {
            $payload['quantity'] = ($value === '' || $value === null) ? null : (int) $value;
        } elseif ($field === 'price') {
            // Pass pounds — the pivot setter converts to stored pence.
            $payload['price'] = ($value === '' || $value === null) ? null : (float) $value;
        } elseif ($field === 'optional') {
            $payload['optional'] = match ($value) {
                'inherit', '', null => null,
                'optional', true, 'true', '1', 1 => true,
                default => false,
            };
        }

        $this->package->features()->updateExistingPivot($featureId, $payload);
        $this->package->load('features');
    }

    public function render(): View
    {
        $features = $this->package->features()->orderBy('name')->get();
        $disabledIds = $features->pluck('id')->all();

        return view('livewire.admin.packages.package-edit', [
            'features' => $features,
            'disabledIds' => $disabledIds,
        ]);
    }
}
