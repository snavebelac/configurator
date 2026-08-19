<?php

namespace Tests\Feature;

use App\Enums\CurrencySymbol;
use App\Facades\Settings;
use App\Helpers\SettingsHelper;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The helper is a singleton, so a tenant bound in one test would otherwise
     * bleed into the next.
     */
    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    #[Test]
    public function a_tenant_with_no_settings_row_falls_back_to_defaults()
    {
        $tenant = Tenant::factory()->create();

        Settings::forTenant($tenant->id);

        $this->assertSame('VAT', Settings::getTaxName());
        $this->assertSame(20.0, Settings::getTaxRate());
        $this->assertSame(CurrencySymbol::GBP, Settings::getCurrency());
        $this->assertFalse(Settings::isTaxInclusive());
    }

    #[Test]
    public function no_tenant_at_all_falls_back_to_defaults_rather_than_fatalling()
    {
        $this->assertSame('VAT', Settings::getTaxName());
        $this->assertSame(CurrencySymbol::GBP, Settings::getCurrency());
    }

    #[Test]
    public function settings_resolve_from_the_session_tenant_when_not_bound_explicitly()
    {
        $tenant = Tenant::factory()->create();

        Setting::factory()->create([
            'tenant_id' => $tenant->id,
            'tax_name' => 'Sales Tax',
            'currency' => CurrencySymbol::USD,
        ]);

        session(['tenant_id' => $tenant->id]);

        $this->assertSame('Sales Tax', Settings::getTaxName());
        $this->assertSame(CurrencySymbol::USD, Settings::getCurrency());
    }

    #[Test]
    public function an_unauthenticated_read_returns_the_requested_tenants_settings_not_the_first_row()
    {
        // Tenant A's row is created first, so an unscoped `Setting::first()`
        // would return it regardless of which tenant was asked for. This is
        // the cross-tenant leak that would surface on public proposal URLs.
        $tenantA = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $tenantA->id,
            'tax_name' => 'VAT',
            'tax_rate' => 20.0,
            'currency' => CurrencySymbol::GBP,
        ]);

        $tenantB = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $tenantB->id,
            'tax_name' => 'Sales Tax',
            'tax_rate' => 8.5,
            'currency' => CurrencySymbol::USD,
        ]);

        $this->assertGuest();

        Settings::forTenant($tenantB->id);

        $this->assertSame('Sales Tax', Settings::getTaxName());
        $this->assertSame(8.5, Settings::getTaxRate());
        $this->assertSame(CurrencySymbol::USD, Settings::getCurrency());
    }

    #[Test]
    public function rebinding_the_tenant_discards_the_memoised_row()
    {
        $tenantA = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $tenantA->id,
            'currency' => CurrencySymbol::GBP,
        ]);

        $tenantB = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $tenantB->id,
            'currency' => CurrencySymbol::EUR,
        ]);

        $settings = app(SettingsHelper::class);

        $this->assertSame(CurrencySymbol::GBP, $settings->forTenant($tenantA->id)->getCurrency());
        $this->assertSame(CurrencySymbol::EUR, $settings->forTenant($tenantB->id)->getCurrency());
    }

    #[Test]
    public function an_explicit_tenant_binding_beats_the_session_tenant()
    {
        $sessionTenant = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $sessionTenant->id,
            'currency' => CurrencySymbol::GBP,
        ]);

        $otherTenant = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $otherTenant->id,
            'currency' => CurrencySymbol::EUR,
        ]);

        session(['tenant_id' => $sessionTenant->id]);

        Settings::forTenant($otherTenant->id);

        $this->assertSame(CurrencySymbol::EUR, Settings::getCurrency());
    }
}
