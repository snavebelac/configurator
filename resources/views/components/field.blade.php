@props([
    'label',
    'name',
    'type' => 'text',
    'model' => null,
    'modelLive' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'step' => null,
    'min' => null,
    'prefix' => null,
    'hint' => null,
    'required' => false,
])
@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $inputClasses = 'block w-full rounded-lg border bg-paper-2 px-3 py-2 text-[14px] text-ink focus:bg-white focus:outline-none transition-colors '
        .($hasError ? 'border-status-rejected-dot/50 focus:border-status-rejected-fg' : 'border-rule focus:border-ink');
@endphp
<div {{ $attributes->whereDoesntStartWith(['id', 'class']) }}>
    <label for="{{ $id }}" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">
        {{ $label }}
    </label>
    @if ($prefix)
        <div class="relative">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-soft text-[14px]">{{ $prefix }}</span>
            <input id="{{ $id }}"
                   name="{{ $name }}"
                   type="{{ $type }}"
                   @if ($modelLive) wire:model.live="{{ $modelLive }}" @elseif ($model) wire:model="{{ $model }}" @else wire:model="{{ $name }}" @endif
                   @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                   @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                   @if ($step) step="{{ $step }}" @endif
                   @if ($min !== null) min="{{ $min }}" @endif
                   @if ($required) required @endif
                   class="{{ $inputClasses }} pl-7">
        </div>
    @else
        <input id="{{ $id }}"
               name="{{ $name }}"
               type="{{ $type }}"
               @if ($modelLive) wire:model.live="{{ $modelLive }}" @elseif ($model) wire:model="{{ $model }}" @else wire:model="{{ $name }}" @endif
               @if ($placeholder) placeholder="{{ $placeholder }}" @endif
               @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               @if ($step) step="{{ $step }}" @endif
               @if ($min !== null) min="{{ $min }}" @endif
               @if ($required) required @endif
               class="{{ $inputClasses }}">
    @endif
    @if ($hint && ! $hasError)
        <p class="mt-1.5 text-[12px] text-slate">{{ $hint }}</p>
    @endif
    @error ($name)
        <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
    @enderror
</div>
