<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Livewire\Admin\Proposals\ProposalsList;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalsListTest extends TestCase
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
    public function the_proposals_page_renders_with_an_empty_state()
    {
        $this->signIn();

        $this->get(route('dashboard.proposals'))
            ->assertOk()
            ->assertSeeText('Proposals.')
            ->assertSeeText('No proposals yet');
    }

    #[Test]
    public function the_proposals_page_lists_proposals_across_statuses()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Northwind Trading']);

        foreach (Status::cases() as $status) {
            Proposal::factory()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'client_id' => $client->id,
                'status' => $status,
                'name' => 'Project '.$status->value,
            ]);
        }

        $this->get(route('dashboard.proposals'))
            ->assertOk()
            ->assertSeeText('Project draft')
            ->assertSeeText('Project delivered')
            ->assertSeeText('Project accepted')
            ->assertSeeText('Northwind Trading');
    }

    #[Test]
    public function the_status_filter_narrows_the_list()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        Proposal::factory()->create([
            'tenant_id' => $tenant->id, 'user_id' => $user->id, 'client_id' => $client->id,
            'status' => Status::DRAFT, 'name' => 'Only draft here',
        ]);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id, 'user_id' => $user->id, 'client_id' => $client->id,
            'status' => Status::ACCEPTED, 'name' => 'Already accepted',
        ]);

        Livewire::test(ProposalsList::class)
            ->assertSee('Only draft here')
            ->assertSee('Already accepted')
            ->set('filter', 'draft')
            ->assertSee('Only draft here')
            ->assertDontSee('Already accepted');
    }

    #[Test]
    public function the_search_matches_name_and_client_fields()
    {
        [$tenant, $user] = $this->signIn();
        $halverson = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson Studio']);
        $northwind = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Northwind Trading']);

        Proposal::factory()->create([
            'tenant_id' => $tenant->id, 'user_id' => $user->id, 'client_id' => $halverson->id,
            'status' => Status::DRAFT, 'name' => 'Brand identity system',
        ]);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id, 'user_id' => $user->id, 'client_id' => $northwind->id,
            'status' => Status::DRAFT, 'name' => 'E-commerce launch',
        ]);

        Livewire::test(ProposalsList::class)
            ->set('search', 'brand')
            ->assertSee('Brand identity system')
            ->assertDontSee('E-commerce launch')
            ->set('search', 'Northwind')
            ->assertSee('E-commerce launch')
            ->assertDontSee('Brand identity system');
    }

    #[Test]
    public function a_proposal_can_be_deleted_from_the_list()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id, 'user_id' => $user->id, 'client_id' => $client->id,
            'status' => Status::DRAFT, 'name' => 'Short-lived draft',
        ]);

        Livewire::test(ProposalsList::class)
            ->call('delete', $proposal->id);

        $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
    }
}
