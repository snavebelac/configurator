<?php

namespace Tests\Feature;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Proposals\DeliverProposalModal;
use App\Livewire\Admin\Proposals\ProposalEdit;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Terms;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Marking a proposal delivered turns on the client's share link, so it gets a
 * confirmation restating the three things easiest to get wrong: who it's going
 * to, what it comes to, and which terms it carries.
 */
class DeliverProposalModalTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id, 'active' => true]);
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Halverson Studio',
        ]);
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    private function proposal(): Proposal
    {
        return Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'name' => 'Brand identity system',
            'status' => Status::DRAFT,
        ]);
    }

    private function line(Proposal $proposal, array $attributes): FinalFeature
    {
        $feature = new FinalFeature(array_merge([
            'name' => 'Core build',
            'description' => 'The build.',
            'price' => 0,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
            'pricing_type' => PricingType::Fixed,
        ], $attributes));
        $feature->proposal()->associate($proposal);
        $feature->save();

        return $feature;
    }

    #[Test]
    public function it_restates_the_client_the_total_and_the_terms()
    {
        $terms = new Terms(['name' => 'Standard build']);
        $terms->is_default = true;
        $terms->save();
        $draft = $terms->draftOrNew();
        $draft->body = '<p>The terms.</p>';
        $draft->save();
        $draft->publish();

        $proposal = $this->proposal();
        $this->line($proposal, ['price' => 4000]);
        $proposal->terms_version_id = $terms->fresh()->currentVersion->id;
        $proposal->save();

        Livewire::test(DeliverProposalModal::class, ['proposalId' => $proposal->id])
            ->assertSee('Halverson Studio')
            ->assertSee('4,000.00')
            ->assertSee('Standard build')
            ->assertSee('v1');
    }

    #[Test]
    public function it_keeps_recurring_costs_out_of_the_one_off_total()
    {
        $proposal = $this->proposal();
        $this->line($proposal, ['price' => 4000]);
        $this->line($proposal, [
            'name' => 'Managed hosting',
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        Livewire::test(DeliverProposalModal::class, ['proposalId' => $proposal->id])
            ->assertSee('4,000.00')
            ->assertSee('Per month')
            ->assertSee('50.00')
            // The one-off figure must not have absorbed the monthly fee.
            ->assertDontSee('4,050.00');
    }

    #[Test]
    public function it_warns_when_no_terms_are_attached()
    {
        $proposal = $this->proposal();
        $this->line($proposal, ['price' => 4000]);

        Livewire::test(DeliverProposalModal::class, ['proposalId' => $proposal->id])
            ->assertSee('Nothing attached');
    }

    #[Test]
    public function confirming_asks_the_edit_screen_to_deliver()
    {
        $proposal = $this->proposal();
        $this->line($proposal, ['price' => 4000]);

        Livewire::test(DeliverProposalModal::class, ['proposalId' => $proposal->id])
            ->call('confirm')
            ->assertDispatched('deliverProposalConfirmed')
            ->assertDispatched('closeModal');

        // The modal itself changes nothing — delivery stays with ProposalEdit,
        // which owns the guards.
        $this->assertSame(Status::DRAFT, $proposal->fresh()->status);
    }

    #[Test]
    public function the_edit_screen_delivers_when_the_confirmation_fires()
    {
        $proposal = $this->proposal();
        $this->line($proposal, ['price' => 4000]);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->dispatch('deliverProposalConfirmed');

        $this->assertSame(Status::DELIVERED, $proposal->fresh()->status);
    }

    #[Test]
    public function a_proposal_that_cannot_be_delivered_is_still_refused()
    {
        // No features at all: the confirmation isn't a way around the guard.
        $proposal = $this->proposal();

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->dispatch('deliverProposalConfirmed');

        $this->assertSame(Status::DRAFT, $proposal->fresh()->status);
    }
}
