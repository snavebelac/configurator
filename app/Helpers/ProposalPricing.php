<?php

namespace App\Helpers;

use App\Models\FinalFeature;
use Illuminate\Support\Collection;

/**
 * Works out what a proposal comes to, in integer pence.
 *
 * The single source of truth for proposal maths. The client-facing view runs a
 * mirror of this in Alpine so the total updates without a round trip, but that
 * copy is for display only — every figure that gets *recorded* comes from
 * here, computed server-side from the database. A tampered payload can change
 * which optional lines are selected, never what they cost.
 *
 * Percentage lines (project management, contingency) take their cut of the
 * fixed lines that are currently included, and never of each other: making
 * them compound would make the result depend on line order and open the door
 * to circular definitions.
 */
class ProposalPricing
{
    /**
     * @param  Collection<int, FinalFeature>  $features  every line on the proposal
     * @param  array<int, int>  $selectedOptionalIds  the optional lines currently switched on
     * @return array{
     *     fixedBase: int,
     *     percentageTotal: int,
     *     subtotal: int,
     *     percentageLines: Collection<int, array{feature: FinalFeature, amount: int}>
     * }
     */
    public function calculate(Collection $features, array $selectedOptionalIds): array
    {
        $selected = array_map('intval', $selectedOptionalIds);

        $included = $features->filter(
            fn (FinalFeature $feature) => ! $feature->optional
                || in_array((int) $feature->id, $selected, true),
        );

        $fixedBase = (int) $included
            ->reject(fn (FinalFeature $feature) => $feature->isPercentage())
            ->sum(fn (FinalFeature $feature) => $this->lineAmount($feature, 0));

        $percentageLines = $included
            ->filter(fn (FinalFeature $feature) => $feature->isPercentage())
            ->map(fn (FinalFeature $feature) => [
                'feature' => $feature,
                'amount' => $this->lineAmount($feature, $fixedBase),
            ])
            ->values();

        $percentageTotal = (int) $percentageLines->sum('amount');

        return [
            'fixedBase' => $fixedBase,
            'percentageTotal' => $percentageTotal,
            'subtotal' => $fixedBase + $percentageTotal,
            'percentageLines' => $percentageLines,
        ];
    }

    /**
     * What one line contributes, in pence.
     *
     * Percentage lines ignore quantity — "10% × 3" means nothing — and round
     * half up to the nearest penny.
     */
    public function lineAmount(FinalFeature $feature, int $base): int
    {
        if ($feature->isPercentage()) {
            // percentage_rate is basis points, so 1250 = 12.5%.
            return (int) round($base * $feature->percentage_rate / 10000);
        }

        // getAttributes() rather than getRawOriginal(): both bypass the price
        // accessor's pence-to-pounds conversion, but `original` is only
        // populated once a model has been through the database, so
        // getRawOriginal silently returns null — and therefore zero — for a
        // model that hasn't been saved yet.
        return (int) ($feature->getAttributes()['price'] ?? 0) * (int) $feature->quantity;
    }
}
