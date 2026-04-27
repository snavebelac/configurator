<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
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
                'user_id' => $user->id,
                'client_id' => $client->id,
                'status' => $status,
            ]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee($user->name);
    }

    #[Test]
    public function the_attention_pill_uses_human_readable_age_for_stale_drafts_and_stuck_delivered(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $staleDraft = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
            'name' => 'Stale draft',
        ]);
        $staleDraft->updated_at = Carbon::now()->subDays(10);
        $staleDraft->saveQuietly();

        $stuckDelivered = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DELIVERED,
            'name' => 'Stuck delivered',
        ]);
        $stuckDelivered->updated_at = Carbon::now()->subDays(20);
        $stuckDelivered->saveQuietly();

        $response = $this->get(route('dashboard'))->assertOk();

        $response->assertSeeText('untouched for');
        $response->assertSeeText('Delivered');
        $response->assertSeeText('ago');
        // Guard against the Carbon-3 float regression — never render the
        // raw decimal-and-d format ("14.23456d", or its plain "Xd" parent).
        $response->assertDontSeeText('d untouched');
        $this->assertDoesNotMatchRegularExpression(
            '/\d+\.\d+d/',
            $response->getContent(),
            'Dashboard rendered a decimal-day value — diffInDays leaked into the pill again',
        );
    }
}
