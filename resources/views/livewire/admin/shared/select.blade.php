<div
    class="relative flex items-center gap-2">
    <label id="listbox-label" class="block text-sm/6 font-medium text-gray-900">{{ $label }}</label>
    <div class="relative min-w-48">
        <button
            wire:click="toggle()"
            wire:keyup.escape="close()"
            wire:click.outside="close()"
            type="button"
            class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
            <span class="col-start-1 row-start-1 truncate pr-6" x-text="$wire.selectedLabel"></span>
            <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
            </svg>
        </button>
        <ul
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base ring-1 shadow-lg ring-black/5 focus:outline-hidden sm:text-sm"
            x-show="$wire.open"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            tabindex="-1"
            role="listbox"
            aria-labelledby="listbox-label"
            aria-activedescendant="listbox-option-0">
            <li
                wire:click="select()"
                x-show="$wire.selected"
                class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" id="listbox-option-0" role="option">
                <span class="block truncate font-normal">Clear selection</span>
            </li>
            @foreach ($items as $item)
            <li
                wire:key="{{ $item->id }}"
                wire:click="select({{ $item->id }})"
                class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none"
                id="listbox-option-{{$item->id}}"
                role="option">
                <span class="block truncate font-normal" x-bind:class="{{ $item->id }} == $wire.selected ? 'font-semibold' : 'font-normal'">{{ $item->name }}</span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600"
                    x-show="{{ $item->id }} == $wire.selected">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                </span>
            </li>
            @endforeach
        </ul>
    </div>
</div>
