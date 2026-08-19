<div>
    {{-- Deliberately says nothing about the proposal itself — not its name,
         client, or value. Only that something exists at this address. --}}
    <x-auth-shell
        eyebrow="Protected proposal"
        :heading="$expired ? 'This link has expired.' : 'Enter your passcode.'"
        :lede="$expired
            ? 'The link you followed is no longer active. Get in touch with whoever sent it and they can issue a new one.'
            : 'This proposal is passcode protected. Enter the passcode you were given to view it.'">

        @unless ($expired)
            <form wire:submit="unlock" class="space-y-4">
                @if ($passcodeError)
                    <div class="rounded-lg border border-status-rejected-dot/40 bg-status-rejected-bg px-3.5 py-2.5 text-[13px] text-status-rejected-fg">
                        {{ $passcodeError }}
                    </div>
                @endif

                <x-field
                    label="Passcode"
                    name="passcode"
                    type="password"
                    autocomplete="off"
                    required />

                <x-btn variant="accent" type="submit"
                       class="mt-2 w-full justify-center"
                       wire:loading.attr="disabled"
                       wire:target="unlock">
                    <span wire:loading.remove wire:target="unlock">View proposal</span>
                    <span wire:loading wire:target="unlock">Checking…</span>
                </x-btn>
            </form>
        @endunless
    </x-auth-shell>
</div>
