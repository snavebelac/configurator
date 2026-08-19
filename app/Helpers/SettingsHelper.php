<?php

namespace App\Helpers;

use App\Enums\CurrencySymbol;
use App\Models\Scopes\TenantScope;
use App\Models\Setting;

class SettingsHelper
{
    private ?Setting $setting = null;

    private ?int $tenantId = null;

    private bool $tenantWasSet = false;

    /**
     * Bind this helper to a specific tenant's settings.
     *
     * Needed anywhere the settings owner isn't the session's tenant — most
     * importantly the public proposal view, where there is no authenticated
     * user and therefore no session tenant to infer from.
     */
    public function forTenant(?int $tenantId): self
    {
        $this->tenantId = $tenantId;
        $this->tenantWasSet = true;
        $this->setting = null;

        return $this;
    }

    /**
     * Drop any explicit tenant binding and fall back to the session again.
     */
    public function forget(): self
    {
        $this->tenantId = null;
        $this->tenantWasSet = false;
        $this->setting = null;

        return $this;
    }

    public function getTaxName(): string
    {
        return $this->setting()->tax_name;
    }

    public function getTaxRate(): float
    {
        return (float) $this->setting()->tax_rate;
    }

    public function getCurrency(): CurrencySymbol
    {
        return $this->setting()->currency;
    }

    public function isTaxInclusive(): bool
    {
        return (bool) $this->setting()->tax_inclusive;
    }

    public function getCompanyName(): ?string
    {
        return $this->setting()->company_name;
    }

    public function getLogo(): ?string
    {
        return $this->setting()->logo;
    }

    /**
     * Resolve the settings row lazily, memoised for the life of the request.
     *
     * The lookup deliberately bypasses TenantScope and filters on tenant_id
     * explicitly. TenantScope is a no-op for unauthenticated requests, so a
     * plain `Setting::first()` would return whichever tenant's row happened
     * to come back first — leaking one tenant's currency and tax config onto
     * another tenant's public proposal.
     *
     * A tenant with no row yet gets model defaults rather than a fatal.
     */
    private function setting(): Setting
    {
        if ($this->setting instanceof Setting) {
            return $this->setting;
        }

        $tenantId = $this->tenantWasSet ? $this->tenantId : session('tenant_id');

        $setting = $tenantId === null
            ? null
            : Setting::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenantId)
                ->first();

        return $this->setting = $setting ?? new Setting;
    }
}
