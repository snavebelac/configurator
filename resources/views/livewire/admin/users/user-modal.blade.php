@php
    $roleOptions = collect($roles)->mapWithKeys(fn ($role) => [$role->name => $role->name])->all();
@endphp
<x-modal
    :title="$user ? 'Edit user' : 'Invite a user'"
    :subtitle="$user ? 'Update their account details or swap their role.' : 'Invite a teammate to the workspace.'">
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 gap-6 px-8 py-7 sm:grid-cols-2">
            <x-field
                label="First name"
                name="name"
                autocomplete="given-name"
                placeholder="Michael" />

            <x-field
                label="Last name"
                name="lastName"
                autocomplete="family-name"
                placeholder="Garibaldi" />

            <x-field
                label="Email address"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="m.garibaldi@earthforce.mil"
                class="sm:col-span-2" />

            <x-select-field
                label="Role"
                name="role"
                :options="$roleOptions"
                placeholder="Choose a role…"
                class="sm:col-span-2" />

            <x-checkbox-field
                label="Active"
                name="active"
                description="Deactivate to block this user from signing in without deleting them."
                class="sm:col-span-2" />

            <div class="sm:col-span-2 border-t border-rule-soft pt-6">
                <p class="mb-4 text-[12.5px] text-slate">
                    @if ($user)
                        Leave both password fields blank to keep the current password.
                    @else
                        Set an initial password. The user can change it once signed in.
                    @endif
                </p>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-field
                        label="Password"
                        name="password"
                        type="password"
                        autocomplete="new-password" />

                    <x-field
                        label="Confirm password"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
            <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
            <x-btn variant="accent" type="submit">
                {{ $user ? 'Save changes' : 'Create user' }}
            </x-btn>
        </div>
    </form>
</x-modal>
