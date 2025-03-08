@props(['route', 'title', 'icon' => 'hi'])
<?php $current = request()->routeIs($route);  ?>
<li>
    <a
        href="{{ route($route) }}"
        class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ $current ? ' bg-primary-800 text-white' : ' text-primary-100 hover:bg-primary-600 hover:text-white' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="{{$icon == 'fa' ? 'currentColor' : 'none'}}" viewBox="0 0 {{$icon == 'fa' ? '512 512' : '24 24'}}" stroke-width="1.5" stroke="currentColor"
            class="shrink-0 {{ $icon == 'fa' ? 'size-5 mt-0.5' : 'size-6' }} {{ $current ? 'text-white' : 'text-primary-100 group-hover:text-white' }}">
            {{ $slot }}
        </svg>
        {{ $title }}
    </a>
</li>
