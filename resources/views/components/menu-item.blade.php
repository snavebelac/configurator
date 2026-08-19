@props([
    'route',
    'title',
    'icon',
    'collapsed' => true,
])
@php $current = request()->routeIs($route); @endphp
<a
    href="{{ route($route) }}"
    @class([
        'group relative flex items-center rounded-[10px] transition-colors duration-150',
        'size-11 justify-center' => $collapsed,
        'h-11 w-full gap-3 px-3' => ! $collapsed,
        'text-fox' => $current,
        'text-slate-faint hover:bg-white/5 hover:text-sage' => ! $current,
    ])
    @if ($current) aria-current="page" @endif
    aria-label="{{ $title }}"
>
    @if ($current)
        <span class="absolute -left-3.5 top-2.5 bottom-2.5 w-0.5 rounded-r-sm bg-fox"></span>
    @endif

    <x-dynamic-component :component="'phosphor-'.$icon" class="size-[19px] shrink-0" />

    @if ($collapsed)
        {{-- Tooltip only earns its place when there is no visible label. --}}
        <span class="pointer-events-none absolute left-[calc(100%+16px)] top-1/2 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink-3 px-2.5 py-1 text-xs text-sage opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 z-50">
            {{ $title }}
        </span>
    @else
        <span class="truncate text-[13.5px] font-medium">{{ $title }}</span>
    @endif
</a>
