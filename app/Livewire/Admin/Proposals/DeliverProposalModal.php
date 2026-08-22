<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\BillingPeriod;
use App\Facades\Settings;
use App\Helpers\ProposalPricing;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use LivewireUI\Modal\ModalComponent;

/**
 * The last look before a proposal goes to a client.
 *
 * Marking a proposal delivered is the point of no return in practice — the
 * share link starts working and the client can accept — so the three things
 * that are easiest to get wrong get restated first: who it's going to, what it
 * comes to, and which terms it goes out under. Cancelling returns to the edit
 * screen with everything still changeable.
 */
class DeliverProposalModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return 'lg';
    }

    #[Locked]
    public int $proposalId;

    public Proposal $proposal;

    public function mount(int $proposalId): void
    {
        $this->proposalId = $proposalId;
        $this->proposal = Proposal::with(['client', 'features', 'termsVersion.terms'])
            ->findOrFail($proposalId);
    }

    /**
     * Hand back to ProposalEdit rather than delivering here: it owns the
     * guards and the status change, and having two places that can mark a
     * proposal delivered is one too many.
     */
    public function confirm(): void
    {
        $this->dispatch('deliverProposalConfirmed');
        $this->dispatch('closeModal');
    }

    public function render(): View
    {
        $features = $this->proposal->features;

        // The everything-on figure, matching what the admin sees elsewhere.
        // What a client actually accepts depends on which optional lines they
        // keep, so this is billed as a ceiling rather than a promise.
        $pricing = app(ProposalPricing::class)->calculate(
            $features,
            $features->where('optional', true)->pluck('id')->map(fn ($id) => (int) $id)->all(),
        );

        return view('livewire.admin.proposals.deliver-proposal-modal', [
            'pricing' => $pricing,
            'optionalCount' => $features->where('optional', true)->count(),
            'recurringPeriods' => BillingPeriod::cases(),
            'currency' => Settings::getCurrency()->toSymbol(),
        ]);
    }
}
