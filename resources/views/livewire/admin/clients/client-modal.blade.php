<x-modal
    :title="$clientId ? 'Edit client' : 'Add a client'"
    subtitle="Clients appear on proposals and in the presentation view.">
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 gap-6 px-8 py-7 sm:grid-cols-2">
            <x-field
                label="Company name"
                name="name"
                autocomplete="organization"
                placeholder="Halverson Studio"
                class="sm:col-span-2" />

            <x-field
                label="Contact name"
                name="contact"
                autocomplete="name"
                placeholder="Avery Halverson" />

            <x-field
                label="Contact phone"
                name="contactPhone"
                type="tel"
                autocomplete="tel"
                placeholder="+44 20 7946 0958" />

            <x-field
                label="Contact email"
                name="contactEmail"
                type="email"
                autocomplete="email"
                placeholder="avery@halverson.studio"
                class="sm:col-span-2" />
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
            <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
            <x-btn variant="accent" type="submit">
                {{ $clientId ? 'Save changes' : 'Create client' }}
            </x-btn>
        </div>
    </form>
</x-modal>
