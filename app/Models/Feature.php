<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    /** @use HasFactory<\Database\Factories\FeatureFactory> */
    use HasFactory, Uuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'optional',
        'order',
    ];

    protected $casts = [
        'optional' => 'boolean',
        'price' => 'integer',
    ];

    private function convertIntegerPrice(int $value): float
    {
        return round($value / 100, 2);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->convertIntegerPrice($value),
            set: fn ($value) => floor($value * 100),
        );
    }

    protected function priceForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Formatter::currency($this->convertIntegerPrice($attributes['price']))
        );
    }

    protected function lineTotalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Formatter::currency($attributes['price'] * $attributes['quantity'])
        );
    }

    public function proposals(): BelongsToMany
    {
        return $this->belongsToMany(Proposal::class)
            ->using(FeatureProposal::class)
            ->withPivot(['price', 'quantity', 'tenant_id'])
            ->withTimestamps();
    }
}
