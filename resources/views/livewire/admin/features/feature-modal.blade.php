@php
    $parentLockReason = $hasChildren
        ? 'This feature has its own children, so it must stay at the top level.'
        : null;
@endphp
<x-modal
    :title="$featureId ? 'Edit feature' : 'Add a feature'"
    subtitle="Features are the reusable building blocks you drag into proposals.">
    <form wire:submit.prevent="save">
        <div class="flex flex-col gap-6 px-8 py-7">

            {{-- How this line is priced. A percentage is a share of the rest
                 of the proposal, so it hides price, quantity and parent. --}}
            <div>
                <p class="mb-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate">How is this priced?</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($pricingTypes as $type)
                        <button type="button"
                                wire:click="$set('pricingType', '{{ $type->value }}')"
                                @class([
                                    'rounded-lg border px-3.5 py-2.5 text-left text-[13px] transition-colors',
                                    'border-ink bg-paper-2 text-ink' => $pricingType === $type->value,
                                    'border-rule text-slate hover:border-slate-faint hover:bg-paper-2' => $pricingType !== $type->value,
                                ])>
                            <span class="block font-medium">{{ $type->label() }}</span>
                            <span class="mt-0.5 block text-[11.5px] text-slate">{{ $type->description() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-6 border-t border-rule-soft pt-5">
                <x-field
                    label="Name"
                    name="name"
                    placeholder="Hydroponics bay refit" />

                <x-field
                    label="Description"
                    name="description"
                    placeholder="Enough greens to keep Down Below fed for a month." />
            </div>

            @if ($pricingType === \App\Enums\PricingType::Recurring->value)
                <div class="grid grid-cols-2 gap-6">
                    <x-field
                        label="Amount per period"
                        name="price"
                        type="number"
                        step="0.01"
                        min="0"
                        prefix="£" />

                    <x-select-field
                        label="Billed"
                        name="billingPeriod"
                        :options="collect($billingPeriods)->mapWithKeys(fn ($p) => [$p->value => $p->label()])->all()"
                        placeholder="— Choose a period —"
                        required />
                </div>

                <x-field
                    label="Quantity"
                    name="quantity"
                    type="number"
                    step="1"
                    min="1"
                    hint="For per-seat or per-site charges. Leave at 1 otherwise." />

            @elseif ($pricingType === \App\Enums\PricingType::Percentage->value)
                <x-field
                    label="Percentage"
                    name="percentage"
                    type="number"
                    step="0.01"
                    min="0"
                    hint="Of the fixed-price lines the client has selected. Other percentage lines are excluded, so two of these never compound."
                    prefix="%" />
            @else
                <div class="grid grid-cols-2 gap-6">
                    <x-field
                        label="Price"
                        name="price"
                        type="number"
                        step="0.01"
                        min="0"
                        prefix="£" />

                    <x-field
                        label="Quantity"
                        name="quantity"
                        type="number"
                        step="1"
                        min="1" />
                </div>

                <div class="border-t border-rule-soft pt-5">
                    <x-select-field
                        label="Parent feature"
                        name="parentId"
                        :options="$parentOptions"
                        placeholder="— Standalone / parent feature —"
                        :disabled="$hasChildren"
                        :hint="$parentLockReason" />
                </div>
            @endif

            <div class="border-t border-rule-soft pt-5">
                <x-checkbox-field
                    label="Optional feature"
                    name="optional"
                    description="Clients can toggle this on or off during a live presentation, and the running total updates in real time." />
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
            <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
            <x-btn variant="accent" type="submit">
                {{ $featureId ? 'Save changes' : 'Create feature' }}
            </x-btn>
        </div>
    </form>
</x-modal>
