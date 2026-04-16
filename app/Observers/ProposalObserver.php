<?php

namespace App\Observers;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\Proposal;

class ProposalObserver
{
    public function created(Proposal $proposal): void
    {
        Activity::log(ActivityAction::ProposalCreated, $proposal);
    }

    public function updated(Proposal $proposal): void
    {
        if (! $proposal->wasChanged('status')) {
            return;
        }

        $from = $proposal->getOriginal('status');
        $to = $proposal->status;

        Activity::log(ActivityAction::ProposalStatusChanged, $proposal, [
            'from' => $from instanceof \BackedEnum ? $from->value : $from,
            'to' => $to instanceof \BackedEnum ? $to->value : $to,
        ]);
    }
}
