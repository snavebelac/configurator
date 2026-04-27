<?php

namespace Tests\Feature;

use App\Enums\CurrencySymbol;
use App\Livewire\Admin\Settings as SettingsPage;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_page_renders_with_existing_setting_values(): void
    {
        [$tenant] = $this->signIn();

        Setting::factory()->create([
            'tenant_id' => $tenant->id,
            'currency' => CurrencySymbol::USD,
            'tax_rate' => 8.5,
            'tax_name' => 'Sales Tax',
            'tax_inclusive' => true,
            'default_share_expiry_days' => 30,
        ]);

        Livewire::test(SettingsPage::class)
            ->assertSet('currency', CurrencySymbol::USD->value)
            ->assertSet('taxName', 'Sales Tax')
            ->assertSet('taxRate', '8.50')
            ->assertSet('taxInclusive', true)
            ->assertSet('defaultShareExpiryDays', '30');
    }

    #[Test]
    public function blank_default_share_expiry_persists_as_null(): void
    {
        [$tenant] = $this->signIn();

        Setting::factory()->create([
            'tenant_id' => $tenant->id,
            'default_share_expiry_days' => 60,
        ]);

        Livewire::test(SettingsPage::class)
            ->set('defaultShareExpiryDays', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Setting::first()->default_share_expiry_days);
    }

    #[Test]
    public function saving_persists_currency_tax_and_share_expiry_changes(): void
    {
        [$tenant] = $this->signIn();
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(SettingsPage::class)
            ->set('currency', CurrencySymbol::EUR->value)
            ->set('taxName', 'IVA')
            ->set('taxRate', '21')
            ->set('taxInclusive', true)
            ->set('defaultShareExpiryDays', '14')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $setting = Setting::first();
        $this->assertSame(CurrencySymbol::EUR, $setting->currency);
        $this->assertSame('IVA', $setting->tax_name);
        $this->assertEquals(21.0, (float) $setting->tax_rate);
        $this->assertTrue((bool) $setting->tax_inclusive);
        $this->assertSame(14, $setting->default_share_expiry_days);
    }

    #[Test]
    public function validation_rejects_bad_input(): void
    {
        [$tenant] = $this->signIn();
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(SettingsPage::class)
            ->set('currency', 'doubloon')
            ->set('taxName', '')
            ->set('taxRate', 'not a number')
            ->set('defaultShareExpiryDays', '0')
            ->call('save')
            ->assertHasErrors(['currency', 'taxName', 'taxRate', 'defaultShareExpiryDays']);
    }

    #[Test]
    public function settings_for_other_tenants_are_not_visible(): void
    {
        $otherTenant = Tenant::factory()->create();
        Setting::factory()->create([
            'tenant_id' => $otherTenant->id,
            'tax_name' => 'OtherTenantTax',
        ]);

        [$myTenant] = $this->signIn();
        Setting::factory()->create([
            'tenant_id' => $myTenant->id,
            'tax_name' => 'MyTax',
        ]);

        Livewire::test(SettingsPage::class)
            ->assertSet('taxName', 'MyTax');
    }

    #[Test]
    public function the_settings_route_renders_the_livewire_component(): void
    {
        [$tenant] = $this->signIn();
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        $this->get(route('dashboard.settings'))
            ->assertOk()
            ->assertSeeLivewire(SettingsPage::class)
            ->assertSeeText('Workspace settings.');
    }
}
