<?php

namespace App\Models;

use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use BelongsToTenant, HasFactory, Searchable, SoftDeletes, Uuid;

    protected $fillable = [
        'name',
        'description',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'feature_package')
            ->using(FeaturePackage::class)
            ->withPivot(['quantity', 'optional', 'price'])
            ->withTimestamps();
    }

    protected function totalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => Formatter::currency($this->total())
        );
    }

    /**
     * Package total using resolved overrides. Pivot overrides take precedence
     * over the Feature's own defaults.
     */
    public function total(): float|int
    {
        $this->loadMissing('features');

        return $this->features->sum(function (Feature $feature) {
            $price = $feature->pivot->price !== null
                ? Formatter::convertIntegerPrice($feature->pivot->price)
                : $feature->price;

            $quantity = $feature->pivot->quantity ?? $feature->quantity;

            return $price * $quantity;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
        ];
    }
}
