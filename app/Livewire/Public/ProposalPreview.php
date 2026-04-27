<?php

namespace App\Livewire\Public;

use App\Helpers\SettingsHelper;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProposalPreview extends Component
{
    #[Locked]
    public string $uuid = '';

    public string $state = 'preview';

    public string $code = '';

    private ?Proposal $proposalCache = null;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
        $proposal = $this->proposal();

        if ($proposal->isExpired()) {
            $this->state = 'expired';

            return;
        }

        if ($proposal->requiresCode() && ! $this->hasUnlockedCookie()) {
            $this->state = 'gate';

            return;
        }

        $this->state = 'preview';
    }

    public function submitCode(): void
    {
        $this->resetErrorBag();

        $proposal = $this->proposal();
        $key = 'share-code:'.$proposal->id.':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('code', 'Too many attempts. Try again in a few minutes.');

            return;
        }

        if ($proposal->access_code_hash === null || ! Hash::check($this->code, $proposal->access_code_hash)) {
            RateLimiter::hit($key, 60 * 15);
            $this->addError('code', 'Incorrect access code.');

            return;
        }

        RateLimiter::clear($key);

        Cookie::queue(
            $this->cookieName(),
            $this->shareToken(),
            $this->cookieTtlMinutes(),
        );

        $this->state = 'preview';
        $this->code = '';
    }

    public function render(): View
    {
        $proposal = $this->proposal();

        if ($this->state === 'expired') {
            return view('livewire.public.proposal-preview-expired', ['proposal' => $proposal])
                ->title($proposal->name.' — Proposal');
        }

        if ($this->state === 'gate') {
            return view('livewire.public.proposal-preview-gate', ['proposal' => $proposal])
                ->title($proposal->name.' — Proposal');
        }

        $proposal->loadMissing(['features', 'user', 'client']);
        app()->instance(SettingsHelper::class, new SettingsHelper($proposal->tenant_id));

        $features = $proposal->features;

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

        $settings = app(SettingsHelper::class);

        return view('livewire.admin.proposals.preview', [
            'proposal' => $proposal,
            'groups' => $groups,
            'requiredTotal' => $requiredTotal,
            'optionalTotal' => $optionalTotal,
            'optionalCount' => $optionalFeatures->count(),
            'optionalInitial' => $optionalInitial,
            'taxName' => $settings->getTaxName(),
            'taxRate' => (float) $settings->getTaxRate(),
        ])->title($proposal->name.' — Proposal');
    }

    private function proposal(): Proposal
    {
        if ($this->proposalCache === null) {
            $this->proposalCache = Proposal::withoutGlobalScope('tenant')
                ->where('uuid', $this->uuid)
                ->firstOrFail();
        }

        return $this->proposalCache;
    }

    private function hasUnlockedCookie(): bool
    {
        $token = request()->cookie($this->cookieName());

        return is_string($token) && hash_equals($this->shareToken(), $token);
    }

    private function shareToken(): string
    {
        $proposal = $this->proposal();

        return hash_hmac(
            'sha256',
            $proposal->id.'|'.($proposal->access_code_hash ?? ''),
            config('app.key') ?: 'fallback',
        );
    }

    private function cookieName(): string
    {
        return 'share_access_'.$this->proposal()->id;
    }

    private function cookieTtlMinutes(): int
    {
        $weekMinutes = 60 * 24 * 7;
        $proposal = $this->proposal();

        if ($proposal->expires_at === null) {
            return $weekMinutes;
        }

        $minutesUntilExpiry = (int) now()->diffInMinutes($proposal->expires_at);

        return min($weekMinutes, max(1, $minutesUntilExpiry));
    }
}
