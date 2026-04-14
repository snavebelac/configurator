<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes, Uuid;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'optional',
        'order',
        'final',
    ];

    protected $casts = [
        'optional' => 'boolean',
        'price' => 'integer',
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
            get: fn (mixed $value, array $attributes) => Formatter::currency(Formatter::convertIntegerPrice($attributes['price']))
        );
    }

    protected function lineTotalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::currency($attributes['price'] * $attributes['quantity'])
        );
    }
}
