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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;

class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use BelongsToTenant, HasFactory, HasStatus, Uuid;

    protected $fillable = [
        'status',
        'name',
    ];

    protected $casts = [
        'status' => Status::class,
        'access_password' => 'hashed',
        'access_expires_at' => 'datetime',
    ];

    /**
     * Whether a passcode has to be entered before the client-facing view will
     * render this proposal.
     */
    public function isPasscodeProtected(): bool
    {
        return filled($this->access_password);
    }

    public function hasExpired(): bool
    {
        return $this->access_expires_at !== null
            && $this->access_expires_at->isPast();
    }

    public function matchesPasscode(string $passcode): bool
    {
        if (! $this->isPasscodeProtected()) {
            return false;
        }

        return Hash::check($passcode, $this->access_password);
    }

    /**
     * The session key marking this proposal as unlocked for the current
     * visitor. Keyed on uuid rather than id so it means nothing if lifted.
     */
    public function unlockSessionKey(): string
    {
        return 'proposal_unlocked_'.$this->uuid;
    }

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

    public function response(): HasOne
    {
        return $this->hasOne(ProposalResponse::class);
    }

    /**
     * The exact terms revision this proposal was sent under. Pinned when the
     * proposal is marked delivered, so editing the tenant's terms afterwards
     * can't rewrite what the client was shown.
     */
    public function termsVersion(): BelongsTo
    {
        return $this->belongsTo(TermsVersion::class, 'terms_version_id');
    }

    /**
     * Whether the client has already answered. Once they have, the public view
     * locks to what they chose rather than staying interactive.
     */
    public function hasResponse(): bool
    {
        return $this->response()->exists();
    }

    /**
     * A proposal can only be answered once it has actually been sent, and only
     * while it is still awaiting an answer.
     */
    public function isOpenForResponse(): bool
    {
        return $this->status === Status::DELIVERED && ! $this->hasResponse();
    }

    public function canBeDelivered(): bool
    {
        return $this->status === Status::DRAFT && $this->features()->exists();
    }
}
