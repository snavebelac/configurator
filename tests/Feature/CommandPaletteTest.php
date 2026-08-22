<?php

namespace Tests\Feature;

use App\Livewire\Admin\Shared\CommandPalette;
use App\Models\Client;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(?Tenant $tenant = null): array
    {
        $tenant ??= Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_palette_is_closed_by_default(): void
    {
        $this->signIn();

        Livewire::test(CommandPalette::class)
            ->assertSet('open', false)
            ->assertSet('query', '');
    }

    #[Test]
    public function the_open_palette_event_opens_it(): void
    {
        $this->signIn();

        Livewire::test(CommandPalette::class)
            ->dispatch('open-palette')
            ->assertSet('open', true);
    }

    #[Test]
    public function closing_the_palette_clears_the_query(): void
    {
        $this->signIn();

        Livewire::test(CommandPalette::class)
            ->set('open', true)
            ->set('query', 'halverson')
            ->call('closePalette')
            ->assertSet('open', false)
            ->assertSet('query', '');
    }

    #[Test]
    public function with_an_empty_query_the_palette_shows_suggested_actions_and_recent_proposals(): void
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
        ]);

        Livewire::test(CommandPalette::class)
            ->assertSeeText('Create new proposal')
            ->assertSeeText('Brand identity system');
    }

    #[Test]
    public function searching_finds_matching_proposals(): void
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
        ]);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'E-commerce launch',
        ]);

        Livewire::test(CommandPalette::class)
            ->set('query', 'identity')
            ->assertSeeText('Brand identity system')
            ->assertDontSeeText('E-commerce launch');
    }

    #[Test]
    public function searching_finds_matching_clients_features_and_packages(): void
    {
        [$tenant] = $this->signIn();
        Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson Studio']);
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson onboarding']);
        Package::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson signature bundle']);

        Livewire::test(CommandPalette::class)
            ->set('query', 'halverson')
            ->assertSeeText('Halverson Studio')
            ->assertSeeText('Halverson onboarding')
            ->assertSeeText('Halverson signature bundle');
    }

    #[Test]
    public function results_do_not_leak_across_tenants(): void
    {
        $otherTenant = Tenant::factory()->create();
        Client::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'Cross-tenant Client']);
        Proposal::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => User::factory()->create(['tenant_id' => $otherTenant->id])->id,
            'client_id' => Client::factory()->create(['tenant_id' => $otherTenant->id])->id,
            'name' => 'Cross-tenant proposal',
        ]);

        $this->signIn();

        Livewire::test(CommandPalette::class)
            ->set('query', 'cross-tenant')
            ->assertDontSeeText('Cross-tenant Client')
            ->assertDontSeeText('Cross-tenant proposal');
    }

    #[Test]
    public function the_topbar_renders_a_search_trigger_that_opens_the_palette(): void
    {
        $this->signIn();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Search anything')
            ->assertSee("Livewire.dispatch('open-palette')", false)
            ->assertSeeLivewire(CommandPalette::class);
    }
}
