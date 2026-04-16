<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FeaturePackage extends Pivot
{
    use BelongsToTenant;

    public $incrementing = true;

    protected $table = 'feature_package';

    protected $fillable = [
        'tenant_id',
        'package_id',
        'feature_id',
        'quantity',
        'optional',
        'price',
    ];

    protected $casts = [
        'optional' => 'boolean',
        'price' => 'integer',
        'quantity' => 'integer',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : Formatter::convertIntegerPrice($value),
            set: fn ($value) => $value === null ? null : floor($value * 100),
        );
    }
}
