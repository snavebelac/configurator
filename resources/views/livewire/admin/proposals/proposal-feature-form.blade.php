<x-tables.table-row>
    <x-tables.table-cell>
        <input type="text" wire:model.lazy="name" class="border rounded-md border-gray-300 px-2 py-1 w-[350px]" />
    </x-tables.table-cell>
    <x-tables.table-cell>
        <input type="number" min="1" step="1" wire:model.lazy="quantity" class="border rounded-md border-gray-300 px-2 py-1 w-[100px]" />
    </x-tables.table-cell>
    <x-tables.table-cell>&pound;
        <input type="number" min="1" step="0.01" wire:model.lazy="price" class="border rounded-md border-gray-300 px-2 py-1 w-[100px]" />
    </x-tables.table-cell>
    <x-tables.table-cell>
        @if ($finalFeature->optional)
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor"
                class="size-6 text-primary-600">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        @endif
    </x-tables.table-cell>
    <x-tables.table-cell>
        <button wire:click="removeFinalFeature({{ $finalFeature->id }})"
            class="button button-warning">X<span
            class="sr-only">, Remove {{ $finalFeature->name }}</span>
        </button>
    </x-tables.table-cell>
</x-tables.table-row>
