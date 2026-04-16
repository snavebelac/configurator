<div>
    <x-auth-shell
        eyebrow="ConfiguPro · Sign in"
        heading="Welcome back."
        lede="Enter your credentials to pick up where you left off.">

        @if ($loginMessage)
            <div class="mb-5 rounded-lg border border-status-rejected-dot/40 bg-status-rejected-bg px-3.5 py-2.5 text-[13px] text-status-rejected-fg">
                {{ $loginMessage }}
            </div>
        @endif

        <form wire:submit="authenticate" class="space-y-4">
            <x-field label="Email" name="email" type="email" autocomplete="email" required />
            <x-field label="Password" name="password" type="password" autocomplete="current-password" required />

            <div class="flex items-center justify-between pt-1">
                <label for="remember-me" class="flex cursor-pointer items-center gap-2.5">
                    <input type="checkbox" id="remember-me" name="remember" wire:model="remember"
                           class="size-4 rounded border-rule bg-paper-2 accent-ink focus:ring-2 focus:ring-ink focus:ring-offset-0">
                    <span class="text-[13px] text-ink">Remember me</span>
                </label>

                <a href="{{ route('password.request') }}"
                   class="text-[13px] font-medium text-slate underline-offset-4 transition-colors hover:text-ink hover:underline">
                    Forgot password?
                </a>
            </div>

            <x-btn variant="accent" type="submit"
                   class="mt-2 w-full justify-center"
                   wire:loading.attr="disabled"
                   wire:target="authenticate">
                <span wire:loading.remove wire:target="authenticate">Sign in</span>
                <span wire:loading wire:target="authenticate">Signing in…</span>
            </x-btn>
        </form>

        <x-slot:footer>
            Need an account? Ask your workspace admin for an invite.
        </x-slot:footer>
    </x-auth-shell>
</div>
