<x-modal
    :title="$featureId ? 'Edit feature' : 'Add a feature'"
    subtitle="Features are the reusable building blocks you drag into proposals.">
    <form wire:submit.prevent="save">
        <div class="flex flex-col gap-6 px-8 py-7">

            <x-field
                label="Name"
                name="name"
                placeholder="Hydroponics bay refit" />

            <x-field
                label="Description"
                name="description"
                placeholder="Enough greens to keep Down Below fed for a month." />

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
