<?php

namespace App\Enums;

enum Status: string
{
    case DRAFT = 'draft';
    case DELIVERED = 'delivered';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';
}
