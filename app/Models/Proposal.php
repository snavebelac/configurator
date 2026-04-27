<?php

namespace App\Models;

use App\Enums\Status;
use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\HasStatus;
use App\Traits\Uuid;
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use BelongsToTenant, HasFactory, HasStatus, Searchable, Uuid;

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
        foreach ($this->features as $feature) {
            $total += $feature->price * $feature->quantity;
        }

        return $total;
    }

    public function totalPrice(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value, array $attributes) => $this->total()
        );
    }

    public function totalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => Formatter::currency($this->total())
        );
    }

    public function createdForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::date($attributes['created_at'])
        );
    }

    public function updatedForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::date($attributes['updated_at'])
        );
    }

    public function features(): HasMany
    {
        return $this->hasMany(FinalFeature::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
