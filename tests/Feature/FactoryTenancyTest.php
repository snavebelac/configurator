<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Factories used to resolve Tenant::factory()->create() inside definition(),
 * which runs regardless of overrides — so scoping a model to an existing
 * tenant still built and discarded one. These pin the lazy behaviour.
 */
class FactoryTenancyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function overriding_the_tenant_does_not_create_a_spare_one()
    {
        $tenant = Tenant::factory()->create();
        $this->assertDatabaseCount('tenants', 1);

        User::factory()->create(['tenant_id' => $tenant->id]);
        Client::factory()->create(['tenant_id' => $tenant->id]);
        Feature::factory()->create(['tenant_id' => $tenant->id]);
        Package::factory()->create(['tenant_id' => $tenant->id]);
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertDatabaseCount('tenants', 1);
    }

    #[Test]
    public function a_fully_overridden_proposal_creates_nothing_extra()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('clients', 1);

        Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('clients', 1);
    }

    #[Test]
    public function a_proposal_scoped_to_a_tenant_builds_its_owner_and_client_in_that_tenant()
    {
        $tenant = Tenant::factory()->create();

        $proposal = Proposal::factory()->create(['tenant_id' => $tenant->id]);

        // Only the tenant we made — the owner and client came from it, not
        // from tenants of their own.
        $this->assertDatabaseCount('tenants', 1);

        $this->assertSame($tenant->id, $proposal->tenant_id);
        $this->assertSame($tenant->id, User::withoutGlobalScopes()->find($proposal->user_id)->tenant_id);
        $this->assertSame($tenant->id, Client::withoutGlobalScopes()->find($proposal->client_id)->tenant_id);
    }

    #[Test]
    public function a_bare_proposal_is_internally_consistent()
    {
        $proposal = Proposal::factory()->create();

        $this->assertDatabaseCount('tenants', 1);

        $this->assertSame($proposal->tenant_id, User::withoutGlobalScopes()->find($proposal->user_id)->tenant_id);
        $this->assertSame($proposal->tenant_id, Client::withoutGlobalScopes()->find($proposal->client_id)->tenant_id);
    }
}
