@php
    $noise = "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.82' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0.141  0 0 0 0 0.141  0 0 0 0 0.137  0 0 0 0.45 0'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>\")";
@endphp
<div class="relative min-h-screen">
    <div class="pointer-events-none fixed inset-0 opacity-[0.06] mix-blend-multiply" aria-hidden="true"
         style="background-image: {{ $noise }}; background-size: 160px 160px;"></div>

    <div class="relative mx-auto max-w-[1220px] px-10 py-14">

        {{-- ====== Masthead ====== --}}
        <header class="mb-16 border-b border-ink/10 pb-12">
            <div class="flex items-center justify-between">
                @if ($logo)
                    {{-- The tenant's own branding takes the masthead when set. --}}
                    <img src="{{ Storage::disk('public')->url($logo) }}"
                         alt="{{ $companyName ?? 'Logo' }}"
                         class="max-h-10 max-w-[200px] object-contain object-left">
                @else
                    <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate">
                        <span class="inline-block size-1.5 -translate-y-[1px] rounded-full bg-fox-deep align-middle"></span>
                        <span class="ml-2 align-middle">{{ $companyName ? $companyName.' proposal' : 'A Configurator proposal' }}</span>
                    </p>
                @endif
                <p class="font-mono text-[11px] tracking-wider text-slate-soft tnum">№ {{ str_pad((string) $proposal->id, 4, '0', STR_PAD_LEFT) }}</p>
            </div>

            <h1 class="mt-7 font-display text-[clamp(44px,5.4vw,64px)] italic leading-[0.95] tracking-[-0.042em] text-ink">
                {{ $proposal->name ?: 'Untitled proposal' }}
            </h1>

            <div class="mt-8 flex flex-wrap items-baseline gap-x-12 gap-y-3 text-[13px] leading-[1.5]">
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-[0.2em] text-slate-soft">For</div>
                    <div class="mt-1 font-display text-[17px] text-ink">{{ $proposal->client?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-[0.2em] text-slate-soft">Prepared by</div>
                    <div class="mt-1 font-display text-[17px] text-ink">{{ $proposal->user?->full_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-[10px] font-medium uppercase tracking-[0.2em] text-slate-soft">Dated</div>
                    <div class="mt-1 font-display text-[17px] text-ink">{{ $proposal->created_at->format('j F Y') }}</div>
                </div>
            </div>
        </header>

        {{-- ====== Body ====== --}}
        <div x-data="proposalConfigurator()" class="grid gap-14 lg:grid-cols-[1fr_360px]">

            <main class="min-w-0">
                @if ($proposal->description)
                    <p class="mb-16 max-w-[58ch] font-display text-[20px] italic leading-[1.55] text-ink">
                        {{ $proposal->description }}
                    </p>
                @endif

                @forelse ($groups as $group)
                    {{-- The root feature's own row carries its name, so there is
                         deliberately no section heading above it — that only
                         repeated the title, and its "Included" eyebrow repeated
                         the marker on the row itself. --}}
                    <section class="mb-12 border-t border-rule pt-1" wire:key="group-{{ $group['root']->id }}">
                        <div class="flex flex-col divide-y divide-rule-soft">
                            @include('livewire.public.partials.proposal-row', [
                                'feature' => $group['root'],
                                'isChild' => false,
                                'childCount' => $group['children']->count(),
                            ])
                            @foreach ($group['children'] as $child)
                                @include('livewire.public.partials.proposal-row', [
                                    'feature' => $child,
                                    'isChild' => true,
                                    'childCount' => 0,
                                ])
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-2xl border border-dashed border-rule bg-paper-2 px-10 py-14 text-center">
                        <p class="font-display text-[22px] italic text-ink">Nothing to show yet.</p>
                        <p class="mt-3 text-[14px] text-slate">This proposal doesn't contain any features.</p>
                    </div>
                @endforelse

                @if ($percentageFeatures->isNotEmpty())
                    {{-- Proportional lines sit after the itemised work: they
                         are a share of everything above, so they only make
                         sense once the reader has seen it. --}}
                    <section class="mt-16 border-t border-ink/10 pt-10">
                        <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate-soft">
                            Applied to the total
                        </p>

                        <div class="mt-6 flex flex-col divide-y divide-rule-soft">
                            @foreach ($percentageFeatures as $percentageFeature)
                                @php $isOptional = (bool) $percentageFeature->optional; @endphp
                                <div wire:key="pct-{{ $percentageFeature->id }}"
                                     class="grid gap-6 py-5 transition-opacity duration-200 sm:grid-cols-[1fr_auto]"
                                     @if ($isOptional)
                                        x-bind:class="isOn({{ $percentageFeature->id }}) ? 'opacity-100' : 'opacity-45'"
                                     @endif>

                                    <div class="min-w-0">
                                        <div class="flex items-baseline gap-3">
                                            <h3 class="font-display text-[24px] leading-[1.25] text-ink">
                                                {{ $percentageFeature->name }}
                                            </h3>
                                            <span class="whitespace-nowrap font-mono text-[13px] text-slate-soft tnum">
                                                {{ $percentageFeature->percentageForHumans() }}
                                            </span>
                                        </div>
                                        @if ($percentageFeature->description)
                                            <p class="mt-2 max-w-[52ch] text-[14px] leading-[1.55] text-slate">
                                                {{ $percentageFeature->description }}
                                            </p>
                                        @endif
                                        <p class="mt-2 text-[11.5px] uppercase tracking-[0.14em] text-slate-soft">
                                            {{ $percentageFeature->percentageForHumans() }} of the selected work
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-end gap-3">
                                        <div class="flex items-baseline gap-1 font-mono leading-none tnum">
                                            <span class="text-[14px] text-slate-soft">{{ $currency->toSymbol() }}</span>
                                            <span class="text-[22px] text-ink"
                                                  x-text="formatWhole(percentageAmount({{ $percentageFeature->id }}))"></span>
                                        </div>

                                        @if ($isOptional)
                                            <button type="button"
                                                    x-on:click="toggle({{ $percentageFeature->id }})"
                                                    x-bind:disabled="locked"
                                                    x-bind:class="[
                                                        isOn({{ $percentageFeature->id }}) ? 'bg-fox border-fox-deep' : 'bg-white border-rule',
                                                        locked ? 'cursor-default opacity-70' : '',
                                                    ]"
                                                    class="relative h-7 w-[52px] rounded-full border transition-colors duration-200"
                                                    x-bind:aria-pressed="isOn({{ $percentageFeature->id }})"
                                                    aria-label="Toggle {{ $percentageFeature->name }}">
                                                <span class="absolute top-[2px] left-[2px] size-[22px] rounded-full bg-ink shadow-sm transition-transform duration-200"
                                                      x-bind:style="isOn({{ $percentageFeature->id }}) ? 'transform: translateX(24px)' : 'transform: translateX(0)'"></span>
                                            </button>
                                            <p class="text-[10.5px] font-medium uppercase tracking-[0.2em]"
                                               x-bind:class="isOn({{ $percentageFeature->id }}) ? 'text-ink' : 'text-slate-soft'">
                                                <span x-text="isOn({{ $percentageFeature->id }}) ? 'Included' : 'Excluded'"></span>
                                                <span class="text-slate-soft"> · Optional</span>
                                            </p>
                                        @else
                                            <div class="flex items-center gap-1.5 text-[10.5px] font-medium uppercase tracking-[0.2em] text-slate">
                                                <x-phosphor-lock-simple class="size-3 text-slate-soft" />
                                                Included
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($recurringFeatures->isNotEmpty())
                    {{-- Ongoing costs are a separate commitment from the
                         one-off work, so they get their own section and their
                         own totals — never folded into the headline figure. --}}
                    <section class="mt-16 border-t border-ink/10 pt-10">
                        <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate-soft">
                            Ongoing costs
                        </p>
                        <p class="mt-3 max-w-[52ch] text-[13.5px] leading-[1.55] text-slate">
                            Charged for as long as you keep them, separately from the one-off cost above.
                        </p>

                        <div class="mt-6 flex flex-col divide-y divide-rule-soft">
                            @foreach ($recurringFeatures as $recurringFeature)
                                @php $isOptional = (bool) $recurringFeature->optional; @endphp
                                <div wire:key="rec-{{ $recurringFeature->id }}"
                                     class="grid gap-6 py-5 transition-opacity duration-200 sm:grid-cols-[1fr_auto]"
                                     @if ($isOptional)
                                        x-bind:class="isOn({{ $recurringFeature->id }}) ? 'opacity-100' : 'opacity-45'"
                                     @endif>

                                    <div class="min-w-0">
                                        <h3 class="font-display text-[24px] leading-[1.25] text-ink">
                                            {{ $recurringFeature->name }}
                                        </h3>
                                        @if ($recurringFeature->description)
                                            <p class="mt-2 max-w-[52ch] text-[14px] leading-[1.55] text-slate">
                                                {{ $recurringFeature->description }}
                                            </p>
                                        @endif
                                        <p class="mt-2 text-[11.5px] uppercase tracking-[0.14em] text-slate-soft">
                                            Billed {{ strtolower($recurringFeature->billing_period?->label() ?? '') }}
                                            @if ($recurringFeature->quantity > 1)
                                                · <span class="font-mono text-ink tnum">× {{ $recurringFeature->quantity }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-end gap-3">
                                        <div class="flex items-baseline gap-1 font-mono leading-none tnum">
                                            <span class="text-[14px] text-slate-soft">{{ $currency->toSymbol() }}</span>
                                            <span class="text-[22px] text-ink">{{ number_format($recurringFeature->price * $recurringFeature->quantity, 0) }}</span>
                                            <span class="text-[13px] text-slate-soft">{{ $recurringFeature->billingSuffix() }}</span>
                                        </div>

                                        @if ($isOptional)
                                            <button type="button"
                                                    x-on:click="toggle({{ $recurringFeature->id }})"
                                                    x-bind:disabled="locked"
                                                    x-bind:class="[
                                                        isOn({{ $recurringFeature->id }}) ? 'bg-fox border-fox-deep' : 'bg-white border-rule',
                                                        locked ? 'cursor-default opacity-70' : '',
                                                    ]"
                                                    class="relative h-7 w-[52px] rounded-full border transition-colors duration-200"
                                                    x-bind:aria-pressed="isOn({{ $recurringFeature->id }})"
                                                    aria-label="Toggle {{ $recurringFeature->name }}">
                                                <span class="absolute top-[2px] left-[2px] size-[22px] rounded-full bg-ink shadow-sm transition-transform duration-200"
                                                      x-bind:style="isOn({{ $recurringFeature->id }}) ? 'transform: translateX(24px)' : 'transform: translateX(0)'"></span>
                                            </button>
                                            <p class="text-[10.5px] font-medium uppercase tracking-[0.2em]"
                                               x-bind:class="isOn({{ $recurringFeature->id }}) ? 'text-ink' : 'text-slate-soft'">
                                                <span x-text="isOn({{ $recurringFeature->id }}) ? 'Included' : 'Excluded'"></span>
                                                <span class="text-slate-soft"> · Optional</span>
                                            </p>
                                        @else
                                            <div class="flex items-center gap-1.5 text-[10.5px] font-medium uppercase tracking-[0.2em] text-slate">
                                                <x-phosphor-lock-simple class="size-3 text-slate-soft" />
                                                Included
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($proposal->additional)
                    <section class="mt-20 border-t border-ink/10 pt-10">
                        <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate-soft">Notes</p>
                        <p class="mt-4 max-w-[58ch] font-display text-[17px] italic leading-[1.7] text-ink">
                            {{ $proposal->additional }}
                        </p>
                    </section>
                @endif

                @if ($termsVersion?->body)
                    {{-- The exact revision this proposal was sent under, frozen
                         at delivery. Collapsed by default: it's long, and it
                         shouldn't push the pricing off the page. --}}
                    <section class="mt-20 border-t border-ink/10 pt-10" x-data="{ open: false }">
                        <button type="button"
                                x-on:click="open = ! open"
                                class="flex w-full items-baseline justify-between gap-6 text-left">
                            <span>
                                <span class="block text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate-soft">
                                    Terms &amp; conditions
                                </span>
                                <span class="mt-2 block font-display text-[20px] text-ink">
                                    {{ $termsVersion->terms->name }}
                                </span>
                            </span>
                            <span class="flex shrink-0 items-center gap-2 text-[11.5px] text-slate-soft">
                                <span class="font-mono tnum">{{ $termsVersion->label() }}</span>
                                <x-phosphor-caret-down class="size-3.5 transition-transform duration-200"
                                                       x-bind:class="open && 'rotate-180'" />
                            </span>
                        </button>

                        <div x-show="open" x-collapse x-cloak class="mt-6 max-w-[68ch]">
                            <div class="prose-terms">{!! $termsVersion->body !!}</div>
                        </div>

                        <p class="mt-4 text-[11px] leading-[1.5] text-slate-soft" x-show="! open">
                            Accepting this proposal accepts these terms.
                            <button type="button" x-on:click="open = true" class="underline underline-offset-2 hover:text-slate">Read them</button>.
                        </p>
                    </section>
                @endif
            </main>

            {{-- ====== Summary rail ====== --}}
            <aside class="hidden lg:block">
                <div class="sticky top-10 overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-[0_1px_0_rgba(0,0,0,0.02),0_24px_56px_-28px_rgba(36,36,35,0.18)]">

                    <div class="border-b border-rule-soft bg-paper-2/50 px-7 py-5">
                        <div class="flex items-center justify-between">
                            <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate">Total</p>
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-fox-soft px-2 py-0.5 text-[9.5px] font-medium uppercase tracking-[0.18em] text-ink">
                                <span class="size-1.5 rounded-full bg-fox-deep"></span>
                                Live
                            </div>
                        </div>
                        <p class="mt-2 flex items-baseline gap-1 font-mono leading-none tnum">
                            <span class="text-[22px] text-slate-soft">{{ $currency->toSymbol() }}</span>
                            <span class="text-[44px] text-ink" x-text="formatWhole(total)"></span>
                        </p>
                    </div>

                    <div class="px-7 py-5">
                        <dl class="flex flex-col gap-3 text-[13px]">
                            <div class="flex items-baseline justify-between">
                                <dt class="text-slate">Required</dt>
                                <dd class="font-mono tnum text-ink">{{ $currency->toSymbol() }}{{ number_format($requiredBase / 100, 0) }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <dt class="text-slate">
                                    Optional
                                    <span class="ml-0.5 text-slate-soft">·</span>
                                    <span class="text-slate-soft" x-text="optionalOnCount"></span>
                                    <span class="text-slate-soft">of {{ $optionalCount }}</span>
                                </dt>
                                <dd class="font-mono tnum text-ink">
                                    {{ $currency->toSymbol() }}<span x-text="formatWhole(optionalSum)"></span>
                                </dd>
                            </div>
                            @foreach ($percentageFeatures as $percentageFeature)
                                <div class="flex items-baseline justify-between"
                                     wire:key="rail-pct-{{ $percentageFeature->id }}"
                                     x-bind:class="isOn({{ $percentageFeature->id }}) ? '' : 'opacity-45'">
                                    <dt class="min-w-0 truncate text-slate">
                                        {{ $percentageFeature->name }}
                                        <span class="text-slate-soft">· {{ $percentageFeature->percentageForHumans() }}</span>
                                    </dt>
                                    <dd class="ml-3 shrink-0 font-mono tnum text-ink">
                                        {{ $currency->toSymbol() }}<span x-text="formatWhole(percentageAmount({{ $percentageFeature->id }}))"></span>
                                    </dd>
                                </div>
                            @endforeach
                            <div class="flex items-baseline justify-between pt-2 text-slate-soft">
                                <dt>{{ $taxName }} ({{ rtrim(rtrim(number_format($taxRate, 1), '0'), '.') }}%)</dt>
                                <dd class="font-mono tnum">
                                    {{ $currency->toSymbol() }}<span x-text="formatWhole(tax)"></span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if ($recurringFeatures->isNotEmpty())
                        {{-- Its own block, below the one-off figures and
                             visually separated, so the two are never read as
                             one number. --}}
                        <div class="border-t border-rule-soft bg-paper-2/40 px-7 py-5">
                            <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate">Then, ongoing</p>
                            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                                @foreach ($recurringPeriods as $period)
                                    <div class="flex items-baseline justify-between"
                                         x-show="recurringTotal('{{ $period->value }}') > 0"
                                         x-cloak>
                                        <dt class="text-slate">{{ $period->totalLabel() }}</dt>
                                        <dd class="font-mono tnum text-ink">
                                            {{ $currency->toSymbol() }}<span x-text="formatWhole(recurringTotal('{{ $period->value }}'))"></span>
                                        </dd>
                                    </div>
                                @endforeach
                                <p class="text-[11.5px] leading-[1.5] text-slate-soft"
                                   x-show="recurringTotal('monthly') === 0 && recurringTotal('annually') === 0"
                                   x-cloak>
                                    None selected.
                                </p>
                            </dl>
                        </div>
                    @endif

                    @if ($response)
                        {{-- Already answered: show what was recorded, no controls. --}}
                        <div class="border-t border-rule-soft px-7 py-5">
                            @if ($response->wasAccepted())
                                <div class="flex items-center gap-2 text-[13px] font-medium text-status-accepted-fg">
                                    <x-phosphor-check-circle class="size-4" />
                                    Accepted
                                </div>
                                <p class="mt-2 text-[12px] leading-[1.5] text-slate">
                                    Confirmed on {{ $response->responded_at->format('j F Y') }} at
                                    {{ $response->acceptedTotalForHumans }}. We'll be in touch to get started.
                                </p>
                            @else
                                <div class="flex items-center gap-2 text-[13px] font-medium text-status-rejected-fg">
                                    <x-phosphor-x-circle class="size-4" />
                                    Declined
                                </div>
                                <p class="mt-2 text-[12px] leading-[1.5] text-slate">
                                    Recorded on {{ $response->responded_at->format('j F Y') }}. If this was a
                                    mistake, get in touch and we'll reopen it.
                                </p>
                            @endif
                        </div>
                    @elseif ($canRespond)
                        <div class="border-t border-rule-soft px-7 py-5">
                            <p class="mb-3.5 text-[11.5px] leading-[1.5] text-slate">
                                Happy with this configuration? Accepting confirms the options you've selected above.
                            </p>
                            <div class="flex flex-col gap-2">
                                <x-btn variant="accent" type="button"
                                       class="w-full justify-center"
                                       x-on:click="$wire.accept(selectedOptionalIds())"
                                       wire:confirm="Accept this proposal with the options you've selected?"
                                       wire:loading.attr="disabled"
                                       wire:target="accept,reject">
                                    <span wire:loading.remove wire:target="accept">Accept proposal</span>
                                    <span wire:loading wire:target="accept">Confirming…</span>
                                </x-btn>
                                <button type="button"
                                        wire:click="reject"
                                        wire:confirm="Decline this proposal? You can always get back in touch."
                                        wire:loading.attr="disabled"
                                        wire:target="accept,reject"
                                        class="w-full rounded-lg px-3 py-2 text-[12.5px] font-medium text-slate transition-colors hover:bg-paper-2 hover:text-ink">
                                    Decline
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="border-t border-rule-soft bg-paper-2/30 px-7 py-4 text-[11.5px] leading-[1.5] text-slate">
                            Toggle the optional items above to explore different configurations. Figures update as you go.
                        </p>
                    @endif
                </div>

                <p class="mt-6 px-1 text-[11px] leading-[1.5] text-slate-soft">
                    All prices in {{ $currency->name }}. {{ $taxName }} shown for reference. This proposal was prepared on
                    {{ $proposal->created_at->format('j F Y') }} — reach out if anything needs to change.
                </p>
            </aside>
        </div>

        <footer class="mt-24 flex items-center justify-between border-t border-ink/10 pt-6 text-[11px] tracking-wider text-slate-soft">
            <span class="font-medium uppercase">Configurator</span>
            <span class="font-mono tnum">
                {{ $proposal->created_at->format('Y') }} · Proposal № {{ str_pad((string) $proposal->id, 4, '0', STR_PAD_LEFT) }}
            </span>
            <span class="uppercase tracking-[0.18em]">End of document</span>
        </footer>
    </div>

    <script>
        function proposalConfigurator() {
            return {
                // Everything here is integer pence, matching ProposalPricing
                // on the server. Working in pounds would let the figure on
                // screen drift from the one recorded on acceptance.
                requiredBase: {{ $requiredBase }},
                taxRate: {{ $taxRate }} / 100,
                optionals: @js($optionalInitial),
                percentages: @js($percentageInitial),
                recurrings: @js($recurringInitial),
                locked: @js((bool) $response),

                toggle(id) {
                    if (this.locked) return;
                    const line = this.optionals[id] ?? this.percentages[id] ?? this.recurrings[id];
                    if (line) line.on = ! line.on;
                },
                // The payload sent on accept: ids only, fixed and percentage
                // alike. Amounts are recomputed server-side, so nothing here
                // is trusted.
                selectedOptionalIds() {
                    const ids = [];
                    for (const [id, o] of Object.entries(this.optionals)) {
                        if (o.on) ids.push(id);
                    }
                    for (const [id, p] of Object.entries(this.percentages)) {
                        if (p.optional && p.on) ids.push(id);
                    }
                    for (const [id, r] of Object.entries(this.recurrings)) {
                        if (r.optional && r.on) ids.push(id);
                    }
                    return ids;
                },
                isOn(id) {
                    return (this.optionals[id] ?? this.percentages[id] ?? this.recurrings[id])?.on ?? false;
                },
                get optionalSum() {
                    let sum = 0;
                    for (const o of Object.values(this.optionals)) {
                        if (o.on) sum += Number(o.price);
                    }
                    return sum;
                },
                get optionalOnCount() {
                    let n = Object.values(this.optionals).filter(o => o.on).length;
                    n += Object.values(this.percentages).filter(p => p.optional && p.on).length;
                    n += Object.values(this.recurrings).filter(r => r.optional && r.on).length;
                    return n;
                },
                // What the percentages are a proportion of: the fixed lines
                // currently included, never each other.
                get fixedBase() {
                    return this.requiredBase + this.optionalSum;
                },
                // Mirrors ProposalPricing::lineAmount — basis points, rounded
                // half up to the penny.
                percentageAmount(id) {
                    const line = this.percentages[id];
                    if (! line || ! line.on) return 0;
                    return Math.round(this.fixedBase * line.rate / 10000);
                },
                get percentageSum() {
                    let sum = 0;
                    for (const id of Object.keys(this.percentages)) {
                        sum += this.percentageAmount(id);
                    }
                    return sum;
                },
                get subtotal() {
                    return this.fixedBase + this.percentageSum;
                },
                get tax() {
                    return Math.round(this.subtotal * this.taxRate);
                },
                get total() {
                    return this.subtotal + this.tax;
                },
                // Totalled per period and never added to the one-off figure:
                // a build fee and a monthly fee are different commitments.
                recurringTotal(period) {
                    let sum = 0;
                    for (const r of Object.values(this.recurrings)) {
                        if (r.on && r.period === period) sum += Number(r.amount);
                    }
                    return sum;
                },
                // Pence in, whole currency units out.
                formatWhole(pence) {
                    return Number(Math.round(pence / 100)).toLocaleString('en-GB');
                },
            };
        }
    </script>
</div>
