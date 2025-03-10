<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = session('tenant_id', -1);
        if (auth()->hasUser()) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
        }
    }
}
