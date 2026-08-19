<?php

namespace App\Models;

use App\Enums\Status;
use App\Facades\Formatter;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A client's answer to a proposal: accepted or rejected, what they picked,
 * and what it came to.
 */
class ProposalResponse extends Model
{
    use BelongsToTenant, Uuid;

    protected $fillable = [
        'status',
        'selected_feature_ids',
        'accepted_total',
        'terms_version_id',
        'note',
        'responded_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'selected_feature_ids' => 'array',
            'responded_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * The terms in force when the client answered — recorded here as well as
     * on the proposal, because a proposal can be reopened and re-sent against
     * newer terms and this record must not move with it.
     */
    public function termsVersion(): BelongsTo
    {
        return $this->belongsTo(TermsVersion::class, 'terms_version_id');
    }

    public function wasAccepted(): bool
    {
        return $this->status === Status::ACCEPTED;
    }

    /**
     * accepted_total is stored in pence, matching FinalFeature.price.
     */
    protected function acceptedTotalForHumans(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Formatter::currency(
                Formatter::convertIntegerPrice($attributes['accepted_total']) ?? 0.0
            )
        );
    }
}
