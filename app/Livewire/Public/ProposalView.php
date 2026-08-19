<?php

namespace App\Livewire\Public;

use App\Enums\ActivityAction;
use App\Enums\Status;
use App\Facades\Settings;
use App\Models\Activity;
use App\Models\Proposal;
use App\Models\ProposalResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The client-facing proposal.
 *
 * Served from a public, unauthenticated URL keyed on the proposal's UUID, and
 * used by the admin "Preview (client view)" action too, so what an admin
 * previews is byte-for-byte what the client sees.
 *
 * When a passcode is set, the proposal's content is never sent to the browser
 * until the passcode has been accepted — the locked state renders its own view
 * rather than hiding content client-side.
 */
#[Layout('components.layouts.app')]
class ProposalView extends Component
{
    #[Locked]
    public Proposal $proposal;

    #[Locked]
    public bool $unlocked = false;

    #[Locked]
    public bool $expired = false;

    #[Locked]
    public bool $responded = false;

    public string $passcode = '';

    public string $passcodeError = '';

    private const MAX_ATTEMPTS = 10;

    private const DECAY_SECONDS = 60;

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal;

        // There is no authenticated user here and therefore no session tenant,
        // so the settings owner has to be named explicitly. Without this the
        // tenant scope is a no-op and the proposal would render with whichever
        // tenant's currency and tax config happened to be found first.
        Settings::forTenant($proposal->tenant_id);

        $this->expired = $proposal->hasExpired();

        $this->unlocked = ! $this->expired
            && (! $proposal->isPasscodeProtected() || (bool) session($proposal->unlockSessionKey(), false));

        $this->responded = $proposal->hasResponse();
    }

    public function unlock(): void
    {
        $this->passcodeError = '';

        if ($this->expired) {
            return;
        }

        // Throttled here rather than via route middleware: Livewire actions all
        // arrive on the same livewire/update endpoint, so a route-level limiter
        // couldn't tell one proposal's attempts from another's.
        $key = 'proposal-unlock|'.request()->ip().'|'.$this->proposal->uuid;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $this->passcodeError = "Too many attempts. Try again in {$seconds} seconds.";

            return;
        }

        if (! $this->proposal->matchesPasscode($this->passcode)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            $this->passcode = '';
            $this->passcodeError = 'That passcode is not right.';

            return;
        }

        RateLimiter::clear($key);

        session([$this->proposal->unlockSessionKey() => true]);

        $this->passcode = '';
        $this->unlocked = true;
    }

    /**
     * Record the client's acceptance.
     *
     * @param  array<int, int|string>  $selectedOptionalIds  final_feature ids the client kept
     */
    public function accept(array $selectedOptionalIds = []): void
    {
        if (! $this->canRespond()) {
            return;
        }

        // Only the *identity* of the chosen features is taken from the client.
        // Their prices, and therefore the total, are read back out of the
        // database — a tampered price in the payload can't change what is
        // recorded. Ids that aren't optional features of this proposal are
        // discarded rather than trusted.
        $ids = collect($selectedOptionalIds)
            ->filter(fn ($id) => is_int($id) || (is_string($id) && ctype_digit($id)))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $selected = $this->proposal->features()
            ->where('optional', true)
            ->whereIn('id', $ids)
            ->get();

        $required = $this->proposal->features()->where('optional', false)->get();

        // getRawOriginal bypasses FinalFeature's price accessor, which converts
        // pence to pounds. Totals stay in integer pence end to end.
        $total = $required->concat($selected)
            ->sum(fn ($feature) => (int) $feature->getRawOriginal('price') * (int) $feature->quantity);

        $this->storeResponse(Status::ACCEPTED, $selected->pluck('id')->all(), (int) $total);
    }

    public function reject(): void
    {
        if (! $this->canRespond()) {
            return;
        }

        $this->storeResponse(Status::REJECTED, [], 0);
    }

    /**
     * @param  array<int, int>  $selectedFeatureIds
     */
    private function storeResponse(Status $status, array $selectedFeatureIds, int $total): void
    {
        DB::transaction(function () use ($status, $selectedFeatureIds, $total) {
            $response = new ProposalResponse([
                'status' => $status,
                'selected_feature_ids' => $selectedFeatureIds,
                'accepted_total' => $total,
                'responded_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 512),
            ]);
            // No session tenant on a public request, so stamp it from the
            // proposal rather than relying on BelongsToTenant.
            $response->tenant_id = $this->proposal->tenant_id;
            $response->proposal()->associate($this->proposal);
            $response->save();

            $this->proposal->status = $status;
            $this->proposal->save();

            Activity::log(
                $status === Status::ACCEPTED
                    ? ActivityAction::ProposalAccepted
                    : ActivityAction::ProposalRejected,
                $this->proposal,
                ['total' => $total, 'optional_kept' => count($selectedFeatureIds)],
            );
        });

        $this->proposal->refresh();
        $this->responded = true;
    }

    private function canRespond(): bool
    {
        return $this->unlocked
            && ! $this->expired
            && $this->proposal->isOpenForResponse();
    }

    public function render(): View
    {
        if (! $this->unlocked) {
            return view('livewire.public.proposal-locked')
                ->title('Proposal');
        }

        $this->proposal->loadMissing(['features', 'user', 'client']);

        $features = $this->proposal->features;

        $roots = $features->whereNull('parent_id')
            ->sortBy([['order', 'asc'], ['name', 'asc']])
            ->values();

        $groups = $roots->map(fn ($root) => [
            'root' => $root,
            'children' => $features->where('parent_id', $root->id)->sortBy('name')->values(),
        ]);

        $requiredTotal = (float) $features->where('optional', false)
            ->sum(fn ($f) => $f->price * $f->quantity);

        $optionalFeatures = $features->where('optional', true);
        $optionalTotal = (float) $optionalFeatures
            ->sum(fn ($f) => $f->price * $f->quantity);

        $optionalInitial = $optionalFeatures
            ->mapWithKeys(fn ($f) => [(string) $f->id => [
                'on' => true,
                'price' => (float) ($f->price * $f->quantity),
            ]])
            ->all();

        $response = $this->proposal->response;

        // Once answered, the optional toggles freeze to the client's recorded
        // choice instead of staying interactive.
        if ($response !== null) {
            $kept = array_map('intval', $response->selected_feature_ids ?? []);

            $optionalInitial = $optionalFeatures
                ->mapWithKeys(fn ($f) => [(string) $f->id => [
                    'on' => in_array((int) $f->id, $kept, true),
                    'price' => (float) ($f->price * $f->quantity),
                ]])
                ->all();
        }

        return view('livewire.public.proposal-view', [
            'groups' => $groups,
            'requiredTotal' => $requiredTotal,
            'optionalTotal' => $optionalTotal,
            'optionalCount' => $optionalFeatures->count(),
            'optionalInitial' => $optionalInitial,
            'response' => $response,
            'canRespond' => $this->canRespond(),
            'taxName' => Settings::getTaxName(),
            'taxRate' => (float) Settings::getTaxRate(),
            'currency' => Settings::getCurrency(),
        ])->title($this->proposal->name.' — Proposal');
    }
}
