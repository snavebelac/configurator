<?php

namespace Tests\Feature;

use App\Enums\CurrencySymbol;
use App\Facades\Settings as SettingsFacade;
use App\Livewire\Admin\Settings;
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

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
    }

    protected function tearDown(): void
    {
        SettingsFacade::forget();

        parent::tearDown();
    }

    #[Test]
    public function the_settings_page_renders()
    {
        Setting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'tax_name' => 'VAT',
        ]);

        $this->get(route('dashboard.settings'))
            ->assertOk()
            ->assertSeeText('Workspace settings.')
            ->assertSeeText('Currency');
    }

    #[Test]
    public function the_page_creates_a_settings_row_for_a_tenant_that_has_none()
    {
        $this->assertDatabaseCount('settings', 0);

        Livewire::test(Settings::class)
            ->assertSet('taxName', 'VAT')
            ->assertSet('currency', CurrencySymbol::GBP->value);

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'tax_name' => 'VAT',
        ]);
    }

    #[Test]
    public function settings_can_be_updated()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('companyName', 'Epic Fox Ltd')
            ->set('currency', CurrencySymbol::USD->value)
            ->set('taxName', 'Sales Tax')
            ->set('taxRate', '8.5')
            ->set('taxInclusive', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Epic Fox Ltd',
            'currency' => CurrencySymbol::USD->value,
            'tax_name' => 'Sales Tax',
            'tax_rate' => 8.5,
            'tax_inclusive' => true,
        ]);
    }

    #[Test]
    public function saving_does_not_create_a_second_row_for_the_same_tenant()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('taxName', 'GST')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('settings', 1);
    }

    #[Test]
    public function the_tax_rate_must_be_a_percentage()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('taxRate', '101')
            ->call('save')
            ->assertHasErrors(['taxRate' => 'max']);

        Livewire::test(Settings::class)
            ->set('taxRate', 'not a number')
            ->call('save')
            ->assertHasErrors(['taxRate' => 'numeric']);
    }

    #[Test]
    public function the_tax_name_and_currency_are_required()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('taxName', '')
            ->set('currency', '')
            ->call('save')
            ->assertHasErrors(['taxName' => 'required', 'currency' => 'required']);
    }

    #[Test]
    public function a_tenant_cannot_see_or_edit_another_tenants_settings()
    {
        $otherTenant = Tenant::factory()->create();

        // BelongsToTenant's creating hook stamps tenant_id from the session,
        // so the other tenant's row has to be written with no session tenant
        // in play — otherwise it silently lands on our own tenant instead.
        session()->forget('tenant_id');
        Setting::factory()->create([
            'tenant_id' => $otherTenant->id,
            'tax_name' => 'Someone Elses Tax',
        ]);
        session(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->assertSet('taxName', 'VAT')
            ->set('taxName', 'Our Tax')
            ->call('save');

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $otherTenant->id,
            'tax_name' => 'Someone Elses Tax',
        ]);
        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'tax_name' => 'Our Tax',
        ]);
    }

    #[Test]
    public function saving_refreshes_the_settings_singleton()
    {
        Setting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'currency' => CurrencySymbol::GBP,
        ]);

        // Warm the singleton with the pre-save value.
        $this->assertSame(CurrencySymbol::GBP, SettingsFacade::getCurrency());

        Livewire::test(Settings::class)
            ->set('currency', CurrencySymbol::EUR->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(CurrencySymbol::EUR, SettingsFacade::getCurrency());
    }
}
