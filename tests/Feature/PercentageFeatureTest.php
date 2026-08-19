<?php

namespace Tests\Feature;

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

class PercentageFeatureTest extends TestCase
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
    public function accepting_records_a_total_that_includes_the_percentage()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        // £1,000 + 10% = £1,100.
        $this->assertSame(110000, $response->accepted_total);
    }

    #[Test]
    public function the_recorded_percentage_follows_what_the_client_actually_kept()
    {
        $this->line('Core build', ['price' => 1000]);
        $extra = $this->line('Motion identity', ['price' => 500, 'optional' => true]);
        $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', [$extra->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        // £1,500 base + 10% = £1,650 — the percentage grew with the selection.
        $this->assertSame(165000, $response->accepted_total);
    }

    #[Test]
    public function an_optional_percentage_can_be_declined()
    {
        $this->line('Core build', ['price' => 1000]);
        $pm = $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
            'optional' => true,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', []);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(100000, $response->accepted_total);
        $this->assertNotContains($pm->id, $response->selected_feature_ids);
    }

    #[Test]
    public function an_optional_percentage_can_be_accepted()
    {
        $this->line('Core build', ['price' => 1000]);
        $pm = $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
            'optional' => true,
        ]);

        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', [$pm->id]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(110000, $response->accepted_total);
        $this->assertContains($pm->id, $response->selected_feature_ids);
    }

    #[Test]
    public function a_tampered_percentage_rate_in_the_payload_is_ignored()
    {
        $this->line('Core build', ['price' => 1000]);
        $pm = $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
            'optional' => true,
        ]);

        $this->becomeVisitor();

        // The client controls the payload; the rate is never read from it.
        Livewire::test(ProposalView::class, ['proposal' => $this->proposal])
            ->call('accept', [
                ['id' => $pm->id, 'rate' => 1],
                $pm->id,
            ]);

        $response = ProposalResponse::withoutGlobalScopes()->firstWhere('proposal_id', $this->proposal->id);

        $this->assertSame(110000, $response->accepted_total);
    }

    #[Test]
    public function percentage_lines_render_in_their_own_section_on_the_client_view()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1250,
        ]);

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Applied to the total')
            ->assertSeeText('Project management')
            ->assertSeeText('12.5%')
            ->assertSeeText('12.5% of the selected work');
    }

    #[Test]
    public function a_proposal_with_no_percentage_lines_has_no_such_section()
    {
        $this->line('Core build', ['price' => 1000]);

        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->assertDontSeeText('Applied to the total');
    }

    #[Test]
    public function a_percentage_line_is_kept_out_of_the_itemised_feature_list()
    {
        $this->line('Core build', ['price' => 1000]);
        $pm = $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
        ]);

        $this->becomeVisitor();

        $content = $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->getContent();

        // It appears once in its own section and once in the summary rail,
        // but never as a group heading in the main document.
        $this->assertStringNotContainsString('group-'.$pm->id, $content);
    }

    #[Test]
    public function the_admin_total_includes_percentage_lines()
    {
        $this->line('Core build', ['price' => 1000]);
        $this->line('Project management', [
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1000,
        ]);

        $this->assertSame(1100.0, (float) $this->proposal->fresh()->total());
    }

    #[Test]
    public function a_percentage_feature_can_be_created_in_the_library()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Project management')
            ->set('description', 'Running the work.')
            ->set('pricingType', PricingType::Percentage->value)
            ->set('percentage', '12.5')
            ->call('save')
            ->assertHasNoErrors();

        $feature = Feature::firstWhere('name', 'Project management');

        $this->assertTrue($feature->isPercentage());
        $this->assertSame(1250, $feature->percentage_rate);
        $this->assertSame(12.5, $feature->percentage);
        // Price, quantity and parent are meaningless here and are zeroed
        // rather than left holding stale values.
        $this->assertSame(0, $feature->getAttributes()['price']);
        $this->assertSame(1, $feature->quantity);
        $this->assertNull($feature->parent_id);
    }

    #[Test]
    public function the_features_list_shows_a_rate_rather_than_a_zero_price()
    {
        Feature::create([
            'name' => 'Project management',
            'description' => 'Running the work.',
            'price' => 0,
            'quantity' => 1,
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => 1250,
        ]);

        $this->get(route('dashboard.features'))
            ->assertOk()
            ->assertSeeText('12.5%')
            ->assertSeeText('of total')
            // £0.00 would read as free rather than proportional.
            ->assertDontSeeText('£0.00');
    }

    #[Test]
    public function a_percentage_feature_requires_a_rate_above_zero()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Project management')
            ->set('description', 'Running the work.')
            ->set('pricingType', PricingType::Percentage->value)
            ->set('percentage', '0')
            ->call('save')
            ->assertHasErrors(['percentage' => 'gt']);
    }

    #[Test]
    public function a_percentage_feature_cannot_exceed_a_hundred_percent()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Project management')
            ->set('description', 'Running the work.')
            ->set('pricingType', PricingType::Percentage->value)
            ->set('percentage', '101')
            ->call('save')
            ->assertHasErrors(['percentage' => 'max']);
    }

    #[Test]
    public function a_fixed_feature_still_requires_a_price_and_quantity()
    {
        Livewire::test(FeatureModal::class)
            ->set('name', 'Core build')
            ->set('description', 'The build.')
            ->set('pricingType', PricingType::Fixed->value)
            ->set('price', '')
            ->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['price' => 'required', 'quantity' => 'required']);
    }
}
