<div class="mx-auto max-w-[760px]">

    <x-page-header
        title="New package."
        eyebrow="Step 1 · Name your package"
        lede="Create a package to drop onto proposals in one click. You'll pick its features on the next step.">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.packages')">Cancel</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit.prevent="createPackage">
        <x-card>
            <div class="flex flex-col gap-6 px-8 py-7">
                <x-field
                    label="Name"
                    name="name"
                    placeholder="E.g. Standard brochure website" />

                <x-field
                    label="Description"
                    name="description"
                    placeholder="A short summary for your team — what this bundle typically covers." />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
                <x-btn variant="ghost" :href="route('dashboard.packages')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">
                    Create &amp; continue
                    <x-phosphor-arrow-right class="size-3.5" />
                </x-btn>
            </div>
        </x-card>
    </form>
</div>
