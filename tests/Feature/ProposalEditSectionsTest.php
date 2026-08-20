<?php

namespace Tests\Feature;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Proposals\ProposalEdit;
use App\Livewire\Admin\Proposals\ProposalFeatureForm;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The proposal edit screen groups its lines by pricing model, the way the
 * client-facing view does. Before this, a percentage line sat among the fixed
 * work showing a £0.00 unit price and line total, and a recurring line looked
 * like a one-off charge.
 */
class ProposalEditSectionsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $this->tenant->id, 'active' => true]);
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($user)->session(['tenant_id' => $this->tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    /**
     * Prices go in as currency units — the model's mutator converts to pence.
     */
    private function line(array $attributes): FinalFeature
    {
        $feature = new FinalFeature(array_merge([
            'name' => 'A line',
            'description' => 'A line description.',
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

    #[Test]
    public function it_keeps_each_pricing_model_in_its_own_section(): void
    {
        $fixed = $this->line(['name' => 'Core build', 'price' => 4000]);
        $percentage = $this->line([
            'name' => 'Project management',
            'pricing_type' => PricingType::Percentage,
            'price' => 0,
            'percentage_rate' => 1250,
        ]);
        $recurring = $this->line([
            'name' => 'Managed hosting',
            'pricing_type' => PricingType::Recurring,
            'price' => 50,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        $component = Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal]);

        $groupedIds = collect($component->viewData('featureGroups'))
            ->pluck('root.id')
            ->all();

        $this->assertSame([$fixed->id], $groupedIds, 'Only fixed work belongs in the sortable group.');
        $this->assertSame([$percentage->id], $component->viewData('percentageFeatures')->pluck('id')->all());
        $this->assertSame([$recurring->id], $component->viewData('recurringFeatures')->pluck('id')->all());

        $component
            ->assertSee('Percentage of the work')
            ->assertSee('Ongoing costs');
    }

    #[Test]
    public function it_hands_a_percentage_row_the_base_it_takes_its_share_of(): void
    {
        $this->line(['name' => 'Core build', 'price' => 4000]);
        $this->line(['name' => 'Extra', 'price' => 1000, 'optional' => true]);

        $component = Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal]);

        // Everything-on basis, matching the running total at the foot of the
        // card: £4,000 + £1,000.
        $this->assertSame(500000, $component->viewData('fixedBase'));
    }

    #[Test]
    public function a_percentage_row_shows_its_amount_rather_than_a_zero_price(): void
    {
        $percentage = $this->line([
            'name' => 'Project management',
            'pricing_type' => PricingType::Percentage,
            'price' => 0,
            'percentage_rate' => 1250,
        ]);

        Livewire::test(ProposalFeatureForm::class, [
            'finalFeatureId' => $percentage->id,
            'fixedBase' => 500000,
        ])
            ->assertSet('pricingType', PricingType::Percentage->value)
            // 12.5% of £5,000.
            ->assertSee('625.00')
            ->assertDontSee('0.00');
    }

    #[Test]
    public function a_recurring_row_can_change_its_billing_period(): void
    {
        $recurring = $this->line([
            'name' => 'Managed hosting',
            'pricing_type' => PricingType::Recurring,
            'price' => 50,
            'billing_period' => BillingPeriod::Monthly,
        ]);

        Livewire::test(ProposalFeatureForm::class, ['finalFeatureId' => $recurring->id])
            ->assertSet('billingPeriod', BillingPeriod::Monthly->value)
            ->set('billingPeriod', BillingPeriod::Annually->value);

        $this->assertSame(BillingPeriod::Annually, $recurring->fresh()->billing_period);
    }

    #[Test]
    public function a_percentage_row_can_change_its_rate(): void
    {
        $percentage = $this->line([
            'pricing_type' => PricingType::Percentage,
            'price' => 0,
            'percentage_rate' => 1250,
        ]);

        Livewire::test(ProposalFeatureForm::class, ['finalFeatureId' => $percentage->id])
            ->assertSet('percentage', 12.5)
            ->set('percentage', 15);

        $this->assertSame(1500, $percentage->fresh()->percentage_rate);
    }

    #[Test]
    public function reordering_ignores_lines_that_are_not_fixed_work(): void
    {
        $first = $this->line(['name' => 'Alpha', 'price' => 1, 'order' => 1]);
        $second = $this->line(['name' => 'Beta', 'price' => 1, 'order' => 2]);
        // A percentage line sorted between them would previously have taken up
        // a slot in the reorder list and shifted the dropped position.
        $percentage = $this->line([
            'name' => 'Project management',
            'pricing_type' => PricingType::Percentage,
            'price' => 0,
            'percentage_rate' => 1000,
            'order' => 2,
        ]);

        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->call('reorderParents', $second->id, 0);

        $this->assertSame(1, $second->fresh()->order);
        $this->assertSame(2, $first->fresh()->order);
        $this->assertSame(2, $percentage->fresh()->order, 'A percentage line has no position to move.');
    }
}
