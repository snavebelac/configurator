<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureProposal extends Pivot
{
    use BelongsToTenant;

    protected $fillable = [
        'quantity',
        'price',
    ];

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
            get: fn(mixed $value, array $attributes) => Formatter::currency(Formatter::convertIntegerPrice($attributes['price']))
        );
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
