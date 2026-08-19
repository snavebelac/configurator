{{--
    Below `lg` this is an off-canvas drawer, hidden until the topbar trigger
    opens it. From `lg` up it is the static rail, at whichever width the user's
    saved preference calls for.
--}}
<div>
    {{-- Backdrop — only exists while the drawer is open, and only below lg --}}
    <div x-cloak
         x-show="$store.nav.open"
         x-transition.opacity.duration.200ms
         x-on:click="$store.nav.close()"
         class="fixed inset-0 z-40 bg-ink/40 lg:hidden"
         aria-hidden="true"></div>

    {{-- The translate classes are in the static class list as well as the
         binding, so the closed state is correct on first paint: x-cloak is no
         use here because it would hide the desktop rail until Alpine boots.
         Object syntax lets Alpine add and remove them cleanly. --}}
    <aside x-bind:class="{ 'translate-x-0': $store.nav.open, '-translate-x-full': ! $store.nav.open }"
           x-on:keydown.escape.window="$store.nav.close()"
           @class([
               'fixed inset-y-0 left-0 z-50 flex h-screen w-[264px] shrink-0 flex-col overflow-y-auto',
               'border-r border-black bg-ink px-3 py-3.5 text-sage',
               'transition-transform duration-200 ease-out',
               '-translate-x-full',
               // From lg the drawer mechanics fall away: always on-canvas, and
               // back to the saved width.
               'lg:sticky lg:top-0 lg:z-auto lg:translate-x-0',
               'lg:w-16 lg:px-0' => $collapsed,
               'lg:w-[208px]' => ! $collapsed,
           ])>

        {{-- Mark, collapse toggle (desktop) and close (drawer) --}}
        <div @class([
            'mb-4 flex items-center justify-between',
            'lg:flex-col lg:gap-2' => $collapsed,
        ])>
            <a href="{{ route('dashboard') }}"
               x-on:click="$store.nav.close()"
               @class([
                   'flex items-center gap-2.5 px-1 text-fox',
                   'lg:h-12 lg:w-16 lg:justify-center lg:gap-0 lg:px-0' => $collapsed,
               ])
               title="Configurator">
                <x-logo class="size-[22px] shrink-0" />
                <span @class(['font-display text-[15px] tracking-[-0.01em] text-sage', 'lg:hidden' => $collapsed])>
                    Configurator
                </span>
            </a>

            {{-- Collapsing is meaningless in a drawer, so desktop only --}}
            <button type="button"
                    wire:click="toggle"
                    class="group relative hidden size-8 shrink-0 items-center justify-center rounded-lg text-slate-faint transition-colors hover:bg-white/5 hover:text-sage lg:flex"
                    aria-label="{{ $collapsed ? 'Expand navigation' : 'Collapse navigation' }}"
                    aria-expanded="{{ $collapsed ? 'false' : 'true' }}">
                @if ($collapsed)
                    <x-phosphor-caret-double-right class="size-[15px]" />
                @else
                    <x-phosphor-caret-double-left class="size-[15px]" />
                @endif
            </button>

            {{-- And the mirror image: closing is meaningless on desktop --}}
            <button type="button"
                    x-on:click="$store.nav.close()"
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg text-slate-faint transition-colors hover:bg-white/5 hover:text-sage lg:hidden"
                    aria-label="Close navigation">
                <x-phosphor-x class="size-[16px]" />
            </button>
        </div>

        <nav @class(['flex flex-col gap-0.5', 'lg:items-center' => $collapsed])>
            <x-menu-item route="dashboard" title="Overview" icon="squares-four" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.proposals" title="Proposals" icon="file-text" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.clients" title="Clients" icon="users-three" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.features" title="Features" icon="stack" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.packages" title="Packages" icon="cube" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.users" title="Team" icon="user-circle" :collapsed="$collapsed" />
            <x-menu-item route="dashboard.terms" title="Terms" icon="scroll" :collapsed="$collapsed" />
        </nav>

        <div @class(['mt-auto flex flex-col gap-2', 'lg:items-center' => $collapsed])>
            <x-menu-item route="dashboard.settings" title="Settings" icon="gear" :collapsed="$collapsed" />

            <div @class(['my-1 h-px w-full bg-white/5', 'lg:mx-3 lg:w-10' => $collapsed])></div>

            <div @class([
                'flex w-full items-center gap-2.5 px-1',
                'lg:w-auto lg:flex-col lg:gap-2 lg:px-0' => $collapsed,
            ])>
                <a href="{{ route('dashboard.profile') }}"
                   x-on:click="$store.nav.close()"
                   title="{{ $user->full_name }}"
                   class="block shrink-0">
                    <img src="{{ $user->gravatar }}" alt="{{ $user->full_name }}"
                         class="size-8 rounded-full ring-1 ring-white/10">
                </a>

                <a href="{{ route('dashboard.profile') }}"
                   x-on:click="$store.nav.close()"
                   @class(['min-w-0 flex-1', 'lg:hidden' => $collapsed])>
                    <span class="block truncate text-[13px] font-medium text-sage">{{ $user->full_name }}</span>
                    <span class="block truncate text-[11px] text-slate-faint">{{ $user->tenant?->name }}</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" title="Sign out"
                            class="flex size-9 items-center justify-center rounded-[10px] text-slate-faint transition-colors hover:bg-white/5 hover:text-sage">
                        <x-phosphor-sign-out class="size-[18px]" />
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>
