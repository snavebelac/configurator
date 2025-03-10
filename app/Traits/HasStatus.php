<?php

namespace App\Traits;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', Status::DRAFT);
    }

    public function scopeAccepted(Builder $query): void
    {
        $query->where('status', Status::ACCEPTED);
    }

    public function scopeDelivered(Builder $query): void
    {
        $query->where('status', Status::DELIVERED);
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where('status', Status::REJECTED);
    }

    public function scopeArchived(Builder $query): void
    {
        $query->where('status', Status::ARCHIVED);
    }
}
