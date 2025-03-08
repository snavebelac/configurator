<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /** @use HasFactory<\Database\Factories\FeatureFactory> */
    use HasFactory, Uuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'optional',
        'order',
    ];

    protected $casts = [
        'optional' => 'boolean'
    ];
}
