<?php

namespace App\Livewire\Admin\Features;

use App\Livewire\Admin\AdminComponent;
use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class FeatureModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return '3xl';
    }

    public ?int $featureId = null;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public string $quantity = '';

    public bool $optional = false;

    public ?int $parentId = null;

    public bool $hasChildren = false;

    protected function rules(): array
    {
        $parentRule = $this->hasChildren
            ? ['nullable', 'prohibited']
            : ['nullable', 'integer', Rule::exists('features', 'id')->whereNull('parent_id')];

        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|decimal:0,2',
            'quantity' => 'required|numeric|integer|min:1',
            'parentId' => $parentRule,
        ];
    }

    protected $messages = [
        'parentId.exists' => 'The selected parent feature no longer exists or is itself a child.',
        'parentId.prohibited' => 'This feature has its own children, so it can\'t be placed under another parent.',
    ];

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
                $this->parentId = $feature->parent_id;
                $this->hasChildren = $feature->children()->exists();
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
            'parent_id' => $this->parentId,
        ];

        if ($this->featureId) {
            Feature::findOrFail($this->featureId)->update($updateData);
        } else {
            Feature::create($updateData);
        }

        $this->dispatch('toast', ...AdminComponent::success(['text' => ($this->featureId ? 'Feature updated successfully' : 'Feature created successfully')]));
        $this->dispatch('closeModal');
        $this->reset();
        $this->dispatch('refresh-features');
    }

    public function render(): View
    {
        $parentOptions = Feature::roots()
            ->when($this->featureId, fn ($query) => $query->where('id', '!=', $this->featureId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return view('livewire.admin.features.feature-modal', [
            'parentOptions' => $parentOptions,
        ]);
    }
}
