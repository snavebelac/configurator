<?php

namespace App\Livewire\Admin\Terms;

use App\Helpers\HtmlSanitiser;
use App\Livewire\Admin\AdminComponent;
use App\Models\Terms;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Edit terms')]
class TermsEdit extends AdminComponent
{
    #[Locked]
    public Terms $terms;

    #[Validate('required|string|max:255')]
    public string $name = '';

    /**
     * The draft body, as HTML from the editor. Never trusted: it is run
     * through HtmlSanitiser on every save.
     */
    public ?string $body = null;

    /**
     * A published version being previewed instead of the draft, if any.
     */
    #[Locked]
    public ?int $viewingVersionId = null;

    public function mount(Terms $terms): void
    {
        $this->terms = $terms;
        $this->name = $terms->name;
        $this->body = $terms->draftOrNew()->body;
    }

    public function saveDraft(HtmlSanitiser $sanitiser): void
    {
        $this->validate();

        $this->terms->name = $this->name;
        $this->terms->save();

        $draft = $this->terms->draftOrNew();
        $draft->body = $sanitiser->sanitise($this->body);
        $draft->save();

        // Reflect what was actually stored — if the sanitiser stripped
        // something, the author should see that immediately rather than
        // discover it after publishing.
        $this->body = $draft->body;

        $this->dispatch('toast', ...$this->success(['text' => 'Draft saved']));
    }

    public function publish(HtmlSanitiser $sanitiser): void
    {
        $this->validate();

        $this->terms->name = $this->name;
        $this->terms->save();

        $draft = $this->terms->draftOrNew();
        $draft->body = $sanitiser->sanitise($this->body);
        $draft->save();

        $this->body = $draft->body;

        if (! $draft->publish()) {
            $this->dispatch('toast', ...$this->warning([
                'text' => 'There\'s nothing to publish — write some terms first.',
            ]));

            return;
        }

        $this->terms->refresh();
        $this->viewingVersionId = null;

        // A fresh draft is seeded from what was just published, so editing can
        // continue without the author starting from a blank page.
        $this->body = $this->terms->draftOrNew()->body;

        $this->dispatch('toast', ...$this->success([
            'text' => "Published {$draft->label()}. New proposals will use it from now on.",
        ]));
    }

    /**
     * Look at a frozen version. Read-only — published bodies never change.
     */
    public function viewVersion(int $versionId): void
    {
        $version = $this->terms->versions()->published()->findOrFail($versionId);

        $this->viewingVersionId = $version->id;
    }

    public function backToDraft(): void
    {
        $this->viewingVersionId = null;
    }

    /**
     * Bring an older version's text back into the draft, so it can be edited
     * and published as a new version. The old version is left untouched.
     */
    public function restoreVersion(int $versionId): void
    {
        $version = $this->terms->versions()->published()->findOrFail($versionId);

        $draft = $this->terms->draftOrNew();
        $draft->body = $version->body;
        $draft->save();

        $this->body = $draft->body;
        $this->viewingVersionId = null;

        $this->dispatch('toast', ...$this->success([
            'text' => "{$version->label()} copied into the draft. Publish to make it current.",
        ]));
    }

    public function makeDefault(): void
    {
        $this->terms->makeDefault();
        $this->terms->refresh();

        $this->dispatch('toast', ...$this->success([
            'text' => 'This set is now the default for new proposals',
        ]));
    }

    public function render(): View
    {
        $versions = $this->terms->versions()
            ->published()
            ->orderByDesc('version')
            ->get();

        return view('livewire.admin.terms.terms-edit', [
            'versions' => $versions,
            'currentVersion' => $versions->first(),
            'viewing' => $this->viewingVersionId !== null
                ? $versions->firstWhere('id', $this->viewingVersionId)
                : null,
            'draft' => $this->terms->draft()->first(),
        ]);
    }
}
