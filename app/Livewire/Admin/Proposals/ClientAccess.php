<?php

namespace App\Livewire\Admin\Proposals;

use App\Livewire\Admin\AdminComponent;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Locked;

/**
 * Controls how a proposal's public link behaves: the shareable URL, an
 * optional passcode, and an optional expiry.
 */
class ClientAccess extends AdminComponent
{
    #[Locked]
    public Proposal $proposal;

    public string $passcode = '';

    public string $expiresAt = '';

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal;
        $this->expiresAt = $proposal->access_expires_at?->format('Y-m-d') ?? '';
    }

    public function setPasscode(): void
    {
        $this->validate([
            'passcode' => ['required', 'string', 'min:4', 'max:255'],
        ], attributes: ['passcode' => 'passcode']);

        // The 'hashed' cast on Proposal handles the hashing.
        $this->proposal->access_password = $this->passcode;
        $this->proposal->save();

        $this->passcode = '';

        $this->dispatch('toast', ...$this->success(['text' => 'Passcode set. Share it with your client separately from the link.']));
    }

    public function clearPasscode(): void
    {
        $this->proposal->access_password = null;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->warning(['text' => 'Passcode removed. Anyone with the link can now view this proposal.']));
    }

    public function saveExpiry(): void
    {
        $this->validate([
            'expiresAt' => ['nullable', 'date', 'after:today'],
        ], attributes: ['expiresAt' => 'expiry date']);

        $this->proposal->access_expires_at = $this->expiresAt !== ''
            ? Carbon::parse($this->expiresAt)->endOfDay()
            : null;
        $this->proposal->save();

        $this->dispatch('toast', ...$this->success([
            'text' => $this->expiresAt !== '' ? 'Expiry date saved.' : 'Expiry removed — the link no longer expires.',
        ]));
    }

    public function render(): View
    {
        return view('livewire.admin.proposals.client-access', [
            'shareUrl' => route('proposal.view', ['proposal' => $this->proposal->uuid]),
        ]);
    }
}
