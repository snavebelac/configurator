<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use App\Enums\PricingType;
use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinalFeature extends Model
{
    use BelongsToTenant, SoftDeletes, Uuid;

    protected $fillable = [
        'name',
        'description',
        'price',
        'pricing_type',
        'percentage_rate',
        'billing_period',
        'quantity',
        'optional',
        'parent_id',
        'source_feature_id',
        'order',
    ];

    /**
     * Bumps the parent proposal's `updated_at` whenever a line item is
     * created, updated, or deleted — so the dashboard's "needs your
     * attention" feed and any other recency-based logic reflect line
     * edits, not just edits to the proposal record itself.
     */
    protected $touches = ['proposal'];

    protected $casts = [
        'optional' => 'boolean',
        'price' => 'integer',
        'pricing_type' => PricingType::class,
        'billing_period' => BillingPeriod::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $finalFeature) {
            $finalFeature->children()->get()->each->delete();
        });
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Formatter::convertIntegerPrice($value),
            set: fn ($value) => floor($value * 100),
        );
    }

    protected function priceForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::currency(Formatter::convertIntegerPrice($attributes['price']))
        );
    }

    protected function lineTotalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::currency($attributes['price'] * $attributes['quantity'])
        );
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isChild(): bool
    {
        return $this->parent_id !== null;
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isPercentage(): bool
    {
        return $this->pricing_type === PricingType::Percentage;
    }

    public function isRecurring(): bool
    {
        return $this->pricing_type === PricingType::Recurring;
    }

    /**
     * A one-off, set amount. Deliberately not "everything that isn't a
     * percentage" — recurring lines are neither.
     */
    public function isFixed(): bool
    {
        return $this->pricing_type === PricingType::Fixed;
    }

    /**
     * "£50 / month", for anywhere a recurring line shows its price.
     */
    public function billingSuffix(): string
    {
        return $this->billing_period?->suffix() ?? '';
    }

    /**
     * The rate as a human percentage — 1250 basis points reads as 12.5.
     */
    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => ($attributes['percentage_rate'] ?? 0) / 100,
            set: fn ($value) => ['percentage_rate' => (int) round(((float) $value) * 100)],
        );
    }

    public function percentageForHumans(): string
    {
        return rtrim(rtrim(number_format($this->percentage, 2), '0'), '.').'%';
    }
}
