@php
    $rowLabel = 'text-[11px] font-medium uppercase tracking-[0.08em] text-slate';
    $row = 'flex flex-wrap items-baseline justify-between gap-3 border-b border-rule-soft px-8 py-4 last:border-b-0';
@endphp
<x-modal
    title="Ready to send?"
    subtitle="Once delivered, the share link works and your client can accept or decline.">

    <div class="flex flex-col">

        {{-- Client --}}
        <div class="{{ $row }}">
            <span class="{{ $rowLabel }}">Going to</span>
            <span class="text-right">
                <span class="block text-[14px] font-medium text-ink">
                    {{ $proposal->client?->name ?? 'No client' }}
                </span>
                @if ($proposal->client?->email)
                    <span class="block text-[12.5px] text-slate">{{ $proposal->client->email }}</span>
                @endif
            </span>
        </div>

        {{-- Money. The one-off figure and the recurring ones stay apart:
             summing a build fee and a monthly fee would misstate the deal. --}}
        <div class="{{ $row }}">
            <span class="{{ $rowLabel }}">Total</span>
            <span class="text-right">
                <span class="block font-mono text-[16px] text-ink tnum">
                    {{ $currency }}{{ number_format($pricing['subtotal'] / 100, 2) }}
                </span>
                <span class="block text-[12px] text-slate">
                    one-off, with all {{ $optionalCount }} optional
                    {{ Str::plural('line', $optionalCount) }} included
                </span>
            </span>
        </div>

        @foreach ($recurringPeriods as $period)
            @if (($pricing['recurring'][$period->value] ?? 0) > 0)
                <div class="{{ $row }}" wire:key="recurring-{{ $period->value }}">
                    <span class="{{ $rowLabel }}">{{ $period->totalLabel() }}</span>
                    <span class="font-mono text-[14px] text-ink tnum">
                        {{ $currency }}{{ number_format($pricing['recurring'][$period->value] / 100, 2) }}
                    </span>
                </div>
            @endif
        @endforeach

        {{-- Terms --}}
        <div class="{{ $row }}">
            <span class="{{ $rowLabel }}">Terms</span>
            @if ($proposal->termsVersion)
                <span class="text-right">
                    <span class="block text-[14px] font-medium text-ink">
                        {{ $proposal->termsVersion->terms->name }}
                    </span>
                    <span class="block text-[12.5px] text-slate">
                        <span class="font-mono tnum">{{ $proposal->termsVersion->label() }}</span>
                        · published {{ $proposal->termsVersion->published_at->format('j M Y') }}
                    </span>
                </span>
            @else
                <span class="flex items-center gap-1.5 text-[13.5px] font-medium text-status-rejected-fg">
                    <x-phosphor-warning class="size-4" />
                    Nothing attached
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-8 py-4">
        <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Go back and change</x-btn>
        <x-btn variant="accent" type="button" wire:click="confirm">
            <span wire:loading.remove wire:target="confirm">Mark as delivered</span>
            <span wire:loading wire:target="confirm">Delivering…</span>
        </x-btn>
    </div>
</x-modal>
