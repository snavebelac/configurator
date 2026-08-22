<div class="max-w-3xl">

    <x-page-header
        title="Your profile."
        eyebrow="Account settings"
        lede="Update the name and email address that appear on your proposals and inside the admin." />

    <x-card>
        <form wire:submit="updateUser({{ $userId }})">
            <div class="space-y-8 px-8 py-8">

                @if (session('profile.updated'))
                    <div class="flex items-center gap-3 rounded-lg border border-status-accepted-dot/30 bg-status-accepted-bg px-4 py-2.5 text-[13px] text-status-accepted-fg">
                        <x-phosphor-check class="size-4" />
                        {{ session('profile.updated') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                    <div>
                        <label for="first-name" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">First name</label>
                        <input id="first-name"
                               type="text"
                               wire:model="name"
                               autocomplete="given-name"
                               class="block w-full rounded-lg border border-rule bg-paper-2 px-3 py-2 text-[14px] text-ink focus:border-ink focus:bg-white focus:outline-none transition-colors">
                        @error('name')
                            <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last-name" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Last name</label>
                        <input id="last-name"
                               type="text"
                               wire:model="lastName"
                               autocomplete="family-name"
                               class="block w-full rounded-lg border border-rule bg-paper-2 px-3 py-2 text-[14px] text-ink focus:border-ink focus:bg-white focus:outline-none transition-colors">
                        @error('lastName')
                            <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Email address</label>
                        <input id="email"
                               type="email"
                               wire:model="email"
                               autocomplete="email"
                               class="block w-full rounded-lg border border-rule bg-paper-2 px-3 py-2 text-[14px] text-ink focus:border-ink focus:bg-white focus:outline-none transition-colors">
                        @error('email')
                            <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
                <x-btn variant="ghost" :href="route('dashboard')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Save changes</x-btn>
            </div>
        </form>
    </x-card>

</div>
