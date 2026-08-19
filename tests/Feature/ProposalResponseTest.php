<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Proposals\ProposalEdit;
use App\Livewire\Public\ProposalView;
use App\Models\Activity;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\ProposalResponse;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalResponseTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    private function feature(Proposal $proposal, string $name, int $pounds, bool $optional, int $quantity = 1): FinalFeature
    {
        $feature = new FinalFeature([
            'name' => $name,
            'description' => $name.' description.',
            'price' => $pounds,
            'quantity' => $quantity,
            'optional' => $optional,
            'order' => 1,
        ]);
        $feature->proposal()->associate($proposal);
        $feature->save();

        return $feature;
    }

    /**
     * A delivered proposal with £1000 required and two £100 optional extras.
     *
     * @return array{0: Proposal, 1: FinalFeature, 2: FinalFeature}
     */
    private function deliveredProposal(array $attributes = []): array
    {
        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create(array_merge([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
            'status' => Status::DELIVERED,
        ], $attributes));

        $this->feature($proposal, 'Core build', 1000, false);
        $optionalA = $this->feature($proposal, 'Motion identity', 100, true);
        $optionalB = $this->feature($proposal, 'Photography', 100, true);

        return [$proposal, $optionalA, $optionalB];
    }

    private function becomeVisitor(): void
    {
        auth()->logout();
        session()->flush();
        Settings::forget();
    }

    #[Test]
    public function accepting_records_the_selection_and_moves_the_proposal_to_accepted()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id])
            ->assertSet('responded', true);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertNotNull($response);
        $this->assertSame(Status::ACCEPTED, $response->status);
        $this->assertSame([$optionalA->id], $response->selected_feature_ids);
        $this->assertSame($this->tenant->id, $response->tenant_id);
        $this->assertNotNull($response->responded_at);

        // £1000 required + £100 kept optional = £1100, stored in pence.
        $this->assertSame(110000, $response->accepted_total);

        $this->assertSame(Status::ACCEPTED, $proposal->fresh()->status);
    }

    #[Test]
    public function accepting_with_no_optional_extras_totals_the_required_features_only()
    {
        [$proposal] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertSame([], $response->selected_feature_ids);
        $this->assertSame(100000, $response->accepted_total);
    }

    #[Test]
    public function accepting_every_optional_extra_totals_the_lot()
    {
        [$proposal, $optionalA, $optionalB] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id, $optionalB->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertSame(120000, $response->accepted_total);
    }

    #[Test]
    public function quantities_are_honoured_in_the_recomputed_total()
    {
        [$proposal] = $this->deliveredProposal();
        $bulk = $this->feature($proposal, 'Extra pages', 50, true, quantity: 4);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$bulk->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        // £1000 required + (£50 × 4) = £1200.
        $this->assertSame(120000, $response->accepted_total);
    }

    #[Test]
    public function a_tampered_price_in_the_payload_is_ignored()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        // The client controls the payload, so try to smuggle prices through it
        // in every shape the front end might plausibly have sent.
        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [
                ['id' => $optionalA->id, 'price' => 1],
                $optionalA->id,
                '0',
                -1,
            ]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        // Still the database's £1000 + £100, not anything the payload claimed.
        $this->assertSame(110000, $response->accepted_total);
        $this->assertSame([$optionalA->id], $response->selected_feature_ids);
    }

    #[Test]
    public function feature_ids_belonging_to_another_proposal_are_discarded()
    {
        [$proposal] = $this->deliveredProposal();

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
        $otherClient = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $otherProposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $otherClient->id,
            'status' => Status::DELIVERED,
        ]);
        $foreign = $this->feature($otherProposal, 'Someone elses extra', 9999, true);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$foreign->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertSame([], $response->selected_feature_ids);
        $this->assertSame(100000, $response->accepted_total);
    }

    #[Test]
    public function a_required_feature_id_cannot_be_passed_off_as_an_optional_selection()
    {
        [$proposal] = $this->deliveredProposal();
        $required = $proposal->features()->where('optional', false)->first();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$required->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        // Required features are implicit, never listed as a client selection,
        // and must not be double-counted in the total.
        $this->assertSame([], $response->selected_feature_ids);
        $this->assertSame(100000, $response->accepted_total);
    }

    #[Test]
    public function rejecting_records_a_rejection_and_moves_the_proposal()
    {
        [$proposal] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('reject')
            ->assertSet('responded', true);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);

        $this->assertSame(Status::REJECTED, $response->status);
        $this->assertSame(0, $response->accepted_total);
        $this->assertSame(Status::REJECTED, $proposal->fresh()->status);
    }

    #[Test]
    public function a_draft_proposal_cannot_be_responded_to()
    {
        [$proposal, $optionalA] = $this->deliveredProposal(['status' => Status::DRAFT]);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id])
            ->assertSet('responded', false);

        $this->assertSame(0, ProposalResponse::withoutGlobalScopes()->count());
        $this->assertSame(Status::DRAFT, $proposal->fresh()->status);
    }

    #[Test]
    public function a_proposal_cannot_be_answered_twice()
    {
        [$proposal, $optionalA, $optionalB] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id]);

        // A second attempt, this time trying to decline.
        Livewire::test(ProposalView::class, ['proposal' => $proposal->fresh()])
            ->call('reject');

        $this->assertSame(1, ProposalResponse::withoutGlobalScopes()->count());

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $proposal->id);
        $this->assertSame(Status::ACCEPTED, $response->status);
        $this->assertSame(Status::ACCEPTED, $proposal->fresh()->status);
    }

    #[Test]
    public function a_locked_proposal_cannot_be_responded_to()
    {
        [$proposal, $optionalA] = $this->deliveredProposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->assertSet('unlocked', false)
            ->call('accept', [$optionalA->id])
            ->assertSet('responded', false);

        $this->assertSame(0, ProposalResponse::withoutGlobalScopes()->count());
    }

    #[Test]
    public function an_expired_proposal_cannot_be_responded_to()
    {
        [$proposal, $optionalA] = $this->deliveredProposal(['access_expires_at' => now()->subDay()]);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id])
            ->assertSet('responded', false);

        $this->assertSame(0, ProposalResponse::withoutGlobalScopes()->count());
    }

    #[Test]
    public function the_public_view_locks_to_the_recorded_choice_after_responding()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id]);

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Accepted')
            ->assertDontSeeText('Accept proposal')
            ->assertDontSeeText('Decline');
    }

    #[Test]
    public function responding_is_recorded_in_the_activity_feed_without_an_actor()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id]);

        $activity = Activity::withoutGlobalScopes()
            ->where('action', ActivityAction::ProposalAccepted->value)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($this->tenant->id, $activity->tenant_id);
        $this->assertNull($activity->user_id);
        $this->assertSame('The client accepted Brand identity system', $activity->headline());
        $this->assertSame('Proposal', $activity->subjectTypeLabel());
    }

    #[Test]
    public function an_admin_can_mark_a_draft_proposal_as_delivered()
    {
        [$proposal] = $this->deliveredProposal(['status' => Status::DRAFT]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->assertSame(Status::DELIVERED, $proposal->fresh()->status);
    }

    #[Test]
    public function a_proposal_with_no_features_cannot_be_delivered()
    {
        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('markAsDelivered');

        $this->assertSame(Status::DRAFT, $proposal->fresh()->status);
    }

    #[Test]
    public function an_admin_can_reopen_a_responded_proposal()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal->fresh()])
            ->call('reopen');

        $this->assertSame(0, ProposalResponse::withoutGlobalScopes()->count());
        $this->assertSame(Status::DELIVERED, $proposal->fresh()->status);
        $this->assertTrue($proposal->fresh()->isOpenForResponse());
    }

    #[Test]
    public function a_response_is_scoped_to_its_own_tenant()
    {
        [$proposal, $optionalA] = $this->deliveredProposal();
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->call('accept', [$optionalA->id]);

        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id, 'active' => true]);

        $this->actingAs($otherUser)->session(['tenant_id' => $otherTenant->id]);

        $this->assertSame(0, ProposalResponse::count());
    }
}
