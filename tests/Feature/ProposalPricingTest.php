<?php

namespace Tests\Feature;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Helpers\ProposalPricing;
use App\Models\FinalFeature;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The pricing maths, in isolation. No database — these are pure calculations
 * over unsaved models, so the rules stay legible.
 */
class ProposalPricingTest extends TestCase
{
    private ProposalPricing $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricing = new ProposalPricing;
    }

    private function fixed(int $id, float $pounds, bool $optional = false, int $quantity = 1): FinalFeature
    {
        $feature = new FinalFeature([
            'name' => 'Fixed '.$id,
            'description' => '',
            'price' => $pounds,
            'quantity' => $quantity,
            'optional' => $optional,
            'pricing_type' => PricingType::Fixed,
        ]);
        $feature->id = $id;

        return $feature;
    }

    private function percentage(int $id, float $percent, bool $optional = false): FinalFeature
    {
        $feature = new FinalFeature([
            'name' => 'Percentage '.$id,
            'description' => '',
            'price' => 0,
            'quantity' => 1,
            'optional' => $optional,
            'pricing_type' => PricingType::Percentage,
            'percentage_rate' => (int) round($percent * 100),
        ]);
        $feature->id = $id;

        return $feature;
    }

    private function recurring(int $id, float $pounds, string $period, bool $optional = false, int $quantity = 1): FinalFeature
    {
        $feature = new FinalFeature([
            'name' => 'Recurring '.$id,
            'description' => '',
            'price' => $pounds,
            'quantity' => $quantity,
            'optional' => $optional,
            'pricing_type' => PricingType::Recurring,
            'billing_period' => $period,
        ]);
        $feature->id = $id;

        return $feature;
    }

    /** @param array<int, FinalFeature> $features */
    private function calc(array $features, array $selected = []): array
    {
        return $this->pricing->calculate(new Collection($features), $selected);
    }

    #[Test]
    public function a_percentage_takes_its_share_of_the_fixed_lines()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->percentage(2, 10),
        ]);

        $this->assertSame(100000, $result['fixedBase']);
        $this->assertSame(10000, $result['percentageTotal']);
        $this->assertSame(110000, $result['subtotal']);
    }

    #[Test]
    public function it_only_counts_optional_lines_the_client_kept()
    {
        $features = [
            $this->fixed(1, 1000),
            $this->fixed(2, 200, optional: true),
            $this->fixed(3, 300, optional: true),
            $this->percentage(4, 10),
        ];

        // Nothing optional kept: 10% of £1,000.
        $this->assertSame(110000, $this->calc($features)['subtotal']);

        // One kept: 10% of £1,200.
        $this->assertSame(132000, $this->calc($features, [2])['subtotal']);

        // Both kept: 10% of £1,500.
        $this->assertSame(165000, $this->calc($features, [2, 3])['subtotal']);
    }

    #[Test]
    public function percentages_never_compound_with_each_other()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->percentage(2, 10),
            $this->percentage(3, 5),
        ]);

        // Both off the same £1,000 base: £100 + £50. Compounding would make
        // the second £55 and the answer would depend on line order.
        $this->assertSame(100000, $result['fixedBase']);
        $this->assertSame(15000, $result['percentageTotal']);
        $this->assertSame(115000, $result['subtotal']);
    }

    #[Test]
    public function an_optional_percentage_only_counts_when_kept()
    {
        $features = [
            $this->fixed(1, 1000),
            $this->percentage(2, 10, optional: true),
        ];

        $this->assertSame(100000, $this->calc($features)['subtotal']);
        $this->assertSame(110000, $this->calc($features, [2])['subtotal']);
    }

    #[Test]
    public function quantity_multiplies_fixed_lines_but_is_ignored_for_percentages()
    {
        $result = $this->calc([
            $this->fixed(1, 50, quantity: 4),
            $this->percentage(2, 10),
        ]);

        $this->assertSame(20000, $result['fixedBase']);
        $this->assertSame(2000, $result['percentageTotal']);
    }

    #[Test]
    public function fractional_percentages_round_to_the_nearest_penny()
    {
        // 12.5% of £999.99 = £124.99875 -> 12500 pence (rounded half up).
        $result = $this->calc([
            $this->fixed(1, 999.99),
            $this->percentage(2, 12.5),
        ]);

        $this->assertSame(99999, $result['fixedBase']);
        $this->assertSame(12500, $result['percentageTotal']);
    }

    #[Test]
    public function a_proposal_of_only_percentages_comes_to_nothing()
    {
        // Nothing to take a share of, so nothing is charged — rather than
        // some undefined or divide-by-zero behaviour.
        $result = $this->calc([
            $this->percentage(1, 10),
            $this->percentage(2, 5),
        ]);

        $this->assertSame(0, $result['fixedBase']);
        $this->assertSame(0, $result['percentageTotal']);
        $this->assertSame(0, $result['subtotal']);
    }

    #[Test]
    public function a_proposal_with_no_percentages_behaves_exactly_as_before()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->fixed(2, 200, optional: true),
        ], [2]);

        $this->assertSame(120000, $result['subtotal']);
        $this->assertSame(0, $result['percentageTotal']);
        $this->assertCount(0, $result['percentageLines']);
    }

    #[Test]
    public function each_percentage_line_reports_its_own_amount()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->percentage(2, 10),
            $this->percentage(3, 5),
        ]);

        $amounts = $result['percentageLines']->pluck('amount')->all();

        $this->assertSame([10000, 5000], $amounts);
    }

    #[Test]
    public function recurring_lines_total_per_period_and_stay_out_of_the_one_off_figure()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->recurring(2, 50, BillingPeriod::Monthly->value),
            $this->recurring(3, 600, BillingPeriod::Annually->value),
        ]);

        // The one-off figure is untouched by the ongoing costs.
        $this->assertSame(100000, $result['fixedBase']);
        $this->assertSame(100000, $result['subtotal']);

        $this->assertSame(5000, $result['recurring'][BillingPeriod::Monthly->value]);
        $this->assertSame(60000, $result['recurring'][BillingPeriod::Annually->value]);
        $this->assertTrue($result['hasRecurring']);
    }

    #[Test]
    public function periods_are_never_converted_into_one_another()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->recurring(2, 50, BillingPeriod::Monthly->value),
        ]);

        // £50/month is not silently reported as £600/year.
        $this->assertSame(5000, $result['recurring'][BillingPeriod::Monthly->value]);
        $this->assertSame(0, $result['recurring'][BillingPeriod::Annually->value]);
    }

    #[Test]
    public function a_percentage_ignores_recurring_lines_entirely()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->recurring(2, 500, BillingPeriod::Monthly->value),
            $this->percentage(3, 10),
        ]);

        // 10% of £1,000, not of £1,500 — the monthly charge is a separate
        // commitment and isn't work to be managed.
        $this->assertSame(10000, $result['percentageTotal']);
    }

    #[Test]
    public function an_optional_recurring_line_only_counts_when_kept()
    {
        $features = [
            $this->fixed(1, 1000),
            $this->recurring(2, 50, BillingPeriod::Monthly->value, optional: true),
        ];

        $this->assertSame(0, $this->calc($features)['recurring'][BillingPeriod::Monthly->value]);
        $this->assertSame(5000, $this->calc($features, [2])['recurring'][BillingPeriod::Monthly->value]);
    }

    #[Test]
    public function recurring_lines_honour_quantity()
    {
        $result = $this->calc([
            $this->recurring(1, 12, BillingPeriod::Monthly->value, quantity: 5),
        ]);

        $this->assertSame(6000, $result['recurring'][BillingPeriod::Monthly->value]);
    }

    #[Test]
    public function several_lines_in_the_same_period_add_together()
    {
        $result = $this->calc([
            $this->recurring(1, 50, BillingPeriod::Monthly->value),
            $this->recurring(2, 200, BillingPeriod::Monthly->value),
        ]);

        $this->assertSame(25000, $result['recurring'][BillingPeriod::Monthly->value]);
    }

    #[Test]
    public function a_proposal_with_no_recurring_lines_reports_none()
    {
        $result = $this->calc([$this->fixed(1, 1000)]);

        $this->assertFalse($result['hasRecurring']);
        $this->assertSame(0, $result['recurring'][BillingPeriod::Monthly->value]);
        $this->assertSame(0, $result['recurring'][BillingPeriod::Annually->value]);
    }

    #[Test]
    public function a_hundred_percent_line_doubles_the_base()
    {
        $result = $this->calc([
            $this->fixed(1, 1000),
            $this->percentage(2, 100),
        ]);

        $this->assertSame(200000, $result['subtotal']);
    }
}
