@php
    $crumbs = [
        'dashboard'                  => 'Overview',
        'dashboard.proposals'        => 'Proposals',
        'dashboard.proposal.create'  => 'Proposals · New',
        'dashboard.proposal.edit'    => 'Proposals · Edit',
        'dashboard.clients'          => 'Clients',
        'dashboard.features'         => 'Features',
        'dashboard.packages'         => 'Packages',
        'dashboard.package.create'   => 'Packages · New',
        'dashboard.package.edit'     => 'Packages · Edit',
        'dashboard.users'            => 'Team',
        'dashboard.profile'          => 'Profile',
        'dashboard.settings'         => 'Settings',
        'dashboard.terms'            => 'Terms',
        'dashboard.terms.edit'       => 'Terms · Edit',
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

<div class="flex min-h-screen">

    {{-- ============================================================
         NAV RAIL — collapsible, width persisted per user
         ============================================================ --}}
    <livewire:admin.shared.nav-rail />

    {{-- ============================================================
         MAIN
         ============================================================ --}}
    <main class="flex min-w-0 flex-1 flex-col">

        {{-- TOP BAR --}}
        <header class="sticky top-0 z-30 flex h-[60px] items-center gap-4 border-b border-rule bg-paper px-5 lg:gap-6 lg:px-8">
            {{-- Drawer trigger — below lg the rail is off-canvas --}}
            <button type="button"
                    x-on:click="$store.nav.toggle()"
                    class="-ml-1 flex size-9 shrink-0 items-center justify-center rounded-lg text-slate transition-colors hover:bg-paper-2 hover:text-ink lg:hidden"
                    aria-label="Open navigation"
                    x-bind:aria-expanded="$store.nav.open.toString()">
                <x-phosphor-list class="size-[18px]" />
            </button>

            <div class="flex min-w-0 items-center gap-2 text-[13px] text-slate">
                <span class="hidden truncate sm:inline">{{ $user?->tenant?->name ?? 'Workspace' }}</span>
                <span class="hidden text-slate-faint sm:inline">/</span>
                <strong class="truncate font-medium text-ink">{{ $crumb }}</strong>
            </div>

            <button type="button"
                    class="ml-auto flex shrink-0 items-center gap-3 rounded-[10px] border border-rule bg-paper-2 px-3.5 py-1.5 text-[13px] text-slate transition-colors hover:border-slate-faint hover:bg-white lg:w-[360px]">
                <x-phosphor-magnifying-glass class="size-[15px] opacity-60" />
                <span class="hidden lg:inline">Search anything…</span>
                <span class="ml-auto hidden gap-0.5 lg:flex">
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">⌘</kbd>
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">K</kbd>
                </span>
            </button>
        </header>

        <div class="px-5 pb-20 pt-6 lg:px-10 lg:pt-9">
            {{ $slot }}
        </div>
    </main>
</div>

@livewire('wire-elements-modal')
</body>
</html>
