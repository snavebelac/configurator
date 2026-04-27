<?php

namespace App\Models;

use App\Enums\CurrencySymbol;
use App\Traits\BelongsToTenant;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'currency',
        'tax_rate',
        'tax_name',
        'tax_inclusive',
        'default_share_expiry_days',
        'logo',
        'company_name',
    ];

    protected $casts = [
        'currency' => CurrencySymbol::class,
        'tax_inclusive' => 'boolean',
        'default_share_expiry_days' => 'integer',
    ];

    /**
     * Resolve the Setting record for a specific tenant, intentionally
     * bypassing the tenant global scope.
     *
     * Used by `SettingsHelper` when rendering public share links: the
     * visitor's session-tenant (if any) is not authoritative; the
     * proposal's own `tenant_id` is. Always pass an explicit,
     * application-controlled tenant id — never derive it from user
     * input without re-validation.
     */
    public static function forTenant(int $tenantId): ?self
    {
        return self::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->first();
    }
}
