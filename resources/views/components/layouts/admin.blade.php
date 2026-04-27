@php
    $crumbs = [
        'dashboard'                  => 'Overview',
        'dashboard.proposals'        => 'Proposals',
        'dashboard.proposal.create'  => 'Proposals · New',
        'dashboard.proposal.edit'    => 'Proposals · Edit',
        'dashboard.proposal.preview' => 'Proposals · Preview',
        'dashboard.clients'          => 'Clients',
        'dashboard.features'         => 'Features',
        'dashboard.packages'         => 'Packages',
        'dashboard.package.create'   => 'Packages · New',
        'dashboard.package.edit'     => 'Packages · Edit',
        'dashboard.users'            => 'Team',
        'dashboard.profile'          => 'Profile',
        'dashboard.settings'         => 'Settings',
    ];
    $crumb = $crumbs[Route::currentRouteName()] ?? 'Dashboard';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Configurator' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-paper text-ink antialiased">

<div class="grid min-h-screen grid-cols-[64px_1fr]">

    {{-- ============================================================
         NAV RAIL
         ============================================================ --}}
    <aside class="sticky top-0 flex h-screen flex-col border-r border-black bg-ink py-3.5 text-sage">
        <a href="{{ route('dashboard') }}" class="mx-auto mb-4 flex h-12 w-16 items-center justify-center text-fox" title="Configurator">
            <x-logo class="size-[22px]" />
        </a>

        <nav class="flex flex-col items-center gap-0.5">
            <x-menu-item route="dashboard" title="Overview" icon="squares-four" />
            <x-menu-item route="dashboard.proposals" title="Proposals" icon="file-text" />
            <x-menu-item route="dashboard.clients" title="Clients" icon="users-three" />
            <x-menu-item route="dashboard.features" title="Features" icon="stack" />
            <x-menu-item route="dashboard.packages" title="Packages" icon="cube" />
            <x-menu-item route="dashboard.users" title="Team" icon="user-circle" />
            <x-menu-item route="dashboard.settings" title="Settings" icon="gear-six" />
        </nav>

        <div class="mt-auto flex flex-col items-center gap-2">
            <div class="mx-3 my-1 h-px w-10 bg-white/5"></div>
            <a href="{{ route('dashboard.profile') }}" title="{{ $user->full_name }}" class="block">
                <img src="{{ $user->gravatar }}" alt="{{ $user->full_name }}"
                     class="size-8 rounded-full ring-1 ring-white/10">
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Sign out"
                        class="flex size-9 items-center justify-center rounded-[10px] text-slate-faint transition-colors hover:bg-white/5 hover:text-sage">
                    <x-phosphor-sign-out class="size-[18px]" />
                </button>
            </form>
        </div>
    </aside>

    {{-- ============================================================
         MAIN
         ============================================================ --}}
    <main class="flex min-w-0 flex-col">

        {{-- TOP BAR --}}
        <header class="sticky top-0 z-10 flex h-[60px] items-center gap-6 border-b border-rule bg-paper px-8">
            <div class="flex items-center gap-2 text-[13px] text-slate">
                <span>{{ $user?->tenant?->name ?? 'Workspace' }}</span>
                <span class="text-slate-faint">/</span>
                <strong class="font-medium text-ink">{{ $crumb }}</strong>
            </div>

            <button type="button"
                    onclick="Livewire.dispatch('open-palette')"
                    class="ml-auto flex w-[360px] items-center gap-3 rounded-[10px] border border-rule bg-paper-2 px-3.5 py-1.5 text-[13px] text-slate transition-colors hover:border-slate-faint hover:bg-white">
                <x-phosphor-magnifying-glass class="size-[15px] opacity-60" />
                <span>Search anything…</span>
                <span class="ml-auto flex gap-0.5">
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">⌘</kbd>
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">K</kbd>
                </span>
            </button>

            <button type="button" class="flex size-9 items-center justify-center rounded-lg text-slate transition-colors hover:bg-paper-2 hover:text-ink" title="Notifications">
                <x-phosphor-bell class="size-[18px]" />
            </button>
        </header>

        <div class="px-10 pb-20 pt-9">
            {{ $slot }}
        </div>
    </main>
</div>

@livewire('admin.shared.command-palette')
@livewire('wire-elements-modal')
</body>
</html>
