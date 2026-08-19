<div>
    <x-card>
        <x-card-header title="Client access" />

        <div class="space-y-7 px-[22px] py-6">

            {{-- Share link --}}
            <div>
                <p class="mb-1.5 text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Shareable link</p>
                <div class="flex items-center gap-2" x-data="{ copied: false }">
                    <input type="text"
                           readonly
                           value="{{ $shareUrl }}"
                           x-ref="shareUrl"
                           class="block w-full truncate rounded-lg border border-rule bg-paper-2 px-3 py-2 font-mono text-[12.5px] text-slate">
                    <x-btn variant="ghost" type="button"
                           x-on:click="
                               navigator.clipboard.writeText($refs.shareUrl.value);
                               copied = true;
                               setTimeout(() => copied = false, 2000);
                           ">
                        <span x-show="! copied">Copy</span>
                        <span x-show="copied" x-cloak>Copied</span>
                    </x-btn>
                </div>
                <p class="mt-1.5 text-[12px] text-slate">
                    Anyone with this link can view the proposal. No sign-in required.
                </p>
            </div>

            {{-- Passcode --}}
            <div class="border-t border-rule-soft pt-6">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Passcode</p>
                    @if ($proposal->isPasscodeProtected())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-status-accepted-bg px-2.5 py-0.5 text-[11px] font-medium text-status-accepted-fg">
                            <x-phosphor-lock class="size-3" />
                            Protected
                        </span>
                    @else
                        <span class="text-[12px] text-slate">Not set</span>
                    @endif
                </div>

                <form wire:submit="setPasscode" class="flex items-start gap-2">
                    <x-field
                        label="{{ $proposal->isPasscodeProtected() ? 'Replace passcode' : 'Set a passcode' }}"
                        name="passcode"
                        type="password"
                        autocomplete="off"
                        hint="At least 4 characters. Send it to your client separately from the link."
                        class="flex-1" />
                    <x-btn variant="accent" type="submit" class="mt-[26px]">Save</x-btn>
                </form>

                @if ($proposal->isPasscodeProtected())
                    <button type="button"
                            wire:click="clearPasscode"
                            wire:confirm="Remove the passcode? Anyone with the link will then be able to view this proposal."
                            class="mt-3 text-[12.5px] font-medium text-status-rejected-fg underline-offset-4 hover:underline">
                        Remove passcode
                    </button>
                @endif
            </div>

            {{-- Expiry --}}
            <div class="border-t border-rule-soft pt-6">
                <form wire:submit="saveExpiry" class="flex items-start gap-2">
                    <x-field
                        label="Link expires"
                        name="expiresAt"
                        type="date"
                        hint="Leave blank for a link that never expires."
                        class="flex-1" />
                    <x-btn variant="ghost" type="submit" class="mt-[26px]">Save</x-btn>
                </form>
            </div>

        </div>
    </x-card>
</div>
