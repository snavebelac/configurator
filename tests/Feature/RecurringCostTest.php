<?php

namespace Tests\Feature;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Features\FeatureModal;
use App\Livewire\Public\ProposalView;
use App\Models\Client;
use App\Models\Feature;
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

class RecurringCostTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Proposal $proposal;

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

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
            'status' => Status::DELIVERED,
        ]);
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    private function line(string $name, array $attributes = []): FinalFeature
    {
        $feature = new FinalFeature(array_merge([
            'name' => $name,
            'description' => $name.' description.',
            'price' => 0,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
            'pricing_type' => PricingType::Fixed,
        ], $attributes));
        $feature->proposal()->associate($this->proposal);
        $feature->save();

        return $feature;
    }

    private function becomeVisitor(): void
    {
        auth()->logout();
        session()->flush();
        Settings::forget();
    }

    #[Test]
    public function accepting_records_recurring_totals_separately_from_the_one_off()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);
        $this->line('Licence', [
            'price' => 600,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Annually,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(100000, $response->accepted_total);
        $this->assertSame(5000, $response->accepted_monthly);
        $this->assertSame(60000, $response->accepted_annually);
        $this->assertTrue($response->hasRecurring());
    }

    #[Test]
    public function a_declined_recurring_line_is_not_recorded()
    {
        $this->line('Core build', ['price' => 1000]);
        $hosting = $this->line('Hosting', [
            'price' => 50,
            'optional' => true,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(0, $response->accepted_monthly);
        $this->assertNotContains($hosting->id, $response->selected_feature_ids);
    }

    #[Test]
    public function an_accepted_recurring_line_is_recorded()
    {
        $this->line('Core build', ['price' => 1000]);
        $hosting = $this->line('Hosting', [
            'price' => 50,
            'optional' => true,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', [$hosting->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(5000, $response->accepted_monthly);
        $this->assertContains($hosting->id, $response->selected_feature_ids);
    }

    #[Test]
    public function recurring_costs_never_inflate_the_one_off_total()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Hosting', [
            'price' => 500,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        // £1,000, not £1,500.
        $this->assertSame(100000, $response->accepted_total);
    }

    #[Test]
    public function rejecting_records_no_recurring_commitment()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('reject');

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(0, $response->accepted_monthly);
        $this->assertSame(0, $response->accepted_annually);
        $this->assertFalse($response->hasRecurring());
    }

    #[Test]
    public function recurring_lines_render_in_their_own_section()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Ongoing costs')
            ->assertSeeText('Hosting')
            ->assertSeeText('/ month')
            ->assertSeeText('Billed monthly')
            ->assertSeeText('Then, ongoing');
    }

    #[Test]
    public function a_proposal_with_no_recurring_lines_has_no_such_section()
    {
        $this->line('Core build', ['price' => 1000]);

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->assertDontSeeText('Ongoing costs')
            ->assertDontSeeText('Then, ongoing');
    }

    #[Test]
    public function a_recurring_line_stays_out_of_the_itemised_work()
    {
        $this->line('Core build', ['price' => 1000]);
        $hosting = $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();

        $content = $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('group-'.$hosting->id, $content);
    }

    #[Test]
    public function a_recurring_feature_can_be_created_in_the_library()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Hosting')
            ->set('description', 'Managed hosting.')
            ->set('pricingType', PricingType::Recurring->value)
            ->set('price', '50')
            ->set('billingPeriod', BillingPeriod::Monthly->value)
            ->set('quantity', '1')
            ->call('save')
            ->assertHasNoErrors();

        $feature = Feature::firstWhere('name', 'Hosting');

        $this->assertTrue($feature->isRecurring());
        $this->assertFalse($feature->isFixed());
        $this->assertSame(BillingPeriod::Monthly, $feature->billing_period);
        $this->assertSame(50.0, (float) $feature->price);
        $this->assertNull($feature->parent_id);
    }

    #[Test]
    public function a_recurring_feature_needs_a_billing_period()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Hosting')
            ->set('description', 'Managed hosting.')
            ->set('pricingType', PricingType::Recurring->value)
            ->set('price', '50')
            ->set('billingPeriod', '')
            ->set('quantity', '1')
            ->call('save')
            ->assertHasErrors(['billingPeriod' => 'required']);
    }

    #[Test]
    public function switching_a_feature_to_fixed_clears_its_billing_period()
    {
        $feature = Feature::create([
            'name' => 'Hosting',
            'description' => 'Managed hosting.',
            'price' => 50,
            'quantity' => 1,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        Livewire::test(FeatureModal::class, ['featureId' => $feature->id])
            ->set('pricingType', PricingType::Fixed->value)
            ->set('price', '50')
            ->set('quantity', '1')
            ->call('save')
            ->assertHasNoErrors();

        // A stale period on a fixed line would be a contradiction waiting to
        // confuse the pricing calculation.
        $this->assertNull($feature->fresh()->billing_period);
    }

    #[Test]
    public function the_features_list_shows_the_billing_period()
    {
        Feature::create([
            'name' => 'Hosting',
            'description' => 'Managed hosting.',
            'price' => 50,
            'quantity' => 1,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->get(route('dashboard.features'))
            ->assertOk()
            ->assertSeeText('/ month');
    }

    #[Test]
    public function the_admin_sees_the_recurring_commitment_on_the_response()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $this->becomeVisitor();
        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])->call('accept', []);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $this->get(route('dashboard.proposal.edit', ['proposal' => $this->proposal->fresh()]))
            ->assertOk()
            ->assertSeeText('one-off, then')
            ->assertSeeText('/month');
    }

    #[Test]
    public function percentages_alongside_only_recurring_costs_are_still_flagged()
    {
        $this->proposal->status = Status::DRAFT;
        $this->proposal->save();

        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);
        $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
        ]);

        // A percentage takes its share of one-off work, and there isn't any —
        // recurring costs don't count as a base.
        $this->assertTrue($this->proposal->fresh()->percentagesHaveNoBase());
        $this->assertFalse($this->proposal->fresh()->canBeDelivered());
    }

    #[Test]
    public function a_recurring_only_proposal_is_perfectly_deliverable()
    {
        $this->proposal->status = Status::DRAFT;
        $this->proposal->save();

        $this->line('Hosting', [
            'price' => 50,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        // A retainer-only proposal is a legitimate thing to send.
        $this->assertFalse($this->proposal->fresh()->percentagesHaveNoBase());
        $this->assertTrue($this->proposal->fresh()->canBeDelivered());
    }
}
