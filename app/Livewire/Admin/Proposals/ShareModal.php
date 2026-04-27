<?php

namespace App\Livewire\Admin\Proposals;

use App\Facades\Settings;
use App\Livewire\Admin\AdminComponent;
use App\Models\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use LivewireUI\Modal\ModalComponent;

class ShareModal extends ModalComponent
{
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }

    #[Locked]
    public int $proposalId;

    /**
     * 'YYYY-MM-DD' string, or empty for "never expires".
     */
    #[Validate('nullable|date|after:today')]
    public string $expiresAtDate = '';

    public bool $codeRequired = false;

    /**
     * Plain code shown once immediately after generation.
     */
    public ?string $generatedCode = null;

    public function mount(int $proposalId): void
    {
        $this->proposalId = $proposalId;
        $proposal = Proposal::findOrFail($proposalId);

        if ($proposal->expires_at !== null) {
            $this->expiresAtDate = $proposal->expires_at->toDateString();
        } elseif (($default = Settings::getDefaultShareExpiryDays()) !== null) {
            $this->expiresAtDate = now()->addDays($default)->toDateString();
        }

        $this->codeRequired = $proposal->requiresCode();
    }

    public function save(): void
    {
        $this->validate();

        $proposal = Proposal::findOrFail($this->proposalId);
        $expiresAt = $this->expiresAtDate === ''
            ? null
            : Carbon::parse($this->expiresAtDate)->endOfDay();

        $updates = ['expires_at' => $expiresAt];

        if (! $this->codeRequired) {
            $updates['access_code_hash'] = null;
        } elseif ($this->generatedCode !== null) {
            $updates['access_code_hash'] = Hash::make($this->generatedCode);
        }

        $proposal->update($updates);

        $this->dispatch('toast', ...AdminComponent::success(['text' => 'Share settings updated']));
        $this->dispatch('closeModal');
    }

    public function generateCode(): void
    {
        $this->generatedCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->codeRequired = true;
    }

    public function render()
    {
        $proposal = Proposal::findOrFail($this->proposalId);

        return view('livewire.admin.proposals.share-modal', [
            'proposal' => $proposal,
            'shareUrl' => route('proposal.share', ['uuid' => $proposal->uuid]),
        ]);
    }
}
