<?php

namespace App\Enums;

enum Status: String
{
    case DRAFT = 'draft';
    case DELIVERED = 'delivered';
    case ACCEPTED ='accepted';
    case REJECTED ='rejected';
    case ARCHIVED ='archived';
}
