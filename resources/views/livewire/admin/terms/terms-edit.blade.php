@php
    $btn = 'flex size-8 items-center justify-center rounded-md text-slate transition-colors hover:bg-paper-2 hover:text-ink';
    $btnOn = 'bg-ink text-paper hover:bg-ink hover:text-paper';
@endphp
<div class="mx-auto max-w-[1100px]">

    <x-page-header
        :title="$terms->name"
        eyebrow="Terms &amp; conditions"
        :lede="$currentVersion
            ? 'Current version is ' . $currentVersion->label() . ', published ' . $currentVersion->published_at->format('j F Y') . '. Edits below build the next version.'
            : 'Not published yet. Proposals can\'t be pinned to this set until you publish a version.'">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.terms')">Back to list</x-btn>
            @unless ($terms->is_default)
                <x-btn variant="ghost" type="button" wire:click="makeDefault">Make default</x-btn>
            @endunless
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[1fr_280px]">

        <div class="min-w-0">
            @if ($viewing)
                {{-- Read-only look at a frozen version --}}
                <x-card>
                    <x-card-header :title="$viewing->label()"
                                   :meta="'Published ' . $viewing->published_at->format('j F Y')" />
                    <div class="px-[22px] py-6">
                        <div class="prose-terms">{!! $viewing->body !!}</div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-[22px] py-4">
                        <p class="text-[12.5px] text-slate">
                            Published versions can't be edited — proposals pinned to this one have to keep showing it.
                        </p>
                        <div class="flex gap-2">
                            <x-btn variant="ghost" type="button" wire:click="backToDraft">Back to draft</x-btn>
                            <x-btn variant="accent" type="button"
                                   wire:click="restoreVersion({{ $viewing->id }})"
                                   wire:confirm="Copy {{ $viewing->label() }} into the draft? Your current unpublished changes will be replaced.">
                                Restore into draft
                            </x-btn>
                        </div>
                    </div>
                </x-card>
            @else
                <x-card>
                    <x-card-header title="Draft"
                                   :meta="$currentVersion ? 'Will publish as v' . ($currentVersion->version + 1) : 'Will publish as v1'" />

                    <div class="px-[22px] py-6">
                        <x-field label="Set name" name="name" required class="mb-6" />

                        <p class="mb-1.5 text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Terms</p>

                        <div x-data="richText(@js($body), 'body')" wire:ignore
                             class="overflow-hidden rounded-lg border border-rule bg-white focus-within:border-ink">

                            {{-- Toolbar --}}
                            <div class="flex flex-wrap items-center gap-0.5 border-b border-rule-soft bg-paper-2 px-2 py-1.5">
                                <button type="button" title="Heading" class="{{ $btn }}"
                                        x-bind:class="isActive('heading', { level: 2 }) && '{{ $btnOn }}'"
                                        x-on:click="run('toggleHeading', { level: 2 })">
                                    <x-phosphor-text-h-two class="size-4" />
                                </button>
                                <button type="button" title="Subheading" class="{{ $btn }}"
                                        x-bind:class="isActive('heading', { level: 3 }) && '{{ $btnOn }}'"
                                        x-on:click="run('toggleHeading', { level: 3 })">
                                    <x-phosphor-text-h-three class="size-4" />
                                </button>

                                <span class="mx-1 h-5 w-px bg-rule"></span>

                                <button type="button" title="Bold" class="{{ $btn }}"
                                        x-bind:class="isActive('bold') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleBold')">
                                    <x-phosphor-text-b class="size-4" />
                                </button>
                                <button type="button" title="Italic" class="{{ $btn }}"
                                        x-bind:class="isActive('italic') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleItalic')">
                                    <x-phosphor-text-italic class="size-4" />
                                </button>
                                <button type="button" title="Strikethrough" class="{{ $btn }}"
                                        x-bind:class="isActive('strike') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleStrike')">
                                    <x-phosphor-text-strikethrough class="size-4" />
                                </button>

                                <span class="mx-1 h-5 w-px bg-rule"></span>

                                <button type="button" title="Bulleted list" class="{{ $btn }}"
                                        x-bind:class="isActive('bulletList') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleBulletList')">
                                    <x-phosphor-list-bullets class="size-4" />
                                </button>
                                <button type="button" title="Numbered list" class="{{ $btn }}"
                                        x-bind:class="isActive('orderedList') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleOrderedList')">
                                    <x-phosphor-list-numbers class="size-4" />
                                </button>
                                <button type="button" title="Quote" class="{{ $btn }}"
                                        x-bind:class="isActive('blockquote') && '{{ $btnOn }}'"
                                        x-on:click="run('toggleBlockquote')">
                                    <x-phosphor-quotes class="size-4" />
                                </button>

                                <span class="mx-1 h-5 w-px bg-rule"></span>

                                <button type="button" title="Link" class="{{ $btn }}"
                                        x-bind:class="isActive('link') && '{{ $btnOn }}'"
                                        x-on:click="setLink()">
                                    <x-phosphor-link-simple class="size-4" />
                                </button>
                                <button type="button" title="Divider" class="{{ $btn }}"
                                        x-on:click="run('setHorizontalRule')">
                                    <x-phosphor-minus class="size-4" />
                                </button>

                                <span class="ml-auto flex items-center gap-1 pr-1">
                                    <button type="button" title="Undo" class="{{ $btn }}" x-on:click="run('undo')">
                                        <x-phosphor-arrow-counter-clockwise class="size-4" />
                                    </button>
                                    <button type="button" title="Redo" class="{{ $btn }}" x-on:click="run('redo')">
                                        <x-phosphor-arrow-clockwise class="size-4" />
                                    </button>
                                </span>
                            </div>

                            <div x-ref="editor" class="px-4 py-3.5"></div>
                        </div>

                        <p class="mt-1.5 text-[12px] text-slate">
                            Headings, lists, emphasis and links. Anything else is stripped on save —
                            this renders on a public page.
                        </p>
                        @error('body')
                            <p class="mt-1.5 text-[12px] text-status-rejected-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-[22px] py-4">
                        <p class="text-[12.5px] text-slate">
                            Drafts aren't visible to clients. Publishing freezes this text as a version.
                        </p>
                        <div class="flex gap-2">
                            <x-btn variant="ghost" type="button" wire:click="saveDraft">
                                <span wire:loading.remove wire:target="saveDraft">Save draft</span>
                                <span wire:loading wire:target="saveDraft">Saving…</span>
                            </x-btn>
                            <x-btn variant="accent" type="button"
                                   wire:click="publish"
                                   wire:confirm="Publish these terms? New proposals will be pinned to this version.">
                                <span wire:loading.remove wire:target="publish">Publish</span>
                                <span wire:loading wire:target="publish">Publishing…</span>
                            </x-btn>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Version history --}}
        <div>
            <x-card>
                <x-card-header title="History" :meta="$versions->count() . ' ' . Str::plural('version', $versions->count())" />
                <div class="py-1">
                    @if ($draft)
                        <button type="button"
                                wire:click="backToDraft"
                                @class([
                                    'flex w-full items-start gap-3 border-b border-rule-soft px-[22px] py-3 text-left transition-colors hover:bg-paper-2 last:border-b-0',
                                    'bg-paper-2' => ! $viewing,
                                ])>
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-status-archived-dot"></span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-medium text-ink">Draft</span>
                                <span class="block text-[12px] text-slate">
                                    {{ $draft->body ? 'Edited '.$draft->updated_at->diffForHumans() : 'Empty' }}
                                </span>
                            </span>
                        </button>
                    @endif

                    @forelse ($versions as $version)
                        <button type="button"
                                wire:key="version-{{ $version->id }}"
                                wire:click="viewVersion({{ $version->id }})"
                                @class([
                                    'flex w-full items-start gap-3 border-b border-rule-soft px-[22px] py-3 text-left transition-colors hover:bg-paper-2 last:border-b-0',
                                    'bg-paper-2' => $viewing && $viewing->id === $version->id,
                                ])>
                            <span @class([
                                'mt-1 size-1.5 shrink-0 rounded-full',
                                'bg-status-accepted-dot' => $loop->first,
                                'bg-slate-faint' => ! $loop->first,
                            ])></span>
                            <span class="min-w-0">
                                <span class="block font-mono text-[13px] font-medium text-ink tnum">
                                    {{ $version->label() }}
                                    @if ($loop->first)
                                        <span class="ml-1 font-sans text-[10px] font-medium uppercase tracking-wider text-slate">Current</span>
                                    @endif
                                </span>
                                <span class="block text-[12px] text-slate">
                                    {{ $version->published_at->format('j M Y') }}
                                </span>
                            </span>
                        </button>
                    @empty
                        <p class="px-[22px] py-8 text-center text-[13px] text-slate">
                            Nothing published yet.
                        </p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
