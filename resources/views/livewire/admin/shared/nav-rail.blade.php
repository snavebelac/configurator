<aside @class([
    'sticky top-0 flex h-screen shrink-0 flex-col border-r border-black bg-ink py-3.5 text-sage transition-[width] duration-200',
    'w-16' => $collapsed,
    'w-[208px] px-3' => ! $collapsed,
])>

    {{-- Mark + collapse toggle --}}
    <div @class([
        'mb-4 flex items-center',
        'flex-col gap-2' => $collapsed,
        'justify-between' => ! $collapsed,
    ])>
        <a href="{{ route('dashboard') }}"
           @class([
               'flex items-center text-fox',
               'h-12 w-16 justify-center' => $collapsed,
               'gap-2.5 px-1' => ! $collapsed,
           ])
           title="Configurator">
            <x-logo class="size-[22px] shrink-0" />
            @unless ($collapsed)
                <span class="font-display text-[15px] tracking-[-0.01em] text-sage">Configurator</span>
            @endunless
        </a>

        <button type="button"
                wire:click="toggle"
                @class([
                    'group relative flex items-center justify-center rounded-lg text-slate-faint transition-colors hover:bg-white/5 hover:text-sage',
                    'size-8' => $collapsed,
                    'size-8 shrink-0' => ! $collapsed,
                ])
                aria-label="{{ $collapsed ? 'Expand navigation' : 'Collapse navigation' }}"
                aria-expanded="{{ $collapsed ? 'false' : 'true' }}">
            @if ($collapsed)
                <x-phosphor-caret-double-right class="size-[15px]" />
            @else
                <x-phosphor-caret-double-left class="size-[15px]" />
            @endif

            @if ($collapsed)
                <span class="pointer-events-none absolute left-[calc(100%+16px)] top-1/2 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink-3 px-2.5 py-1 text-xs text-sage opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 z-50">
                    Expand
                </span>
            @endif
        </button>
    </div>

    <nav @class([
        'flex flex-col gap-0.5',
        'items-center' => $collapsed,
    ])>
        <x-menu-item route="dashboard" title="Overview" icon="squares-four" :collapsed="$collapsed" />
        <x-menu-item route="dashboard.proposals" title="Proposals" icon="file-text" :collapsed="$collapsed" />
        <x-menu-item route="dashboard.clients" title="Clients" icon="users-three" :collapsed="$collapsed" />
        <x-menu-item route="dashboard.features" title="Features" icon="stack" :collapsed="$collapsed" />
        <x-menu-item route="dashboard.packages" title="Packages" icon="cube" :collapsed="$collapsed" />
        <x-menu-item route="dashboard.users" title="Team" icon="user-circle" :collapsed="$collapsed" />
    </nav>

    <div @class([
        'mt-auto flex flex-col gap-2',
        'items-center' => $collapsed,
    ])>
        <x-menu-item route="dashboard.settings" title="Settings" icon="gear" :collapsed="$collapsed" />

        <div @class([
            'my-1 h-px bg-white/5',
            'mx-3 w-10' => $collapsed,
            'w-full' => ! $collapsed,
        ])></div>

        <div @class([
            'flex items-center',
            'flex-col gap-2' => $collapsed,
            'w-full gap-2.5 px-1' => ! $collapsed,
        ])>
            <a href="{{ route('dashboard.profile') }}" title="{{ $user->full_name }}" class="block shrink-0">
                <img src="{{ $user->gravatar }}" alt="{{ $user->full_name }}"
                     class="size-8 rounded-full ring-1 ring-white/10">
            </a>

            @unless ($collapsed)
                <a href="{{ route('dashboard.profile') }}" class="min-w-0 flex-1">
                    <span class="block truncate text-[13px] font-medium text-sage">{{ $user->full_name }}</span>
                    <span class="block truncate text-[11px] text-slate-faint">{{ $user->tenant?->name }}</span>
                </a>
            @endunless

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
