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
        'expires_at',
        'access_code_hash',
    ];

    protected $casts = [
        'status' => Status::class,
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_code_hash',
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

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function requiresCode(): bool
    {
        return $this->access_code_hash !== null;
    }

    /**
     * Look up a proposal by its public-share UUID, intentionally bypassing
     * the tenant global scope.
     *
     * This is the ONLY place in the application where the tenant scope is
     * bypassed for proposal lookups. The route at `/p/{uuid}` is designed
     * to be cross-tenant — anyone with the unguessable UUID can view the
     * proposal, subject to the optional expiry and access-code gates
     * handled at a higher level. Do NOT use this from anywhere else; if
     * you need a tenant-scoped lookup, use `Proposal::query()` or
     * `Proposal::findByUuid()` from the `Uuid` trait.
     *
     * Throws `ModelNotFoundException` if no row matches the UUID.
     */
    public static function findByPublicShareUuid(string $uuid): self
    {
        return self::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->firstOrFail();
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
