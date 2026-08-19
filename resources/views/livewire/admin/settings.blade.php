<div class="mx-auto max-w-3xl">

    <x-page-header
        title="Workspace settings."
        eyebrow="Configuration"
        lede="How money and tax are presented across your proposals — including the client-facing view." />

    <x-card>
        <form wire:submit="save">
            <div class="space-y-8 px-8 py-8">

                <div>
                    <x-field
                        name="companyName"
                        label="Company name"
                        hint="Shown on client-facing proposals. Defaults to your workspace name if left blank."
                        autocomplete="organization" />
                </div>

                <div class="border-t border-rule-soft pt-8">
                    <h3 class="mb-1 font-display text-[16px] text-ink">Money</h3>
                    <p class="mb-5 text-[12.5px] text-slate">
                        Applied to every price in the admin and on proposals you share with clients.
                    </p>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                        <x-select-field
                            name="currency"
                            label="Currency"
                            :options="$currencyOptions"
                            placeholder="Select a currency…"
                            required />

                        <x-field
                            name="taxName"
                            label="Tax name"
                            placeholder="VAT"
                            hint="e.g. VAT, GST, Sales Tax."
                            required />

                        <x-field
                            name="taxRate"
                            label="Tax rate"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="20"
                            hint="A percentage, between 0 and 100."
                            required />
                    </div>

                    <div class="mt-6">
                        <x-checkbox-field
                            name="taxInclusive"
                            label="Prices already include tax"
                            description="Tick if the prices you enter are tax-inclusive rather than tax being added on top." />
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
                <x-btn variant="ghost" :href="route('dashboard')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">
                    <span wire:loading.remove wire:target="save">Save settings</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </x-btn>
            </div>
        </form>
    </x-card>

</div>
