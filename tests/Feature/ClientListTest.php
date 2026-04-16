<?php

namespace Tests\Feature;

use App\Livewire\Admin\Clients\ClientList;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientListTest extends TestCase
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
    public function the_clients_page_renders_with_an_empty_state()
    {
        $this->signIn();

        $this->get(route('dashboard.clients'))
            ->assertOk()
            ->assertSeeText('Clients.')
            ->assertSeeText('No clients yet');
    }

    #[Test]
    public function the_clients_page_lists_existing_clients()
    {
        [$tenant] = $this->signIn();

        Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson Studio']);
        Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Northwind Trading']);

        $this->get(route('dashboard.clients'))
            ->assertOk()
            ->assertSeeText('Halverson Studio')
            ->assertSeeText('Northwind Trading');
    }

    #[Test]
    public function the_search_matches_name_and_contact_fields()
    {
        [$tenant] = $this->signIn();

        Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson Studio', 'contact' => 'Avery Halverson']);
        Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Northwind Trading', 'contact' => 'Priya Ram']);

        Livewire::test(ClientList::class)
            ->set('search', 'Halverson')
            ->assertSee('Halverson Studio')
            ->assertDontSee('Northwind Trading')
            ->set('search', 'Priya')
            ->assertSee('Northwind Trading')
            ->assertDontSee('Halverson Studio');
    }

    #[Test]
    public function a_client_can_be_deleted_from_the_list()
    {
        [$tenant] = $this->signIn();

        $client = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Will be gone']);

        Livewire::test(ClientList::class)
            ->call('delete', $client->id);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
