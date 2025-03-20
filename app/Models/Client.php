<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, Uuid, BelongsToTenant;

    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'contact'
    ];
}
