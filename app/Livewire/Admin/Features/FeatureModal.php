<?php

namespace App\Livewire\Admin\Features;

use App\Models\Feature;
use Illuminate\Contracts\View\View;
use LivewireUI\Modal\ModalComponent;
use App\Livewire\Admin\AdminComponent;

class FeatureModal extends ModalComponent
{
    public ?int $featureId = null;
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $quantity = '';
    public bool $optional = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|decimal:0,2',
            'quantity' => 'required|numeric|integer|min:1',
        ];
    }

    public function mount(?int $featureId = null): void
    {
        if ($featureId) {
            $this->featureId = $featureId;
            $feature = Feature::find($featureId);
            if ($feature) {
                $this->name = $feature->name;
                $this->description = $feature->description;
                $this->price = $feature->price;
                $this->quantity = $feature->quantity;
                $this->optional = $feature->optional;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $updateData = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'optional' => $this->optional,
        ];

        if ($this->featureId) {
            Feature::findOrFail($this->featureId)->update($updateData);
        } else {
            Feature::create($updateData);
        }

        $this->dispatch('toast', ...AdminComponent::success(['text' => ($this->featureId ? 'Feature updated successfully' : 'Feature created successfully')]));
        $this->dispatch('closeModal'); // Close modal after save
        $this->reset();
        $this->dispatch('refresh-features');
    }

    public function render(): View
    {
        return view('livewire.admin.features.feature-modal');
    }
}
