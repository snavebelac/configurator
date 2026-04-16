@props([
    'label',
    'name',
    'options' => [],
    'model' => null,
    'placeholder' => 'Please select…',
    'required' => false,
])
@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $selectClasses = 'block w-full rounded-lg border bg-paper-2 px-3 py-2 text-[14px] text-ink focus:bg-white focus:outline-none transition-colors '
        .($hasError ? 'border-status-rejected-dot/50 focus:border-status-rejected-fg' : 'border-rule focus:border-ink');
@endphp
<div {{ $attributes->whereDoesntStartWith(['id', 'class']) }}>
    <label for="{{ $id }}" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.08em] text-slate">
        {{ $label }}
    </label>
    <select id="{{ $id }}"
            name="{{ $name }}"
            @if ($model) wire:model="{{ $model }}" @else wire:model="{{ $name }}" @endif
            @if ($required) required @endif
            class="{{ $selectClasses }}">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
    @error ($name)
        <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
    @enderror
</div>
