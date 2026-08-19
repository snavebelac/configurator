<?php

namespace App\Livewire\Admin\Features;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
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

    public string $pricingType = PricingType::Fixed->value;

    public string $price = '';

    public string $percentage = '';

    public ?string $billingPeriod = BillingPeriod::Monthly->value;

    public string $quantity = '';

    public bool $optional = false;

    public ?int $parentId = null;

    public bool $hasChildren = false;

    protected function rules(): array
    {
        $isPercentage = $this->pricingType === PricingType::Percentage->value;
        $isRecurring = $this->pricingType === PricingType::Recurring->value;

        // Neither a percentage nor a recurring charge belongs under a parent
        // feature: one is a share of the whole proposal, the other is a
        // separate ongoing commitment. And a percentage carries no quantity —
        // "10% x 3" means nothing.
        $parentRule = ($this->hasChildren || $isPercentage || $isRecurring)
            ? ['nullable', 'prohibited']
            : ['nullable', 'integer', Rule::exists('features', 'id')->whereNull('parent_id')];

        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'pricingType' => ['required', Rule::enum(PricingType::class)],
            'price' => $isPercentage ? ['nullable'] : ['required', 'numeric', 'decimal:0,2'],
            'percentage' => $isPercentage
                ? ['required', 'numeric', 'gt:0', 'max:100']
                : ['nullable'],
            'billingPeriod' => $isRecurring
                ? ['required', Rule::enum(BillingPeriod::class)]
                : ['nullable'],
            'quantity' => $isPercentage ? ['nullable'] : ['required', 'numeric', 'integer', 'min:1'],
            'parentId' => $parentRule,
        ];
    }

    protected $messages = [
        'parentId.exists' => 'The selected parent feature no longer exists or is itself a child.',
        'parentId.prohibited' => 'This feature can\'t be placed under another parent.',
        'percentage.gt' => 'Enter a percentage greater than zero.',
        'percentage.max' => 'A percentage can\'t be more than 100%.',
    ];

    public function mount(?int $featureId = null): void
    {
        if ($featureId) {
            $this->featureId = $featureId;
            $feature = Feature::find($featureId);
            if ($feature) {
                $this->name = $feature->name;
                $this->description = $feature->description;
                $this->pricingType = $feature->pricing_type->value;
                $this->price = (string) $feature->price;
                $this->percentage = $feature->isPercentage() ? (string) $feature->percentage : '';
                $this->billingPeriod = $feature->billing_period?->value ?? BillingPeriod::Monthly->value;
                $this->quantity = (string) $feature->quantity;
                $this->optional = $feature->optional;
                $this->parentId = $feature->parent_id;
                $this->hasChildren = $feature->children()->exists();
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $isPercentage = $this->pricingType === PricingType::Percentage->value;
        $isRecurring = $this->pricingType === PricingType::Recurring->value;

        $updateData = [
            'name' => $this->name,
            'description' => $this->description,
            'pricing_type' => $this->pricingType,
            // A percentage line has no fixed price, no quantity and no parent.
            // Zeroing them here rather than leaving stale values behind keeps
            // the pricing calculation from ever seeing a contradiction.
            'price' => $isPercentage ? 0 : $this->price,
            'percentage_rate' => $isPercentage ? (int) round(((float) $this->percentage) * 100) : 0,
            'billing_period' => $isRecurring ? $this->billingPeriod : null,
            'quantity' => $isPercentage ? 1 : $this->quantity,
            'optional' => $this->optional,
            'parent_id' => ($isPercentage || $isRecurring) ? null : $this->parentId,
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
            'pricingTypes' => PricingType::cases(),
            'billingPeriods' => BillingPeriod::cases(),
        ]);
    }
}
