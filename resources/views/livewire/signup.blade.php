<div>
    <x-auth-shell
        eyebrow="Configurator · Get started"
        heading="Create your workspace."
        lede="Your own tenancy, with its own clients, features and proposals.">

        <form wire:submit="register" class="space-y-4">
            <x-field
                label="Company name"
                name="company"
                autocomplete="organization"
                hint="What your workspace will be called."
                required />

            <div class="grid grid-cols-2 gap-4">
                <x-field label="First name" name="name" autocomplete="given-name" required />
                <x-field label="Last name" name="lastName" autocomplete="family-name" required />
            </div>

            <x-field label="Email" name="email" type="email" autocomplete="email" required />

            <x-field
                label="Password"
                name="password"
                type="password"
                autocomplete="new-password"
                required />

            <x-field
                label="Confirm password"
                name="passwordConfirmation"
                type="password"
                autocomplete="new-password"
                required />

            <x-btn variant="accent" type="submit"
                   class="mt-2 w-full justify-center"
                   wire:loading.attr="disabled"
                   wire:target="register">
                <span wire:loading.remove wire:target="register">Create workspace</span>
                <span wire:loading wire:target="register">Creating…</span>
            </x-btn>
        </form>

        <x-slot:footer>
            Already have an account?
            <a href="{{ route('login') }}"
               class="font-medium text-ink underline-offset-4 transition-colors hover:underline">
                Sign in
            </a>
        </x-slot:footer>
    </x-auth-shell>
</div>
