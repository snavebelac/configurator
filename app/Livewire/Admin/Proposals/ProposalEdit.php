<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\PricingType;
use App\Enums\Status;
use App\Facades\Settings;
use App\Helpers\ProposalPricing;
use App\Livewire\Admin\AdminComponent;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Terms;
use App\Models\TermsVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Edit proposal')]
class ProposalEdit extends AdminComponent
{
    public ?int $proposalId = null;

    public Proposal $proposal;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:4000')]
    public ?string $description = null;

    #[Validate('nullable|string|max:4000')]
    public ?string $additional = null;

    public function mount(Proposal $proposal): void
    {
        $this->proposalId = $proposal->id;
        $this->name = $proposal->name;
        $this->description = $proposal->description;
        $this->additional = $proposal->additional;
    }

    /**
     * The proposal's own copy — the title, the opening paragraph and the
     * closing notes. All three render on the client-facing view; until now
     * none of them could be edited after creation.
     */
    public function saveDetails(): void
    {
        $this->validate();

        $this->proposal->name = $this->name;
        $this->proposal->description = $this->description ?: null;
        $this->proposal->additional = $this->additional ?: null;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success(['text' => 'Proposal details saved']));
    }

    #[On('refreshFeatureProposalEdit')]
    public function refresh(): void
    {
        $this->proposal->load(['features', 'client', 'user']);
    }

    /**
     * Send the proposal: move it out of draft so the client's public link can
     * be answered. Until this happens accept/reject is not offered.
     */
    public function markAsDelivered(): void
    {
        if (! $this->proposal->canBeDelivered()) {
            $this->dispatch('toast', ...$this->warning([
                'text' => $this->proposal->percentagesHaveNoBase()
                    ? 'The percentage lines on this proposal have no work to apply to, so they come to nothing. Add some fixed-price work first.'
                    : 'Add at least one feature before marking this proposal as delivered.',
            ]));

            return;
        }

        // Pin the terms as they stand right now. Doing it here rather than at
        // creation means a proposal drafted in March and sent in July goes out
        // under July's terms, and doing it at all means editing the tenant's
        // terms afterwards can't rewrite what was sent.
        if ($this->proposal->terms_version_id === null) {
            $this->proposal->terms_version_id = Terms::query()
                ->where('is_default', true)
                ->first()
                ?->currentVersion?->id;
        }

        $this->proposal->status = Status::DELIVERED;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => 'Marked as delivered. Your client can now respond to the share link.',
        ]));
    }

    /**
     * Clear a client's response so they can answer again — the escape hatch for
     * an accidental accept or decline.
     */
    public function reopen(): void
    {
        $this->proposal->response()->delete();

        $this->proposal->status = Status::DELIVERED;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => 'Reopened. The client can respond again.',
        ]));
    }

    /**
     * Swap the terms a proposal goes out under. Only published versions are
     * offered — a draft could still change under the client's feet.
     */
    public function setTermsVersion(?int $termsVersionId): void
    {
        if ($termsVersionId === null) {
            $this->proposal->terms_version_id = null;
            $this->proposal->save();

            $this->dispatch('toast', ...$this->warning([
                'text' => 'Terms removed from this proposal.',
            ]));

            return;
        }

        $version = TermsVersion::query()->published()->find($termsVersionId);

        if ($version === null) {
            return;
        }

        $this->proposal->terms_version_id = $version->id;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => "Now sending under {$version->terms->name} {$version->label()}.",
        ]));
    }

    public function reorderParents(int $finalFeatureId, int $position): void
    {
        // Fixed roots only. Percentage and recurring lines are shown in their
        // own sections and can't be dragged, so including them here would make
        // the dropped position refer to a different list than the one on
        // screen.
        $parentIds = $this->proposal->features()
            ->whereNull('parent_id')
            ->where('pricing_type', PricingType::Fixed)
            ->orderBy('order')
            ->orderBy('name')
            ->pluck('id')
            ->all();

        $currentIndex = array_search($finalFeatureId, $parentIds, true);
        if ($currentIndex === false) {
            return;
        }

        array_splice($parentIds, $currentIndex, 1);
        array_splice($parentIds, $position, 0, [$finalFeatureId]);

        foreach ($parentIds as $index => $id) {
            FinalFeature::where('id', $id)->update(['order' => $index + 1]);
        }

        $this->proposal->load('features');
    }

    public function render(): View
    {
        $this->proposal->load(['features', 'client', 'user', 'response', 'termsVersion.terms']);

        $features = $this->proposal->features;

        // The everything-on basis, matching the running total at the foot of
        // the card and Proposal::total(). A percentage row shows its share of
        // this rather than the £0.00 its own price column would imply.
        $pricing = app(ProposalPricing::class)->calculate(
            $features,
            $features->where('optional', true)->pluck('id')->map(fn ($id) => (int) $id)->all(),
        );

        return view('livewire.admin.proposals.proposal-edit', [
            'featureGroups' => $this->groupFeatures($features),
            'percentageFeatures' => $features->filter(fn (FinalFeature $f) => $f->isPercentage())
                ->sortBy([['order', 'asc'], ['name', 'asc']])
                ->values(),
            'recurringFeatures' => $features->filter(fn (FinalFeature $f) => $f->isRecurring())
                ->sortBy([['billing_period', 'asc'], ['order', 'asc'], ['name', 'asc']])
                ->values(),
            'fixedBase' => $pricing['fixedBase'],
            'currencySymbol' => Settings::getCurrency()->toSymbol(),
            'termsOptions' => Terms::query()
                ->with('currentVersion')
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get()
                ->filter(fn (Terms $set) => $set->currentVersion !== null),
        ]);
    }

    /**
     * @return Collection<int, array{root: FinalFeature, children: Collection<int, FinalFeature>}>
     */
    private function groupFeatures(Collection $features): Collection
    {
        // Fixed lines only — percentages and recurring charges are priced on
        // different terms and get sections of their own, the same way the
        // client-facing view separates them.
        $fixed = $features->filter(fn (FinalFeature $feature) => $feature->isFixed());

        $roots = $fixed->whereNull('parent_id')
            ->sortBy([['order', 'asc'], ['name', 'asc']])
            ->values();

        return $roots->map(fn (FinalFeature $root) => [
            'root' => $root,
            'children' => $fixed->where('parent_id', $root->id)
                ->sortBy('name')
                ->values(),
        ]);
    }
}
