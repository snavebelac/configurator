@props([
    'label',
    'name',
    'description' => null,
    'model' => null,
    'modelLive' => null,
])
@php
    $id = $attributes->get('id', $name);
@endphp
<div {{ $attributes->whereDoesntStartWith(['id', 'class']) }}>
    <label for="{{ $id }}" class="flex cursor-pointer items-start gap-3">
        <input type="checkbox"
               id="{{ $id }}"
               name="{{ $name }}"
               @if ($modelLive) wire:model.live="{{ $modelLive }}" @elseif ($model) wire:model="{{ $model }}" @else wire:model="{{ $name }}" @endif
               class="mt-0.5 size-4 rounded border-rule bg-paper-2 text-ink focus:ring-2 focus:ring-ink focus:ring-offset-0 accent-ink">
        <span class="flex flex-col gap-0.5 text-[14px] text-ink">
            <span class="font-medium">{{ $label }}</span>
            @if ($description)
                <span class="text-[12.5px] text-slate">{{ $description }}</span>
            @endif
        </span>
    </label>
    @error ($name)
        <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
    @enderror
</div>
