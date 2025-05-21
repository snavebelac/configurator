<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalFeature extends Model
{
    use Uuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'optional',
        'order',
        'final'
    ];

    protected $casts = [
        'optional' => 'boolean',
        'price' => 'integer'
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

    protected function lineTotalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Formatter::currency($attributes['price'] * $attributes['quantity'])
        );
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
