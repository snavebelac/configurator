<x-modal
    title="Add features"
    subtitle="Pick from the library — already-added features are marked done.">
    <div class="flex flex-col">
        <livewire:admin.features.feature-picker
            :disabled-ids="$disabledIds"
            :key="'add-features-modal-picker-'.$proposalId" />
    </div>

    <div class="flex items-center justify-between gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
        <span class="text-[12.5px] text-slate">Each pick is added straight to the proposal.</span>
        <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Done</x-btn>
    </div>
</x-modal>
