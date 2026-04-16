@php
    use Illuminate\Support\Facades\Auth;
    $total = count($users);
    $activeCount = collect($users)->where('active', true)->count();
    $currentUserId = Auth::id();
@endphp
<div class="mx-auto max-w-[1480px]">

    <x-page-header
        title="Team."
        :eyebrow="$total . ' ' . Str::plural('member', $total) . ' · ' . $activeCount . ' active'"
        lede="Invite teammates, manage their roles, and deactivate anyone who shouldn't be signing in.">
        <x-slot:actions>
            <x-btn variant="accent" wire:click="$dispatch('openModal', {component: 'admin.users.user-modal'})">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Add user
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:36%">Name</x-th>
                    <x-th>Email</x-th>
                    <x-th>Role</x-th>
                    <x-th>Status</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" @class([
                        'group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0',
                        'opacity-60' => ! $user->active,
                    ])>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="flex items-center gap-2.5">
                                <span class="font-medium">{{ $user->full_name }}</span>
                                @if ($user->id === $currentUserId)
                                    <span class="rounded-full bg-sage px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-ink">You</span>
                                @endif
                            </div>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-slate">
                            {{ $user->email }}
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-xs uppercase tracking-[0.08em] text-slate">
                            @if ($user->roles->isNotEmpty())
                                {{ $user->roles->pluck('name')->implode(', ') }}
                            @else
                                <span class="text-slate-soft">No role</span>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            @if ($user->active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-status-accepted-bg px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5 text-status-accepted-fg">
                                    <span class="size-1.5 rounded-full bg-status-accepted-dot"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-status-archived-bg px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5 text-status-archived-fg">
                                    <span class="size-1.5 rounded-full bg-status-archived-dot"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <x-btn variant="row" wire:click="$dispatch('openModal', {component: 'admin.users.user-modal', arguments: {userId: {{ $user->id }} }})">
                                    Edit
                                </x-btn>
                                @if ($user->id !== $currentUserId)
                                    <x-btn variant="row" class="text-status-rejected-fg hover:bg-status-rejected-bg"
                                           wire:click="delete({{ $user->id }})"
                                           wire:confirm="Are you sure you wish to delete [{{ $user->full_name }}]?">
                                        Delete
                                    </x-btn>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center text-sm text-slate">No users yet — invite your first teammate.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

</div>
