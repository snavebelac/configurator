<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Uuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid7();
        });
    }

    public static function findByUuid($uuid): Model
    {
        return self::where('uuid', $uuid)->first();
    }
}
