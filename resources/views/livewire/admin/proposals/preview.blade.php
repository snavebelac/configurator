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
                <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate">
                    <span class="inline-block size-1.5 -translate-y-[1px] rounded-full bg-fox-deep align-middle"></span>
                    <span class="ml-2 align-middle">A Configurator proposal</span>
                </p>
                <p class="font-mono text-[11px] tracking-wider text-slate-soft tnum">№ {{ $proposal->reference ?? str_pad((string) $proposal->id, 4, '0', STR_PAD_LEFT) }}</p>
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
                    <section class="mb-14" wire:key="group-{{ $group['root']->id }}">
                        <div class="mb-7 border-b border-rule pb-3">
                            <h2 class="font-display text-[24px] leading-[1.15] text-ink">
                                {{ $group['root']->name }}
                            </h2>
                        </div>

                        <div class="flex flex-col divide-y divide-rule-soft">
                            @include('livewire.admin.proposals.partials.preview-row', [
                                'feature' => $group['root'],
                                'isChild' => false,
                            ])
                            @foreach ($group['children'] as $child)
                                @include('livewire.admin.proposals.partials.preview-row', [
                                    'feature' => $child,
                                    'isChild' => true,
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

                @if ($proposal->additional)
                    <section class="mt-20 border-t border-ink/10 pt-10">
                        <p class="text-[10.5px] font-medium uppercase tracking-[0.22em] text-slate-soft">Notes</p>
                        <p class="mt-4 max-w-[58ch] font-display text-[17px] italic leading-[1.7] text-ink">
                            {{ $proposal->additional }}
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
                            <span class="text-[22px] text-slate-soft">£</span>
                            <span class="text-[44px] text-ink" x-text="formatWhole(total)"></span>
                        </p>
                    </div>

                    <div class="px-7 py-5">
                        <dl class="flex flex-col gap-3 text-[13px]">
                            <div class="flex items-baseline justify-between">
                                <dt class="text-slate">Required</dt>
                                <dd class="font-mono tnum text-ink">£{{ number_format($requiredTotal, 0) }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <dt class="text-slate">
                                    Optional
                                    <span class="ml-0.5 text-slate-soft">·</span>
                                    <span class="text-slate-soft" x-text="optionalOnCount"></span>
                                    <span class="text-slate-soft">of {{ $optionalCount }}</span>
                                </dt>
                                <dd class="font-mono tnum text-ink">
                                    £<span x-text="formatWhole(optionalSum)"></span>
                                </dd>
                            </div>
                            <div class="flex items-baseline justify-between pt-2 text-slate-soft">
                                <dt>{{ $taxName }} ({{ rtrim(rtrim(number_format($taxRate, 1), '0'), '.') }}%)</dt>
                                <dd class="font-mono tnum">
                                    £<span x-text="formatWhole(tax)"></span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <p class="border-t border-rule-soft bg-paper-2/30 px-7 py-4 text-[11.5px] leading-[1.5] text-slate">
                        Toggle the optional items above to explore different configurations. Figures update as you go.
                    </p>
                </div>

                <p class="mt-6 px-1 text-[11px] leading-[1.5] text-slate-soft">
                    All prices in GBP. {{ $taxName }} shown for reference. This proposal was prepared on
                    {{ $proposal->created_at->format('j F Y') }} — reach out if anything needs to change.
                </p>
            </aside>
        </div>

        <footer class="mt-24 flex items-center justify-between border-t border-ink/10 pt-6 text-[11px] tracking-wider text-slate-soft">
            <span class="font-medium uppercase">Configurator</span>
            <span class="font-mono tnum">
                Proposal № {{ $proposal->reference ?? str_pad((string) $proposal->id, 4, '0', STR_PAD_LEFT) }}
            </span>
            <span class="uppercase tracking-[0.18em]">End of document</span>
        </footer>
    </div>

    <script>
        function proposalConfigurator() {
            return {
                required: {{ $requiredTotal }},
                taxRate: {{ $taxRate }} / 100,
                optionals: @js($optionalInitial),

                toggle(id) {
                    this.optionals[id].on = ! this.optionals[id].on;
                },
                isOn(id) {
                    return this.optionals[id]?.on ?? false;
                },
                get optionalSum() {
                    let sum = 0;
                    for (const o of Object.values(this.optionals)) {
                        if (o.on) sum += Number(o.price);
                    }
                    return sum;
                },
                get optionalOnCount() {
                    return Object.values(this.optionals).filter(o => o.on).length;
                },
                get subtotal() {
                    return this.required + this.optionalSum;
                },
                get tax() {
                    return this.subtotal * this.taxRate;
                },
                get total() {
                    return this.subtotal + this.tax;
                },
                formatWhole(n) {
                    return Number(Math.round(n)).toLocaleString('en-GB');
                },
            };
        }
    </script>
</div>
