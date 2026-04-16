<x-modal
    :title="$featureId ? 'Edit feature' : 'Add a feature'"
    subtitle="Features are the reusable building blocks you drag into proposals.">
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 gap-6 px-8 py-7 sm:grid-cols-6">
            <x-field
                label="Name"
                name="name"
                placeholder="Brand identity system"
                class="sm:col-span-6" />

            <x-field
                label="Description"
                name="description"
                placeholder="What's included — one concise line."
                class="sm:col-span-6" />

            <x-field
                label="Price"
                name="price"
                type="number"
                step="0.01"
                min="0"
                prefix="£"
                class="sm:col-span-3" />

            <x-field
                label="Quantity"
                name="quantity"
                type="number"
                step="1"
                min="1"
                class="sm:col-span-3" />

            <x-checkbox-field
                label="Optional feature"
                name="optional"
                description="Clients can toggle this on or off during a live presentation."
                class="sm:col-span-6" />
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
            <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
            <x-btn variant="accent" type="submit">
                {{ $featureId ? 'Save changes' : 'Create feature' }}
            </x-btn>
        </div>
    </form>
</x-modal>
