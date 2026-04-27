<div
    x-data="commandPalette()"
    @keydown.window.escape.prevent="if ($wire.open) $wire.closePalette()"
    @keydown.window.meta.k.prevent="$wire.open ? $wire.closePalette() : $wire.openPalette()"
    @keydown.window.ctrl.k.prevent="$wire.open ? $wire.closePalette() : $wire.openPalette()"
>
    @php
        $items = [];
        foreach ($results['suggested'] as $action) {
            $items[] = ['kind' => 'action', 'href' => route($action['route']), 'label' => $action['label']];
        }
        foreach ($results['proposals'] as $proposal) {
            $items[] = ['kind' => 'proposal', 'href' => route('dashboard.proposal.edit', $proposal), 'label' => $proposal->name];
        }
        foreach ($results['clients'] as $client) {
            $items[] = ['kind' => 'client', 'href' => route('dashboard.clients').'?q='.urlencode($client->name), 'label' => $client->name];
        }
        foreach ($results['features'] as $feature) {
            $items[] = ['kind' => 'feature', 'href' => route('dashboard.features').'?q='.urlencode($feature->name), 'label' => $feature->name];
        }
        foreach ($results['packages'] as $package) {
            $items[] = ['kind' => 'package', 'href' => route('dashboard.package.edit', $package), 'label' => $package->name];
        }
    @endphp

    <div
        x-show="$wire.open"
        x-transition.opacity.duration.140ms
        x-cloak
        @click.self="$wire.closePalette()"
        class="fixed inset-0 z-[100] flex items-start justify-center bg-ink/45 px-4 pt-[12vh] backdrop-blur-md"
        role="presentation"
    >
        <div
            x-show="$wire.open"
            x-transition:enter="transition ease-out duration-180"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="w-full max-w-[640px] overflow-hidden rounded-[14px] border border-rule bg-paper shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-label="Search anything"
            @keydown.arrow-down.prevent="moveFocus(1)"
            @keydown.arrow-up.prevent="moveFocus(-1)"
            @keydown.enter.prevent="activateFocused()"
        >
            <div class="flex items-center gap-3.5 border-b border-rule px-5 py-4">
                <x-phosphor-magnifying-glass class="size-[18px] text-slate" />
                <input
                    type="text"
                    wire:model.live.debounce.150ms="query"
                    x-ref="input"
                    x-init="$watch('$wire.open', value => { if (value) { $nextTick(() => { $refs.input.focus(); focusedIndex = 0; }); } })"
                    placeholder="Search proposals, clients, features — or type a command…"
                    class="flex-1 border-0 bg-transparent font-display text-[16px] tracking-[-0.01em] text-ink outline-none placeholder:text-slate-faint focus:ring-0"
                    autocomplete="off"
                    spellcheck="false"
                >
                <span class="rounded border border-b-2 border-rule bg-white px-1.5 py-0.5 font-mono text-[10.5px] font-medium leading-[16px] text-slate">esc</span>
            </div>

            <div class="max-h-[60vh] overflow-y-auto" x-ref="list">
                @if (count($items) === 0)
                    <div class="px-6 py-10 text-center text-[13px] text-slate">
                        No matches for &ldquo;{{ $query }}&rdquo;.
                    </div>
                @else
                    @php $cursor = 0; @endphp

                    @if (count($results['suggested']) > 0)
                        <div class="py-2">
                            <div class="px-5 pb-1 pt-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.14em] text-slate">
                                {{ $query === '' ? 'Suggested' : 'Actions' }}
                            </div>
                            @foreach ($results['suggested'] as $action)
                                <a
                                    wire:key="action-{{ $loop->index }}"
                                    href="{{ route($action['route']) }}"
                                    data-palette-item="{{ $cursor }}"
                                    @mouseenter="focusedIndex = {{ $cursor }}"
                                    :class="focusedIndex === {{ $cursor }} ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'"
                                    class="flex items-center gap-3.5 px-5 py-2.5 text-[13.5px] transition-colors"
                                >
                                    <x-dynamic-component
                                        :component="'phosphor-'.$action['icon']"
                                        class="size-[18px] shrink-0"
                                        ::class="focusedIndex === {{ $cursor }} ? 'text-fox' : 'text-slate'"
                                    />
                                    <span>{{ $action['label'] }}</span>
                                    @if (! empty($action['hint']))
                                        <span
                                            class="ml-auto text-[11.5px]"
                                            :class="focusedIndex === {{ $cursor }} ? 'text-sage-soft' : 'text-slate-faint'"
                                        >{{ $action['hint'] }}</span>
                                    @endif
                                </a>
                                @php $cursor++; @endphp
                            @endforeach
                        </div>
                    @endif

                    @if (count($results['proposals']) > 0)
                        <div class="border-t border-rule-soft py-2">
                            <div class="px-5 pb-1 pt-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.14em] text-slate">
                                {{ $query === '' ? 'Recent proposals' : 'Proposals' }}
                            </div>
                            @foreach ($results['proposals'] as $proposal)
                                <a
                                    wire:key="proposal-{{ $proposal->id }}"
                                    href="{{ route('dashboard.proposal.edit', $proposal) }}"
                                    data-palette-item="{{ $cursor }}"
                                    @mouseenter="focusedIndex = {{ $cursor }}"
                                    :class="focusedIndex === {{ $cursor }} ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'"
                                    class="flex items-center gap-3.5 px-5 py-2.5 text-[13.5px] transition-colors"
                                >
                                    <x-phosphor-file-text
                                        class="size-[18px] shrink-0"
                                        ::class="focusedIndex === {{ $cursor }} ? 'text-fox' : 'text-slate'"
                                    />
                                    <span class="truncate">
                                        @if ($proposal->client)
                                            <span class="opacity-70">{{ $proposal->client->name }} ·</span>
                                        @endif
                                        {{ $proposal->name }}
                                    </span>
                                    <span
                                        class="ml-auto text-[11.5px]"
                                        :class="focusedIndex === {{ $cursor }} ? 'text-sage-soft' : 'text-slate-faint'"
                                    >Proposal</span>
                                </a>
                                @php $cursor++; @endphp
                            @endforeach
                        </div>
                    @endif

                    @if (count($results['clients']) > 0)
                        <div class="border-t border-rule-soft py-2">
                            <div class="px-5 pb-1 pt-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.14em] text-slate">Clients</div>
                            @foreach ($results['clients'] as $client)
                                <a
                                    wire:key="client-{{ $client->id }}"
                                    href="{{ route('dashboard.clients').'?q='.urlencode($client->name) }}"
                                    data-palette-item="{{ $cursor }}"
                                    @mouseenter="focusedIndex = {{ $cursor }}"
                                    :class="focusedIndex === {{ $cursor }} ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'"
                                    class="flex items-center gap-3.5 px-5 py-2.5 text-[13.5px] transition-colors"
                                >
                                    <x-phosphor-users-three
                                        class="size-[18px] shrink-0"
                                        ::class="focusedIndex === {{ $cursor }} ? 'text-fox' : 'text-slate'"
                                    />
                                    <span class="truncate">{{ $client->name }}</span>
                                    <span
                                        class="ml-auto text-[11.5px]"
                                        :class="focusedIndex === {{ $cursor }} ? 'text-sage-soft' : 'text-slate-faint'"
                                    >Client</span>
                                </a>
                                @php $cursor++; @endphp
                            @endforeach
                        </div>
                    @endif

                    @if (count($results['features']) > 0)
                        <div class="border-t border-rule-soft py-2">
                            <div class="px-5 pb-1 pt-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.14em] text-slate">Features</div>
                            @foreach ($results['features'] as $feature)
                                <a
                                    wire:key="feature-{{ $feature->id }}"
                                    href="{{ route('dashboard.features').'?q='.urlencode($feature->name) }}"
                                    data-palette-item="{{ $cursor }}"
                                    @mouseenter="focusedIndex = {{ $cursor }}"
                                    :class="focusedIndex === {{ $cursor }} ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'"
                                    class="flex items-center gap-3.5 px-5 py-2.5 text-[13.5px] transition-colors"
                                >
                                    <x-phosphor-stack
                                        class="size-[18px] shrink-0"
                                        ::class="focusedIndex === {{ $cursor }} ? 'text-fox' : 'text-slate'"
                                    />
                                    <span class="truncate">{{ $feature->name }}</span>
                                    <span
                                        class="ml-auto text-[11.5px]"
                                        :class="focusedIndex === {{ $cursor }} ? 'text-sage-soft' : 'text-slate-faint'"
                                    >Feature</span>
                                </a>
                                @php $cursor++; @endphp
                            @endforeach
                        </div>
                    @endif

                    @if (count($results['packages']) > 0)
                        <div class="border-t border-rule-soft py-2">
                            <div class="px-5 pb-1 pt-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.14em] text-slate">Packages</div>
                            @foreach ($results['packages'] as $package)
                                <a
                                    wire:key="package-{{ $package->id }}"
                                    href="{{ route('dashboard.package.edit', $package) }}"
                                    data-palette-item="{{ $cursor }}"
                                    @mouseenter="focusedIndex = {{ $cursor }}"
                                    :class="focusedIndex === {{ $cursor }} ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'"
                                    class="flex items-center gap-3.5 px-5 py-2.5 text-[13.5px] transition-colors"
                                >
                                    <x-phosphor-cube
                                        class="size-[18px] shrink-0"
                                        ::class="focusedIndex === {{ $cursor }} ? 'text-fox' : 'text-slate'"
                                    />
                                    <span class="truncate">{{ $package->name }}</span>
                                    <span
                                        class="ml-auto text-[11.5px]"
                                        :class="focusedIndex === {{ $cursor }} ? 'text-sage-soft' : 'text-slate-faint'"
                                    >Package</span>
                                </a>
                                @php $cursor++; @endphp
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            <div class="flex items-center justify-between border-t border-rule bg-paper-2 px-5 py-2.5 text-[11.5px] text-slate">
                <div class="flex items-center gap-3.5">
                    <span class="flex items-center gap-1.5">
                        <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">↑↓</kbd>
                        navigate
                    </span>
                    <span class="flex items-center gap-1.5">
                        <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">↵</kbd>
                        select
                    </span>
                    <span class="flex items-center gap-1.5">
                        <kbd class="rounded border border-b-2 border-rule bg-white px-1.5 py-0 font-mono text-[10.5px] font-medium leading-[18px] text-slate">esc</kbd>
                        close
                    </span>
                </div>
                <div>{{ auth()->user()?->tenant?->name ?? 'Configurator' }} · workspace</div>
            </div>
        </div>
    </div>

    <script>
        function commandPalette() {
            return {
                focusedIndex: 0,
                itemCount() { return this.$refs.list?.querySelectorAll('[data-palette-item]').length ?? 0; },
                moveFocus(delta) {
                    const count = this.itemCount();
                    if (count === 0) { return; }
                    this.focusedIndex = (this.focusedIndex + delta + count) % count;
                    const target = this.$refs.list.querySelector(`[data-palette-item="${this.focusedIndex}"]`);
                    target?.scrollIntoView({ block: 'nearest' });
                },
                activateFocused() {
                    const target = this.$refs.list.querySelector(`[data-palette-item="${this.focusedIndex}"]`);
                    if (target) { target.click(); }
                },
            };
        }
    </script>
</div>
