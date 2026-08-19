<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One frozen revision of a terms set.
 *
 * Unpublished rows are the working draft. Once published the body is never
 * edited again — a proposal pinned to this version has to keep showing what
 * the client actually agreed to.
 */
class TermsVersion extends Model
{
    use BelongsToTenant, Uuid;

    protected $fillable = [
        'version',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function terms(): BelongsTo
    {
        return $this->belongsTo(Terms::class, 'terms_id');
    }

    /**
     * Proposals pinned to this exact revision.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'terms_version_id');
    }

    /**
     * Client answers given against this exact revision.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ProposalResponse::class, 'terms_version_id');
    }

    /**
     * Whether anything depends on this revision surviving unchanged.
     */
    public function isInUse(): bool
    {
        return $this->proposals()->exists() || $this->responses()->exists();
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Freeze the draft. Refuses an empty body: publishing nothing would let a
     * proposal go out pinned to blank terms, which is worse than none at all.
     */
    public function publish(): bool
    {
        if ($this->isPublished() || blank($this->body)) {
            return false;
        }

        $this->published_at = now();

        return $this->save();
    }

    public function label(): string
    {
        return 'v'.$this->version;
    }
}
