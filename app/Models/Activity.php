<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use BelongsToTenant, Uuid;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'action' => ActivityAction::class,
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(ActivityAction $action, Model $subject, array $payload = []): ?self
    {
        $tenantId = session('tenant_id') ?? ($subject->tenant_id ?? null);
        if ($tenantId === null) {
            return null;
        }

        // Preserve the subject's display name so the feed still reads well
        // if the subject is later soft-deleted.
        $payload = array_merge(
            ['subject_name' => $subject->name ?? null],
            $payload,
        );

        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'action' => $action->value,
            'payload' => $payload,
        ]);
    }

    public function subjectName(): ?string
    {
        return $this->payload['subject_name']
            ?? $this->subject?->name
            ?? null;
    }

    public function headline(): string
    {
        $actor = $this->user?->full_name ?? 'Someone';
        $name = $this->subjectName() ?? '—';

        return match ($this->action) {
            ActivityAction::ProposalCreated => "{$actor} created {$name}",
            ActivityAction::ProposalStatusChanged => "{$actor} moved {$name} to ".ucfirst((string) ($this->payload['to'] ?? '')),
            ActivityAction::ClientCreated => "{$actor} added client {$name}",
            ActivityAction::PackageCreated => "{$actor} created package {$name}",
        };
    }

    public function subjectTypeLabel(): string
    {
        return match ($this->action) {
            ActivityAction::ProposalCreated, ActivityAction::ProposalStatusChanged => 'Proposal',
            ActivityAction::ClientCreated => 'Client',
            ActivityAction::PackageCreated => 'Package',
        };
    }
}
