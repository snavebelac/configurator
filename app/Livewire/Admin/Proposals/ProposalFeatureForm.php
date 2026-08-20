<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Models\FinalFeature;
use Livewire\Component;

class ProposalFeatureForm extends Component
{
    public FinalFeature $finalFeature;

    public string $name = '';

    public $price = 0;

    public int $quantity = 1;

    public bool $optional = false;

    public bool $isChild = false;

    public string $gridTemplate = '';

    /**
     * How this line is priced. Drives which fields the row offers at all: a
     * percentage has no unit price or quantity, and a recurring charge has a
     * billing period instead of a one-off line total.
     */
    public string $pricingType = '';

    public $percentage = 0;

    public ?string $billingPeriod = null;

    /**
     * The fixed-price base a percentage line takes its share of, in pence,
     * handed down by the parent so the row can show its own amount without
     * every row recomputing the whole proposal.
     */
    public int $fixedBase = 0;

    public function mount(
        int $finalFeatureId,
        bool $isChild = false,
        string $gridTemplate = '',
        int $fixedBase = 0,
    ): void {
        $this->finalFeature = FinalFeature::findOrFail($finalFeatureId);
        $this->name = $this->finalFeature->name;
        $this->price = $this->finalFeature->price;
        $this->quantity = $this->finalFeature->quantity;
        $this->optional = $this->finalFeature->optional;
        $this->pricingType = $this->finalFeature->pricing_type->value;
        $this->percentage = $this->finalFeature->percentage;
        $this->billingPeriod = $this->finalFeature->billing_period?->value;
        $this->isChild = $isChild;
        $this->gridTemplate = $gridTemplate;
        $this->fixedBase = $fixedBase;
    }

    public function updated($name, $value): void
    {
        $attributes = [
            'name' => 'name',
            'price' => 'price',
            'quantity' => 'quantity',
            'optional' => 'optional',
            'percentage' => 'percentage',
            'billingPeriod' => 'billing_period',
        ];

        if (! array_key_exists($name, $attributes)) {
            return;
        }

        $this->finalFeature->{$attributes[$name]} = $value;
        $this->finalFeature->save();
        $this->dispatch('refreshFeatureProposalEdit');
    }

    public function isPercentage(): bool
    {
        return $this->pricingType === PricingType::Percentage->value;
    }

    public function isRecurring(): bool
    {
        return $this->pricingType === PricingType::Recurring->value;
    }

    /**
     * What a percentage line comes to against the current fixed base, in
     * currency units. Mirrors ProposalPricing::lineAmount — rounded half up at
     * the penny, quantity ignored.
     */
    public function percentageAmount(): float
    {
        return round($this->fixedBase * ((float) $this->percentage) / 100) / 100;
    }

    public function removeFinalFeature(): void
    {
        $this->finalFeature->delete();
        $this->dispatch('refreshFeatureProposalEdit');
        $this->dispatch('finalFeatureRemoved', id: $this->finalFeature->id);
    }

    public function render()
    {
        return view('livewire.admin.proposals.proposal-feature-form', [
            'billingPeriods' => BillingPeriod::cases(),
        ]);
    }
}
