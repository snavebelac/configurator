<div>
    <x-auth-shell
        eyebrow="ConfiguPro · Recovery"
        heading="Reset your password."
        lede="Enter the email on your account and we'll send you a link to set a new password.">

        @isset ($errorMessage)
            <div class="mb-5 rounded-lg border border-status-rejected-dot/40 bg-status-rejected-bg px-3.5 py-2.5 text-[13px] text-status-rejected-fg">
                {{ $errorMessage }}
            </div>
        @endisset
        @isset ($successMessage)
            <div class="mb-5 rounded-lg border border-status-accepted-dot/40 bg-status-accepted-bg px-3.5 py-2.5 text-[13px] text-status-accepted-fg">
                {{ $successMessage }}
            </div>
        @endisset

        <form wire:submit="resetPassword" class="space-y-4">
            <x-field label="Email" name="email" type="email" autocomplete="email" required />

            <x-btn variant="accent" type="submit"
                   class="mt-1 w-full justify-center"
                   wire:loading.attr="disabled"
                   wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Send reset link</span>
                <span wire:loading wire:target="resetPassword">Sending…</span>
            </x-btn>
        </form>

        <x-slot:footer>
            Remembered it? <a href="{{ route('login') }}" class="font-medium text-ink underline-offset-4 hover:underline">Back to sign in</a>
        </x-slot:footer>
    </x-auth-shell>
</div>
