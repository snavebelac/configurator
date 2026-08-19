<?php

namespace Tests\Feature;

use App\Enums\CurrencySymbol;
use App\Facades\Settings;
use App\Helpers\TenantProvisioner;
use App\Livewire\Signup;
use App\Models\Client;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignupTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company' => 'Epic Fox Ltd',
            'name' => 'Caleb',
            'lastName' => 'Evans',
            'email' => 'caleb@epicfox.co.uk',
            'password' => 'a-strong-password',
            'passwordConfirmation' => 'a-strong-password',
        ], $overrides);
    }

    private function submit(array $overrides = []): Testable
    {
        $component = Livewire::test(Signup::class);

        foreach ($this->validPayload($overrides) as $field => $value) {
            $component->set($field, $value);
        }

        return $component->call('register');
    }

    #[Test]
    public function the_signup_page_renders()
    {
        $this->get(route('signup'))
            ->assertOk()
            ->assertSeeText('Create your workspace.')
            ->assertSeeText('Company name');
    }

    #[Test]
    public function signing_up_creates_a_tenant_settings_and_an_active_admin_user()
    {
        $this->submit()->assertHasNoErrors();

        $this->assertDatabaseHas('tenants', [
            'name' => 'Epic Fox Ltd',
            'subdomain' => 'epic-fox-ltd',
        ]);

        $tenant = Tenant::firstWhere('name', 'Epic Fox Ltd');

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'caleb@epicfox.co.uk',
            'name' => 'Caleb',
            'last_name' => 'Evans',
            'active' => true,
        ]);

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $tenant->id,
            'tax_name' => 'VAT',
            'currency' => CurrencySymbol::GBP->value,
        ]);
    }

    #[Test]
    public function the_new_user_is_logged_in_with_the_owner_role_and_a_session_tenant()
    {
        $this->submit()->assertRedirect(route('dashboard'));

        $user = User::withoutGlobalScopes()->firstWhere('email', 'caleb@epicfox.co.uk');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole(TenantProvisioner::OWNER_ROLE));
        $this->assertSame($user->tenant_id, session('tenant_id'));
    }

    #[Test]
    public function the_password_is_hashed()
    {
        $this->submit()->assertHasNoErrors();

        $user = User::withoutGlobalScopes()->firstWhere('email', 'caleb@epicfox.co.uk');

        $this->assertNotSame('a-strong-password', $user->password);
        $this->assertTrue(Hash::check('a-strong-password', $user->password));
    }

    #[Test]
    public function a_second_workspace_gets_a_distinct_subdomain()
    {
        $this->submit()->assertHasNoErrors();

        auth()->logout();
        session()->flush();

        $this->submit(['email' => 'someone@example.com'])->assertHasNoErrors();

        $this->assertDatabaseHas('tenants', ['subdomain' => 'epic-fox-ltd']);
        $this->assertDatabaseHas('tenants', ['subdomain' => 'epic-fox-ltd-2']);
    }

    #[Test]
    public function a_new_workspace_cannot_see_another_workspaces_data()
    {
        $existingTenant = Tenant::factory()->create();
        Client::factory()->create([
            'tenant_id' => $existingTenant->id,
            'name' => 'Pre-existing Client',
        ]);

        $this->submit()->assertHasNoErrors();

        $this->assertSame(0, Client::count());
    }

    #[Test]
    public function all_fields_are_required()
    {
        Livewire::test(Signup::class)
            ->call('register')
            ->assertHasErrors([
                'company' => 'required',
                'name' => 'required',
                'lastName' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);

        $this->assertDatabaseCount('tenants', 0);
    }

    #[Test]
    public function the_email_must_be_unique_across_every_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'email' => 'caleb@epicfox.co.uk',
        ]);

        $this->submit()->assertHasErrors(['email' => 'unique']);

        // Only the existing tenant should remain — nothing was provisioned.
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseMissing('tenants', ['name' => 'Epic Fox Ltd']);
    }

    #[Test]
    public function the_password_must_be_confirmed()
    {
        $this->submit(['passwordConfirmation' => 'something-else'])
            ->assertHasErrors(['password' => 'confirmed']);

        $this->assertDatabaseCount('tenants', 0);
    }

    #[Test]
    public function a_failed_signup_leaves_nothing_behind()
    {
        $this->submit(['email' => 'not-an-email'])->assertHasErrors('email');

        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('settings', 0);
    }

    #[Test]
    public function the_tenant_create_command_provisions_a_workspace()
    {
        $this->artisan('tenant:create', [
            '--company' => 'Terminal Co',
            '--name' => 'Caleb',
            '--last-name' => 'Evans',
            '--email' => 'terminal@example.com',
            '--password' => 'a-strong-password',
        ])->assertSuccessful();

        $tenant = Tenant::firstWhere('name', 'Terminal Co');

        $this->assertNotNull($tenant);
        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'terminal@example.com',
            'active' => true,
        ]);
        $this->assertDatabaseHas('settings', ['tenant_id' => $tenant->id]);
    }

    #[Test]
    public function the_tenant_create_command_rejects_a_duplicate_email()
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'taken@example.com',
        ]);

        $this->artisan('tenant:create', [
            '--company' => 'Terminal Co',
            '--name' => 'Caleb',
            '--last-name' => 'Evans',
            '--email' => 'taken@example.com',
            '--password' => 'a-strong-password',
        ])->assertFailed();

        $this->assertDatabaseMissing('tenants', ['name' => 'Terminal Co']);
    }

    #[Test]
    public function settings_for_a_freshly_provisioned_tenant_resolve_to_defaults()
    {
        $this->submit()->assertHasNoErrors();

        $tenant = Tenant::firstWhere('name', 'Epic Fox Ltd');

        $this->assertSame(1, Setting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        Settings::forTenant($tenant->id);

        $this->assertSame('VAT', Settings::getTaxName());
        $this->assertSame(20.0, Settings::getTaxRate());
        $this->assertSame(CurrencySymbol::GBP, Settings::getCurrency());
    }
}
