<x-modal
    title="Share this proposal"
    subtitle="Send the link below to your client. Optional expiry and 6-digit access code controls live alongside.">
    <form wire:submit.prevent="save">
        <div class="space-y-7 px-8 py-7">

            <section>
                <label class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Public link</label>
                <div class="flex items-stretch gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $shareUrl }}"
                        class="flex-1 rounded-lg border border-rule bg-paper-2 px-3 py-2 font-mono text-[13px] text-ink focus:outline-none"
                        x-on:focus="$event.target.select()">
                    <x-btn
                        type="button"
                        variant="ghost"
                        x-data="{ copied: false }"
                        x-on:click="
                            navigator.clipboard.writeText('{{ $shareUrl }}');
                            copied = true;
                            setTimeout(() => copied = false, 1800);
                        "
                    >
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-status-accepted-fg">Copied!</span>
                    </x-btn>
                </div>
                <p class="mt-1.5 text-[12px] text-slate">Anyone with the link can view the proposal until it expires (and only after entering the access code, if one is set).</p>
            </section>

            <section class="border-t border-rule-soft pt-6">
                <x-field
                    label="Expires on"
                    name="expiresAtDate"
                    type="date"
                    hint="Leave blank for no expiry. Default is set in workspace settings." />
            </section>

            <section class="border-t border-rule-soft pt-6">
                <x-checkbox-field
                    name="codeRequired"
                    modelLive="codeRequired"
                    label="Require a 6-digit access code"
                    description="Adds a code-entry step before the proposal can be viewed. Share the code with your client through a different channel (text, voice) for an extra layer." />

                @if ($codeRequired)
                    <div class="mt-5 rounded-lg border border-rule bg-paper-2 px-5 py-4">
                        @if ($generatedCode !== null)
                            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate">New code · save the modal to apply</p>
                            <div class="mt-2 flex items-center gap-3">
                                <code class="font-mono text-[26px] tracking-[0.5em] text-ink">{{ $generatedCode }}</code>
                                <x-btn
                                    type="button"
                                    variant="ghost"
                                    x-data="{ copied: false }"
                                    x-on:click="
                                        navigator.clipboard.writeText('{{ $generatedCode }}');
                                        copied = true;
                                        setTimeout(() => copied = false, 1800);
                                    "
                                >
                                    <span x-show="!copied">Copy code</span>
                                    <span x-show="copied" x-cloak class="text-status-accepted-fg">Copied!</span>
                                </x-btn>
                            </div>
                            <p class="mt-2 text-[12px] text-slate">For security we don&rsquo;t store the plain code — copy it now. Saving will replace any previous code; existing visitors will be locked out and will need the new code.</p>
                        @elseif ($proposal->requiresCode())
                            <p class="text-[12.5px] text-slate">A code is currently in place. Generating a new one will replace it and immediately invalidate the previous one (anyone already viewing will need to re-enter).</p>
                            <div class="mt-3">
                                <x-btn type="button" variant="ghost" wire:click="generateCode">Regenerate code</x-btn>
                            </div>
                        @else
                            <p class="text-[12.5px] text-slate">No code set yet. Generate one to lock the link.</p>
                            <div class="mt-3">
                                <x-btn type="button" variant="ghost" wire:click="generateCode">Generate code</x-btn>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

        </div>

        <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
            <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
            <x-btn variant="accent" type="submit">Save share settings</x-btn>
        </div>
    </form>
</x-modal>
