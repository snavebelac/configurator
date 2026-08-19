<?php

namespace Tests\Feature;

use App\Livewire\Admin\Terms\TermsEdit;
use App\Livewire\Admin\Terms\TermsList;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\Terms;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
    }

    private function set(string $name = 'Standard build', bool $default = true): Terms
    {
        $terms = new Terms(['name' => $name]);
        $terms->is_default = $default;
        $terms->save();

        return $terms;
    }

    private function publishVersion(Terms $terms, string $body): TermsVersion
    {
        $draft = $terms->draftOrNew();
        $draft->body = $body;
        $draft->save();
        $draft->publish();

        return $draft->fresh();
    }

    #[Test]
    public function the_terms_list_renders()
    {
        $this->set();

        $this->get(route('dashboard.terms'))
            ->assertOk()
            ->assertSeeText('Standard build')
            ->assertSeeText('Each set is versioned.');
    }

    #[Test]
    public function the_edit_page_renders_with_the_editor_mounted()
    {
        $terms = $this->set();
        $this->publishVersion($terms, '<p>Version one.</p>');

        $this->get(route('dashboard.terms.edit', ['terms' => $terms]))
            ->assertOk()
            ->assertSeeText('Standard build')
            ->assertSee('x-data="richText(', escape: false)
            // wire:ignore matters: without it Livewire would morph the editor's
            // DOM out from under TipTap on every round trip.
            ->assertSee('wire:ignore', escape: false)
            ->assertSeeText('Draft')
            ->assertSeeText('v1');
    }

    #[Test]
    public function the_first_set_a_tenant_creates_becomes_their_default()
    {
        Livewire::test(TermsList::class)
            ->set('name', 'Standard build')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertTrue(Terms::firstWhere('name', 'Standard build')->is_default);

        Livewire::test(TermsList::class)
            ->set('name', 'Retainer')
            ->call('create');

        $this->assertFalse(Terms::firstWhere('name', 'Retainer')->is_default);
    }

    #[Test]
    public function a_tenant_has_exactly_one_default_set()
    {
        $first = $this->set('Standard build');
        $second = $this->set('Retainer', default: false);

        Livewire::test(TermsList::class)->call('makeDefault', $second->id);

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, Terms::where('is_default', true)->count());
    }

    #[Test]
    public function editing_starts_from_a_draft_seeded_by_the_current_version()
    {
        $terms = $this->set();
        $this->publishVersion($terms, '<p>Version one.</p>');

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->assertSet('body', '<p>Version one.</p>');
    }

    #[Test]
    public function a_draft_can_be_saved_without_publishing()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p>Work in progress.</p>')
            ->call('saveDraft')
            ->assertHasNoErrors();

        $this->assertSame(0, $terms->publishedVersions()->count());
        $this->assertSame('<p>Work in progress.</p>', $terms->draft()->first()->body);
    }

    #[Test]
    public function publishing_freezes_the_draft_as_a_numbered_version()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p>Version one.</p>')
            ->call('publish')
            ->assertHasNoErrors();

        $version = $terms->currentVersion()->first();

        $this->assertNotNull($version);
        $this->assertSame(1, $version->version);
        $this->assertNotNull($version->published_at);
        $this->assertSame('<p>Version one.</p>', $version->body);
    }

    #[Test]
    public function publishing_again_mints_the_next_version_and_leaves_the_old_one_alone()
    {
        $terms = $this->set();
        $v1 = $this->publishVersion($terms, '<p>Version one.</p>');

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p>Version two.</p>')
            ->call('publish')
            ->assertHasNoErrors();

        $terms->refresh();
        $v2 = $terms->currentVersion()->first();

        $this->assertSame(2, $v2->version);
        $this->assertSame('<p>Version two.</p>', $v2->body);

        // The whole point: v1's text is untouched.
        $this->assertSame('<p>Version one.</p>', $v1->fresh()->body);
    }

    #[Test]
    public function an_empty_draft_cannot_be_published()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '')
            ->call('publish');

        $this->assertSame(0, $terms->publishedVersions()->count());
    }

    #[Test]
    public function a_draft_containing_only_hostile_markup_cannot_be_published()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<script>alert(1)</script>')
            ->call('publish');

        // Sanitising leaves nothing, so there is nothing to publish.
        $this->assertSame(0, $terms->publishedVersions()->count());
    }

    #[Test]
    public function the_body_is_sanitised_on_save()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p onclick="alert(1)">Scope</p><script>alert(1)</script>')
            ->call('publish')
            ->assertHasNoErrors();

        $stored = $terms->currentVersion()->first()->body;

        $this->assertStringNotContainsString('script', $stored);
        $this->assertStringNotContainsString('onclick', $stored);
        $this->assertStringContainsString('Scope', $stored);
    }

    #[Test]
    public function the_editor_is_shown_what_was_actually_stored_after_sanitising()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p>Scope</p><script>alert(1)</script>')
            ->call('saveDraft')
            ->assertSet('body', '<p>Scope</p>');
    }

    #[Test]
    public function publishing_leaves_a_fresh_draft_seeded_from_what_was_published()
    {
        $terms = $this->set();

        Livewire::test(TermsEdit::class, ['terms' => $terms])
            ->set('body', '<p>Version one.</p>')
            ->call('publish')
            ->assertSet('body', '<p>Version one.</p>');

        $this->assertNotNull($terms->draft()->first());
        $this->assertSame(2, $terms->draft()->first()->version);
    }

    #[Test]
    public function an_older_version_can_be_restored_into_the_draft()
    {
        $terms = $this->set();
        $v1 = $this->publishVersion($terms, '<p>Version one.</p>');
        $this->publishVersion($terms->fresh(), '<p>Version two.</p>');

        Livewire::test(TermsEdit::class, ['terms' => $terms->fresh()])
            ->call('restoreVersion', $v1->id)
            ->assertSet('body', '<p>Version one.</p>');

        // Restoring copies — it doesn't roll the published history back.
        $this->assertSame(2, $terms->fresh()->currentVersion()->first()->version);
    }

    #[Test]
    public function a_set_in_use_by_a_proposal_cannot_be_deleted()
    {
        $terms = $this->set();
        $version = $this->publishVersion($terms, '<p>Terms.</p>');

        $proposal = Proposal::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal->terms_version_id = $version->id;
        $proposal->save();

        Livewire::test(TermsList::class)->call('delete', $terms->id);

        $this->assertNotNull($terms->fresh());
    }

    #[Test]
    public function an_unused_set_can_be_deleted_and_the_default_moves_on()
    {
        $first = $this->set('Standard build');
        $second = $this->set('Retainer', default: false);

        Livewire::test(TermsList::class)->call('delete', $first->id);

        $this->assertNull($first->fresh());
        $this->assertTrue($second->fresh()->is_default);
    }

    #[Test]
    public function terms_are_scoped_to_their_tenant()
    {
        $this->set('Ours');

        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id, 'active' => true]);

        $this->actingAs($otherUser)->session(['tenant_id' => $otherTenant->id]);

        $this->assertSame(0, Terms::count());

        $this->get(route('dashboard.terms'))
            ->assertOk()
            ->assertDontSeeText('Ours');
    }
}
