<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * A named set of terms and conditions — "Standard build", "Retainer" — owned
 * by a tenant and versioned over time.
 */
class Terms extends Model
{
    use BelongsToTenant, Uuid;

    protected $table = 'terms';

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TermsVersion::class, 'terms_id');
    }

    /**
     * The working copy. There is at most one unpublished version per set.
     */
    public function draft(): HasOne
    {
        return $this->hasOne(TermsVersion::class, 'terms_id')
            ->whereNull('published_at');
    }

    /**
     * The version a proposal would be pinned to right now.
     */
    public function currentVersion(): HasOne
    {
        // The published_at filter has to go inside the aggregate subquery, via
        // ofMany's constraint closure. Chaining ->whereNotNull()->latestOfMany()
        // instead applies it to the *outer* query, so MAX(version) picks the
        // unpublished draft and the outer filter then throws it away — leaving
        // no current version at all the moment a draft exists.
        return $this->hasOne(TermsVersion::class, 'terms_id')->ofMany(
            ['version' => 'max'],
            fn (Builder $query) => $query->whereNotNull('published_at'),
        );
    }

    public function publishedVersions(): HasMany
    {
        return $this->versions()->whereNotNull('published_at');
    }

    public function isPublished(): bool
    {
        return $this->publishedVersions()->exists();
    }

    /**
     * Get the working draft, creating one from the current published version
     * if there isn't one — editing always starts from what is live.
     */
    public function draftOrNew(): TermsVersion
    {
        $draft = $this->draft()->first();

        if ($draft !== null) {
            return $draft;
        }

        $current = $this->currentVersion()->first();

        $draft = new TermsVersion([
            'version' => ($this->versions()->max('version') ?? 0) + 1,
            'body' => $current?->body,
        ]);
        $draft->tenant_id = $this->tenant_id;
        $draft->terms_id = $this->id;
        $draft->save();

        return $draft;
    }

    /**
     * Make this the set new proposals are pinned to, demoting whichever set
     * held it. A tenant has exactly one default.
     */
    public function makeDefault(): void
    {
        DB::transaction(function () {
            static::query()
                ->where('tenant_id', $this->tenant_id)
                ->whereKeyNot($this->getKey())
                ->update(['is_default' => false]);

            $this->is_default = true;
            $this->save();
        });
    }
}
