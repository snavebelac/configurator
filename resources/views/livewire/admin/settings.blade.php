<div class="max-w-3xl">

    <x-page-header
        title="Workspace settings."
        eyebrow="Account settings"
        lede="Currency, tax labelling, and the default lifetime applied to share-link previews when a proposal is shared with a client." />

    <x-card>
        <form wire:submit="save">
            <div class="space-y-8 px-8 py-8">

                <section class="space-y-6">
                    <h2 class="font-display text-[18px] tracking-[-0.01em] text-ink">Currency &amp; tax</h2>

                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                        <x-select-field
                            name="currency"
                            label="Currency"
                            :options="$currencyOptions"
                            placeholder="Select a currency" />

                        <x-field
                            name="taxName"
                            label="Tax label"
                            placeholder="VAT, GST, Sales Tax…"
                            hint="The name shown next to the tax line on proposals." />

                        <x-field
                            name="taxRate"
                            label="Tax rate"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="20"
                            hint="Percentage applied to the proposal subtotal." />

                        <div class="flex items-end">
                            <x-checkbox-field
                                name="taxInclusive"
                                label="Prices are tax-inclusive"
                                description="When enabled, line-item prices already include tax — no additional tax line is rendered on proposals." />
                        </div>
                    </div>
                </section>

                <section class="space-y-6 border-t border-rule-soft pt-8">
                    <h2 class="font-display text-[18px] tracking-[-0.01em] text-ink">Sharing defaults</h2>

                    <x-field
                        name="defaultShareExpiryDays"
                        label="Default share-link expiry"
                        type="number"
                        min="1"
                        placeholder="Leave blank for never"
                        hint="Number of days a public share link stays live by default. Leave blank for no expiry. You can override this per-proposal when sharing." />
                </section>

            </div>

            <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
                <x-btn variant="ghost" :href="route('dashboard')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Save settings</x-btn>
            </div>
        </form>
    </x-card>

</div>
