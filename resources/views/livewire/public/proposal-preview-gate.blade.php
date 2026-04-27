<div>
    <x-auth-shell
        eyebrow="Proposal access"
        heading="Enter your access code."
        lede="Your sender shared a 6-digit code along with this link. Enter it below to view the proposal.">

        <form wire:submit="submitCode" class="space-y-5">
            <div>
                <label for="code" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Access code</label>
                <input
                    id="code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    pattern="[0-9]*"
                    maxlength="6"
                    wire:model="code"
                    class="block w-full rounded-lg border border-rule bg-paper-2 px-3 py-2.5 text-center font-mono text-[18px] tracking-[0.4em] text-ink focus:border-ink focus:bg-white focus:outline-none transition-colors"
                    autofocus>
                @error('code')
                    <p class="mt-2 text-[12.5px] text-status-rejected-fg">{{ $message }}</p>
                @enderror
            </div>

            <x-btn variant="accent" type="submit" class="w-full justify-center">Unlock proposal</x-btn>
        </form>

        <x-slot:footer>
            Don&rsquo;t have a code? Get back in touch with the sender.
        </x-slot:footer>
    </x-auth-shell>
</div>
