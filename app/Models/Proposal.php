<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\HasStatus;
use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Proposal extends Model
{
    /** @use HasFactory<\Database\Factories\ProposalFactory> */
    use HasFactory, Uuid, BelongsToTenant, HasStatus;

    protected $fillable = [
        'status',
        'name',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function total(): float|int
    {
        $total = 0;
        $this->loadMissing('features');
        foreach($this->features as $feature) {
//            $price = $feature->pivot->price ?? $feature->price;
//            $quantity = $feature->pivot->quantity ?? $feature->quantity;
            $price = $feature->price;
            $quantity = $feature->quantity;
            $total += $price * $quantity;
        }
        return $total;
    }

    public function totalPrice(): Attribute
    {
        return Attribute::make(
            set: fn(mixed $value, array $attributes) => $this->total()
        );
    }

    public function totalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => Formatter::currency($this->total())
        );
    }

    public function createdForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Formatter::date($attributes['created_at'])
        );
    }
    public function updatedForHumans(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Formatter::date($attributes['updated_at'])
        );
    }

//    public function features(): BelongsToMany
//    {
//        return $this->belongsToMany(Feature::class)
//            ->using(FeatureProposal::class)
//            ->withPivot(['price', 'quantity', 'tenant_id', 'id'])
//            ->withTimestamps();
//    }

    public function features(): HasMany
    {
        return $this->hasMany(FinalFeature::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
