<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurator</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-paper text-ink antialiased">

<div class="grid min-h-screen place-items-center px-6 py-12">
    <div class="w-full max-w-[480px] text-center">
        <a href="{{ route('home') }}" class="mx-auto flex size-12 items-center justify-center rounded-xl bg-ink text-fox" title="Configurator">
            <x-logo class="size-[22px]" />
        </a>

        <p class="mt-8 text-[11px] font-medium uppercase tracking-[0.14em] text-slate">Configurator</p>
        <h1 class="mt-3 font-display text-[34px] italic leading-[1.1] tracking-[-0.04em] text-ink">
            Proposals worth talking through.
        </h1>
        <p class="mx-auto mt-3 max-w-[38ch] text-[14px] leading-[1.55] text-slate">
            Build a price configurator once, then shape the proposal with your client in real time
            instead of emailing revisions back and forth.
        </p>

        <div class="mt-8 flex items-center justify-center gap-3">
            <x-btn variant="accent" :href="route('signup')">Create a workspace</x-btn>
            <x-btn variant="ghost" :href="route('login')">Sign in</x-btn>
        </div>
    </div>
</div>

</body>
</html>
