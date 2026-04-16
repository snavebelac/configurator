<div>
    <x-auth-shell
        eyebrow="ConfiguPro · New password"
        heading="Set a new password."
        lede="Confirm your email and choose a fresh password for your account.">

        @isset ($message)
            <div class="mb-5 rounded-lg border border-status-rejected-dot/40 bg-status-rejected-bg px-3.5 py-2.5 text-[13px] text-status-rejected-fg">
                {{ $message }}
            </div>
        @endisset

        <form wire:submit="updatePassword" class="space-y-4">
            <input type="hidden" wire:model="token">

            <x-field label="Email" name="email" type="email" autocomplete="email" required />
            <x-field label="New password" name="password" type="password" autocomplete="new-password" required
                     hint="At least 8 characters." />
            <x-field label="Confirm new password" name="passwordConfirmation" type="password" autocomplete="new-password" required />

            <x-btn variant="accent" type="submit"
                   class="mt-1 w-full justify-center"
                   wire:loading.attr="disabled"
                   wire:target="updatePassword">
                <span wire:loading.remove wire:target="updatePassword">Update password</span>
                <span wire:loading wire:target="updatePassword">Updating…</span>
            </x-btn>
        </form>

        <x-slot:footer>
            <a href="{{ route('login') }}" class="font-medium text-ink underline-offset-4 hover:underline">Back to sign in</a>
        </x-slot:footer>
    </x-auth-shell>
</div>
