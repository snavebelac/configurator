<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(?Tenant $tenant = null): array
    {
        $tenant ??= Tenant::factory()->create();
        Setting::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user, $client];
    }

    private function makeProposal(Tenant $tenant, User $user, Client $client): Proposal
    {
        return Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);
    }

    #[Test]
    public function the_first_proposal_for_a_tenant_gets_a_reference_of_the_form_year_slash_0001(): void
    {
        Carbon::setTestNow('2026-04-27 10:00:00');
        [$tenant, $user, $client] = $this->signIn();

        $proposal = $this->makeProposal($tenant, $user, $client);

        $this->assertSame('2026/0001', $proposal->reference);
    }

    #[Test]
    public function references_increment_within_a_year(): void
    {
        Carbon::setTestNow('2026-04-27 10:00:00');
        [$tenant, $user, $client] = $this->signIn();

        $first = $this->makeProposal($tenant, $user, $client);
        $second = $this->makeProposal($tenant, $user, $client);
        $third = $this->makeProposal($tenant, $user, $client);

        $this->assertSame('2026/0001', $first->reference);
        $this->assertSame('2026/0002', $second->reference);
        $this->assertSame('2026/0003', $third->reference);
    }

    #[Test]
    public function the_sequence_resets_at_the_start_of_a_new_year(): void
    {
        [$tenant, $user, $client] = $this->signIn();

        Carbon::setTestNow('2025-12-30 10:00:00');
        $endOf2025 = $this->makeProposal($tenant, $user, $client);

        Carbon::setTestNow('2026-01-02 10:00:00');
        $startOf2026 = $this->makeProposal($tenant, $user, $client);

        $this->assertSame('2025/0001', $endOf2025->reference);
        $this->assertSame('2026/0001', $startOf2026->reference);
    }

    #[Test]
    public function references_are_scoped_per_tenant(): void
    {
        Carbon::setTestNow('2026-04-27 10:00:00');

        $tenantA = Tenant::factory()->create();
        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $clientA = Client::factory()->create(['tenant_id' => $tenantA->id]);
        $this->actingAs($userA)->session(['tenant_id' => $tenantA->id]);
        $a1 = $this->makeProposal($tenantA, $userA, $clientA);
        $a2 = $this->makeProposal($tenantA, $userA, $clientA);

        $tenantB = Tenant::factory()->create();
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $clientB = Client::factory()->create(['tenant_id' => $tenantB->id]);
        $this->actingAs($userB)->session(['tenant_id' => $tenantB->id]);
        $b1 = $this->makeProposal($tenantB, $userB, $clientB);

        $this->assertSame('2026/0001', $a1->reference);
        $this->assertSame('2026/0002', $a2->reference);
        $this->assertSame('2026/0001', $b1->reference);
    }

    #[Test]
    public function deleting_a_proposal_does_not_recycle_its_reference(): void
    {
        Carbon::setTestNow('2026-04-27 10:00:00');
        [$tenant, $user, $client] = $this->signIn();

        $first = $this->makeProposal($tenant, $user, $client);
        $second = $this->makeProposal($tenant, $user, $client);
        $third = $this->makeProposal($tenant, $user, $client);

        $second->delete();

        $fourth = $this->makeProposal($tenant, $user, $client);

        $this->assertSame('2026/0001', $first->reference);
        $this->assertSame('2026/0003', $third->reference);
        $this->assertSame('2026/0004', $fourth->reference);
    }

    #[Test]
    public function the_reference_renders_on_the_admin_preview_and_proposal_edit_pages(): void
    {
        Carbon::setTestNow('2026-04-27 10:00:00');
        [$tenant, $user, $client] = $this->signIn();
        $proposal = $this->makeProposal($tenant, $user, $client);

        $this->get(route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('2026/0001');

        $this->get(route('dashboard.proposal.edit', ['proposal' => $proposal->id]))
            ->assertOk()
            ->assertSeeText('2026/0001');
    }
}
