@props([
    'href' => null,
    'click' => null,
])
{{--
    The clickable title of a table row.

    House rule: if a row has a single primary "open" or "edit" action, its
    title performs that same action — nobody should have to track across to
    the button at the far right to open the thing they just read.

    Pass `href` for a link, or `click` for a Livewire expression (modals).
    With neither it degrades to plain text, so it is safe on rows that have
    no primary action.
--}}
@php
    $classes = 'text-left font-medium text-ink underline-offset-[3px] transition-colors hover:text-ink hover:underline decoration-slate-faint';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@elseif ($click)
    <button type="button" wire:click="{{ $click }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@else
    <span {{ $attributes->class('font-medium text-ink') }}>{{ $slot }}</span>
@endif
