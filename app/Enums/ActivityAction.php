<?php

namespace App\Enums;

enum ActivityAction: string
{
    case ProposalCreated = 'proposal.created';
    case ProposalStatusChanged = 'proposal.status_changed';
    case ClientCreated = 'client.created';
    case PackageCreated = 'package.created';
}
