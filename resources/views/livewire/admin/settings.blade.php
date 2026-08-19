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

                <div>
                    <p class="mb-1.5 text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Logo</p>

                    <div class="flex items-start gap-5">
                        <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-rule bg-paper-2">
                            @if ($logo && $logo->isPreviewable())
                                {{-- isPreviewable() guards the case where someone picks a
                                     non-image: temporaryUrl() throws on those, which would
                                     take the page down instead of showing the error. --}}
                                <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="max-h-full max-w-full object-contain">
                            @elseif ($storedLogo)
                                <img src="{{ Storage::disk('public')->url($storedLogo) }}" alt="Your logo" class="max-h-full max-w-full object-contain">
                            @else
                                <x-phosphor-image class="size-6 text-slate-faint" />
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <input type="file"
                                   id="logo"
                                   wire:model="logo"
                                   accept="image/png,image/jpeg,image/webp"
                                   class="block w-full text-[13px] text-slate file:mr-3 file:rounded-lg file:border file:border-rule file:bg-white file:px-3 file:py-1.5 file:text-[13px] file:font-medium file:text-ink hover:file:bg-paper-2">

                            <p class="mt-1.5 text-[12px] text-slate">
                                PNG, JPG or WebP, up to 2&nbsp;MB. Applied when you save.
                            </p>

                            <div wire:loading wire:target="logo" class="mt-1.5 text-[12px] text-slate">Uploading…</div>

                            @error('logo')
                                <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
                            @enderror

                            @if ($storedLogo)
                                <button type="button"
                                        wire:click="removeLogo"
                                        wire:confirm="Remove your logo?"
                                        class="mt-2 text-[12.5px] font-medium text-status-rejected-fg underline-offset-4 hover:underline">
                                    Remove logo
                                </button>
                            @endif
                        </div>
                    </div>
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
