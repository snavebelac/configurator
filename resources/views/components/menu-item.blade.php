@props([
    'route',
    'title',
])
@php $current = request()->routeIs($route); @endphp
<a
    href="{{ route($route) }}"
    class="group relative flex size-11 items-center justify-center rounded-[10px] transition-colors duration-150 {{ $current ? 'text-fox' : 'text-slate-faint hover:bg-white/5 hover:text-sage' }}"
    aria-label="{{ $title }}"
>
    @if ($current)
        <span class="absolute -left-3.5 top-2.5 bottom-2.5 w-0.5 rounded-r-sm bg-fox"></span>
    @endif
    <svg class="size-[19px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        {{ $slot }}
    </svg>
    <span class="pointer-events-none absolute left-[calc(100%+16px)] top-1/2 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink-3 px-2.5 py-1 text-xs text-sage opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 z-50">
        {{ $title }}
    </span>
</a>
