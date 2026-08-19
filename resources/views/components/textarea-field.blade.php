@props([
    'label',
    'name',
    'model' => null,
    'placeholder' => null,
    'rows' => 4,
    'hint' => null,
    'required' => false,
])
@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $inputClasses = 'block w-full rounded-lg border bg-paper-2 px-3 py-2 text-[14px] leading-[1.6] text-ink focus:bg-white focus:outline-none transition-colors '
        .($hasError ? 'border-status-rejected-dot/50 focus:border-status-rejected-fg' : 'border-rule focus:border-ink');
@endphp
<div {{ $attributes->whereDoesntStartWith(['id', 'class']) }}>
    <label for="{{ $id }}" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">
        {{ $label }}
    </label>
    <textarea id="{{ $id }}"
              name="{{ $name }}"
              rows="{{ $rows }}"
              @if ($model) wire:model="{{ $model }}" @else wire:model="{{ $name }}" @endif
              @if ($placeholder) placeholder="{{ $placeholder }}" @endif
              @if ($required) required @endif
              class="{{ $inputClasses }}"></textarea>
    @if ($hint && ! $hasError)
        <p class="mt-1.5 text-[12px] text-slate">{{ $hint }}</p>
    @endif
    @error ($name)
        <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
    @enderror
</div>
