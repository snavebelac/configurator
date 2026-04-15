@php
    $crumbs = [
        'dashboard'                  => 'Overview',
        'dashboard.proposals'        => 'Proposals',
        'dashboard.proposal.create'  => 'Proposals · New',
        'dashboard.proposal.edit'    => 'Proposals · Edit',
        'dashboard.proposal.preview' => 'Proposals · Preview',
        'dashboard.clients'          => 'Clients',
        'dashboard.features'         => 'Features',
        'dashboard.users'            => 'Team',
        'dashboard.profile'          => 'Profile',
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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..600&family=Geist:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

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
            <x-menu-item route="dashboard" title="Overview">
                <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                <rect x="13" y="3" width="8" height="5" rx="1.5"/>
                <rect x="13" y="10" width="8" height="11" rx="1.5"/>
                <rect x="3" y="13" width="8" height="8" rx="1.5"/>
            </x-menu-item>
            <x-menu-item route="dashboard.proposals" title="Proposals">
                <path d="M5 3h9l5 5v13H5z"/>
                <path d="M14 3v5h5"/>
                <path d="M8 13h8M8 17h5"/>
            </x-menu-item>
            <x-menu-item route="dashboard.clients" title="Clients">
                <circle cx="12" cy="7" r="3"/>
                <circle cx="6" cy="11" r="2"/>
                <circle cx="18" cy="11" r="2"/>
                <path d="M3 19c0-2.5 4-4.5 9-4.5s9 2 9 4.5"/>
            </x-menu-item>
            <x-menu-item route="dashboard.features" title="Features">
                <path d="M5 7l7-4 7 4-7 4z"/>
                <path d="M5 12l7 4 7-4"/>
                <path d="M5 17l7 4 7-4"/>
            </x-menu-item>
            <x-menu-item route="dashboard.users" title="Team">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
            </x-menu-item>
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
                    <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15"/>
                        <path d="m4 12 3-3m-3 3 3 3M4 12h11"/>
                    </svg>
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
                    class="ml-auto flex w-[360px] items-center gap-3 rounded-[10px] border border-rule bg-paper-2 px-3.5 py-1.5 text-[13px] text-slate transition-colors hover:border-slate-faint hover:bg-white">
                <svg class="size-[15px] opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3-3"/>
                </svg>
                <span>Search anything…</span>
                <span class="ml-auto flex gap-0.5">
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">⌘</kbd>
                    <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">K</kbd>
                </span>
            </button>

            <button type="button" class="flex size-9 items-center justify-center rounded-lg text-slate transition-colors hover:bg-paper-2 hover:text-ink" title="Notifications">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 10a6 6 0 0 1 12 0c0 4 2 6 2 6H4s2-2 2-6z"/>
                    <path d="M10 20a2 2 0 0 0 4 0"/>
                </svg>
            </button>
        </header>

        <div class="px-10 pb-20 pt-9">
            {{ $slot }}
        </div>
    </main>
</div>

@livewire('wire-elements-modal')
</body>
</html>
