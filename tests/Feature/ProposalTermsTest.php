<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Proposals\ProposalCreate;
use App\Livewire\Admin\Proposals\ProposalEdit;
use App\Livewire\Public\ProposalView;
use App\Models\Client;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\ProposalResponse;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Terms;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalTermsTest extends TestCase
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
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    private function termsSet(string $name, string $body, bool $default = true): Terms
    {
        $terms = new Terms(['name' => $name]);
        $terms->is_default = $default;
        $terms->save();

        $draft = $terms->draftOrNew();
        $draft->body = $body;
        $draft->save();
        $draft->publish();

        return $terms->fresh();
    }

    private function proposal(array $attributes = []): Proposal
    {
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create(array_merge([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
            'status' => Status::DRAFT,
        ], $attributes));

        $feature = new FinalFeature([
            'name' => 'Core build',
            'description' => 'The build.',
            'price' => 1000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $feature->proposal()->associate($proposal);
        $feature->save();

        return $proposal;
    }

    /**
     * Attach a set and send. Delivery no longer reaches for the default set on
     * its own, so anything that wants pinned terms has to say so — which is
     * what the create screen and the delivery confirmation both do.
     */
    private function deliverUnder(Proposal $proposal, Terms $terms): void
    {
        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('setTermsVersion', $terms->currentVersion->id)
            ->call('markAsDelivered');
    }

    private function becomeVisitor(): void
    {
        auth()->logout();
        session()->flush();
        Settings::forget();
    }

    #[Test]
    public function creating_a_proposal_attaches_the_default_sets_current_version()
    {
        $terms = $this->termsSet('Standard build', '<p>The terms.</p>');
        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(ProposalCreate::class)
            ->assertSet('termsVersionId', $terms->currentVersion->id)
            ->set('name', 'Brand identity system')
            ->set('clientId', $client->id)
            ->set('selectedFeatureIds', [$feature->id])
            ->call('createProposal');

        $proposal = Proposal::firstWhere('name', 'Brand identity system');

        $this->assertSame($terms->currentVersion->id, $proposal->terms_version_id);
    }

    #[Test]
    public function the_most_recently_published_set_is_used_when_none_is_default()
    {
        $this->termsSet('Older set', '<p>Older.</p>', default: false);
        $newer = $this->termsSet('Newer set', '<p>Newer.</p>', default: false);

        Livewire::test(ProposalCreate::class)
            ->assertSet('termsVersionId', $newer->currentVersion->id);
    }

    #[Test]
    public function an_author_can_choose_a_different_set_when_creating()
    {
        $this->termsSet('Standard build', '<p>Standard.</p>');
        $retainer = $this->termsSet('Retainer', '<p>Retainer.</p>', default: false);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(ProposalCreate::class)
            ->set('termsVersionId', $retainer->currentVersion->id)
            ->set('name', 'Retained work')
            ->set('clientId', $client->id)
            ->set('selectedFeatureIds', [$feature->id])
            ->call('createProposal');

        $this->assertSame(
            $retainer->currentVersion->id,
            Proposal::firstWhere('name', 'Retained work')->terms_version_id,
        );
    }

    #[Test]
    public function delivering_no_longer_attaches_terms_by_itself()
    {
        // The default set exists, but this proposal was never given terms.
        // Silently attaching them here would contradict the delivery
        // confirmation the user just agreed to.
        $this->termsSet('Standard build', '<p>The terms.</p>');
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->assertNull($proposal->fresh()->terms_version_id);
        $this->assertSame(Status::DELIVERED, $proposal->fresh()->status);
    }

    #[Test]
    public function editing_the_terms_afterwards_does_not_change_what_was_sent()
    {
        $terms = $this->termsSet('Standard build', '<p>Original terms.</p>');
        $proposal = $this->proposal();

        $this->deliverUnder($proposal, $terms);

        $pinned = $proposal->fresh()->terms_version_id;

        // Publish a second version after the proposal went out.
        $draft = $terms->fresh()->draftOrNew();
        $draft->body = '<p>Revised terms.</p>';
        $draft->save();
        $draft->publish();

        // The proposal still points at v1, with its original wording.
        $this->assertSame($pinned, $proposal->fresh()->terms_version_id);
        $this->assertSame('<p>Original terms.</p>', $proposal->fresh()->termsVersion->body);
        $this->assertSame(2, $terms->fresh()->currentVersion->version);
    }

    #[Test]
    public function delivering_without_any_published_terms_pins_nothing()
    {
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->assertNull($proposal->fresh()->terms_version_id);
        $this->assertSame(Status::DELIVERED, $proposal->fresh()->status);
    }

    #[Test]
    public function a_draft_only_set_is_never_pinned()
    {
        $terms = new Terms(['name' => 'Unpublished']);
        $terms->is_default = true;
        $terms->save();
        $draft = $terms->draftOrNew();
        $draft->body = '<p>Not published.</p>';
        $draft->save();

        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->assertNull($proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function an_admin_can_override_which_set_a_proposal_uses()
    {
        $this->termsSet('Standard build', '<p>Standard.</p>');
        $retainer = $this->termsSet('Retainer', '<p>Retainer terms.</p>', default: false);

        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('setTermsVersion', $retainer->currentVersion->id);

        $this->assertSame($retainer->currentVersion->id, $proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function an_override_survives_delivery()
    {
        $this->termsSet('Standard build', '<p>Standard.</p>');
        $retainer = $this->termsSet('Retainer', '<p>Retainer terms.</p>', default: false);

        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('setTermsVersion', $retainer->currentVersion->id)
            ->call('markAsDelivered');

        $this->assertSame($retainer->currentVersion->id, $proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function terms_can_be_removed_from_a_proposal()
    {
        $terms = $this->termsSet('Standard build', '<p>Standard.</p>');
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('setTermsVersion', $terms->currentVersion->id)
            ->call('setTermsVersion', null);

        $this->assertNull($proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function an_unpublished_version_cannot_be_attached()
    {
        $terms = new Terms(['name' => 'Unpublished']);
        $terms->is_default = true;
        $terms->save();
        $draft = $terms->draftOrNew();
        $draft->body = '<p>Not published.</p>';
        $draft->save();

        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('setTermsVersion', $draft->id);

        $this->assertNull($proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function the_terms_appear_on_the_client_facing_proposal()
    {
        $terms = $this->termsSet('Standard build', '<p>Payment is due within 30 days.</p>');
        $proposal = $this->proposal();

        $this->deliverUnder($proposal, $terms);

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Standard build')
            ->assertSeeText('Payment is due within 30 days.')
            ->assertSeeText('v1');
    }

    #[Test]
    public function a_proposal_with_no_terms_shows_no_terms_section()
    {
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertDontSeeText('Accepting this proposal accepts these terms.');
    }

    #[Test]
    public function accepting_records_the_terms_version_in_force()
    {
        $terms = $this->termsSet('Standard build', '<p>Original terms.</p>');
        $proposal = $this->proposal();

        $this->deliverUnder($proposal, $terms);

        $pinned = $proposal->fresh()->terms_version_id;

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal->fresh()])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertSame($pinned, $response->terms_version_id);
        $this->assertSame('<p>Original terms.</p>', $response->termsVersion->body);
    }

    #[Test]
    public function reopening_and_re_sending_under_new_terms_does_not_move_the_recorded_answer()
    {
        $terms = $this->termsSet('Standard build', '<p>Original terms.</p>');
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $v1 = $proposal->fresh()->terms_version_id;

        $this->becomeVisitor();
        Livewire::test(ProposalView::class, ['proposal' => $proposal->fresh()])
            ->call('accept', []);

        $firstResponseVersion = ProposalResponse::withoutGlobalScopes()
            ->firstWhere('proposal_id', $proposal->id)->terms_version_id;

        // Back in the admin: publish new terms, reopen, re-pin, re-send.
        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $draft = $terms->fresh()->draftOrNew();
        $draft->body = '<p>Revised terms.</p>';
        $draft->save();
        $draft->publish();
        $v2 = $draft->fresh();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal->fresh()])
            ->call('reopen')
            ->call('setTermsVersion', $v2->id);

        $this->assertSame($v1, $firstResponseVersion);
        $this->assertNotSame($v1, $proposal->fresh()->terms_version_id);
        $this->assertSame($v2->id, $proposal->fresh()->terms_version_id);
    }

    #[Test]
    public function a_version_attached_to_a_proposal_reports_itself_as_in_use()
    {
        $terms = $this->termsSet('Standard build', '<p>Terms.</p>');
        $version = $terms->currentVersion;

        $this->assertFalse($version->isInUse());

        $proposal = $this->proposal();
        $this->deliverUnder($proposal, $terms);

        $this->assertTrue(TermsVersion::withoutGlobalScopes()->find($version->id)->isInUse());
    }
}
