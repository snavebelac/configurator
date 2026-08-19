@props([
    'route',
    'title',
    'icon',
    'collapsed' => true,
])
@php $current = request()->routeIs($route); @endphp
{{--
    The collapsed preference is a desktop concern. Below `lg` the rail is an
    off-canvas drawer you opened deliberately, so it always shows labels — the
    collapsed styling is therefore applied only from `lg` up.
--}}
<a
    href="{{ route($route) }}"
    x-on:click="$store.nav.close()"
    @class([
        'group relative flex h-11 w-full items-center gap-3 rounded-[10px] px-3 transition-colors duration-150',
        'lg:size-11 lg:justify-center lg:gap-0 lg:px-0' => $collapsed,
        'text-fox' => $current,
        'text-slate-faint hover:bg-white/5 hover:text-sage' => ! $current,
    ])
    @if ($current) aria-current="page" @endif
>
    @if ($current)
        <span class="absolute -left-3 top-2.5 bottom-2.5 w-0.5 rounded-r-sm bg-fox lg:-left-3.5"></span>
    @endif

    <x-dynamic-component :component="'phosphor-'.$icon" class="size-[19px] shrink-0" />

    <span @class(['truncate text-[13.5px] font-medium', 'lg:hidden' => $collapsed])>{{ $title }}</span>

    @if ($collapsed)
        {{-- Only earns its place where the label is hidden. --}}
        <span class="pointer-events-none absolute left-[calc(100%+16px)] top-1/2 hidden -translate-y-1/2 whitespace-nowrap rounded-md bg-ink-3 px-2.5 py-1 text-xs text-sage opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 lg:block z-50">
            {{ $title }}
        </span>
    @endif
</a>
