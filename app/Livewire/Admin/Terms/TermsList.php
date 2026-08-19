<?php

namespace App\Livewire\Admin\Terms;

use App\Livewire\Admin\AdminComponent;
use App\Models\Terms;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Terms & conditions')]
class TermsList extends AdminComponent
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public bool $creating = false;

    public function startCreating(): void
    {
        $this->creating = true;
        $this->name = '';
        $this->resetErrorBag();
    }

    public function cancelCreating(): void
    {
        $this->creating = false;
        $this->name = '';
        $this->resetErrorBag();
    }

    public function create(): void
    {
        $this->validate();

        $terms = new Terms(['name' => $this->name]);
        // The first set a tenant creates becomes their default, so proposals
        // have something to pin without anyone having to think about it.
        $terms->is_default = ! Terms::query()->exists();
        $terms->save();

        $this->creating = false;
        $this->name = '';

        $this->redirect(route('dashboard.terms.edit', ['terms' => $terms]), navigate: false);
    }

    public function makeDefault(int $termsId): void
    {
        $terms = Terms::findOrFail($termsId);
        $terms->makeDefault();

        $this->dispatch('toast', ...$this->success([
            'text' => "“{$terms->name}” is now the default for new proposals",
        ]));
    }

    public function delete(int $termsId): void
    {
        $terms = Terms::withCount('versions')->findOrFail($termsId);

        // Deleting a set would orphan the versions that delivered proposals
        // are pinned to, and with them the record of what was agreed.
        $inUse = $terms->versions()
            ->whereHas('proposals')
            ->exists();

        if ($inUse) {
            $this->dispatch('toast', ...$this->warning([
                'text' => 'That set is attached to a proposal, so it can\'t be deleted.',
            ]));

            return;
        }

        $wasDefault = $terms->is_default;
        $terms->versions()->delete();
        $terms->delete();

        // Never leave a tenant without a default while sets still exist.
        if ($wasDefault) {
            Terms::query()->first()?->makeDefault();
        }

        $this->dispatch('toast', ...$this->success(['text' => 'Terms set deleted']));
    }

    public function render(): View
    {
        return view('livewire.admin.terms.terms-list', [
            'termsSets' => Terms::query()
                ->withCount(['versions', 'publishedVersions'])
                ->with('currentVersion')
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
