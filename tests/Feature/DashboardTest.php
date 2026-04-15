<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_dashboard_renders_with_no_proposals()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Overview')
            ->assertSeeText('Open pipeline')
            ->assertSeeText('Won this month');
    }

    #[Test]
    public function the_dashboard_renders_when_proposals_exist_across_statuses()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        foreach ([Status::DRAFT, Status::DELIVERED, Status::ACCEPTED, Status::REJECTED, Status::ARCHIVED] as $status) {
            Proposal::factory()->create([
                'tenant_id' => $tenant->id,
                'user_id'   => $user->id,
                'client_id' => $client->id,
                'status'    => $status,
            ]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($user->name);
    }
}
