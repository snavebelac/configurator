@props([
    'status' => 'draft',
    'label' => null,
])
@php
    $tones = [
        'draft'     => ['bg' => 'bg-status-draft-bg',     'fg' => 'text-status-draft-fg',     'dot' => 'bg-status-draft-dot'],
        'delivered' => ['bg' => 'bg-status-delivered-bg', 'fg' => 'text-status-delivered-fg', 'dot' => 'bg-status-delivered-dot'],
        'accepted'  => ['bg' => 'bg-status-accepted-bg',  'fg' => 'text-status-accepted-fg',  'dot' => 'bg-status-accepted-dot'],
        'rejected'  => ['bg' => 'bg-status-rejected-bg',  'fg' => 'text-status-rejected-fg',  'dot' => 'bg-status-rejected-dot'],
        'archived'  => ['bg' => 'bg-status-archived-bg',  'fg' => 'text-status-archived-fg',  'dot' => 'bg-status-archived-dot'],
    ];
    $tone = $tones[$status] ?? $tones['draft'];
    $text = $label ?? ucfirst($status);
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5', $tone['bg'], $tone['fg']]) }}>
    <span class="size-1.5 rounded-full {{ $tone['dot'] }}"></span>
    {{ $text }}
</span>
