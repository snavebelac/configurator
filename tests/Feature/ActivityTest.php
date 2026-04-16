<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Enums\Status;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Package;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'active' => true,
            'name' => 'Caleb',
            'last_name' => 'Evans',
        ]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function creating_a_proposal_logs_a_proposal_created_activity()
    {
        [, $user] = $this->signIn();
        $client = Client::factory()->create();

        $proposal = Proposal::factory()->create([
            'name' => 'New work',
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $activity = Activity::where('action', ActivityAction::ProposalCreated->value)
            ->where('subject_id', $proposal->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame('New work', $activity->payload['subject_name']);
    }

    #[Test]
    public function changing_a_proposal_status_logs_a_status_changed_activity()
    {
        [, $user] = $this->signIn();
        $client = Client::factory()->create();
        $proposal = Proposal::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        Activity::query()->delete();

        $proposal->update(['status' => Status::DELIVERED]);

        $activity = Activity::where('action', ActivityAction::ProposalStatusChanged->value)->first();

        $this->assertNotNull($activity);
        $this->assertSame(Status::DRAFT->value, $activity->payload['from']);
        $this->assertSame(Status::DELIVERED->value, $activity->payload['to']);
    }

    #[Test]
    public function non_status_updates_do_not_log_a_status_change()
    {
        [, $user] = $this->signIn();
        $client = Client::factory()->create();
        $proposal = Proposal::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        Activity::query()->delete();

        $proposal->update(['name' => 'Renamed']);

        $this->assertSame(0, Activity::count());
    }

    #[Test]
    public function creating_a_client_logs_a_client_created_activity()
    {
        [$tenant] = $this->signIn();

        Client::create([
            'tenant_id' => $tenant->id,
            'name' => 'Halverson Studio',
            'contact' => 'Avery',
            'contact_email' => 'avery@halverson.studio',
        ]);

        $this->assertSame(1, Activity::where('action', ActivityAction::ClientCreated->value)->count());
        $this->assertSame(
            'Halverson Studio',
            Activity::where('action', ActivityAction::ClientCreated->value)->first()->payload['subject_name']
        );
    }

    #[Test]
    public function creating_a_package_logs_a_package_created_activity()
    {
        [$tenant] = $this->signIn();

        Package::create([
            'tenant_id' => $tenant->id,
            'name' => 'Standard brochure site',
        ]);

        $this->assertSame(1, Activity::where('action', ActivityAction::PackageCreated->value)->count());
    }

    #[Test]
    public function activities_are_scoped_by_tenant()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $this->actingAs($userB)->session(['tenant_id' => $tenantB->id]);
        Package::create(['tenant_id' => $tenantB->id, 'name' => 'Not mine']);
        auth()->logout();
        session()->forget('tenant_id');

        $this->actingAs($userA)->session(['tenant_id' => $tenantA->id]);
        Package::create(['tenant_id' => $tenantA->id, 'name' => 'Mine']);

        $subjectNames = Activity::pluck('payload')
            ->map(fn ($payload) => $payload['subject_name'] ?? null)
            ->filter()
            ->all();

        $this->assertContains('Mine', $subjectNames);
        $this->assertNotContains('Not mine', $subjectNames);
    }

    #[Test]
    public function the_dashboard_renders_the_activity_feed()
    {
        [, $user] = $this->signIn();
        $client = Client::factory()->create(['name' => 'Halverson Studio']);

        Proposal::factory()->create([
            'name' => 'Brand identity system',
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Caleb Evans created Brand identity system')
            ->assertSeeText('Caleb Evans added client Halverson Studio');
    }

    #[Test]
    public function the_dashboard_shows_the_new_quick_action_buttons()
    {
        $this->signIn();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('New feature')
            ->assertSeeText('New package')
            ->assertSeeText('New proposal');
    }
}
