<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function proposalFixture(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true, 'name' => 'Caleb', 'last_name' => 'Evans']);
        Setting::factory()->create(['tenant_id' => $tenant->id, 'tax_name' => 'VAT', 'tax_rate' => 20]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Halverson Studio']);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
            'name' => 'Brand identity system',
        ]);

        return [$tenant, $user, $proposal];
    }

    #[Test]
    public function the_preview_renders_masthead_and_features()
    {
        [$tenant, , $proposal] = $this->proposalFixture();

        $required = new FinalFeature([
            'name' => 'Logo design',
            'description' => 'Three rounds of exploration.',
            'price' => 4800,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $required->proposal()->associate($proposal);
        $required->save();

        $optional = new FinalFeature([
            'name' => 'Motion identity',
            'description' => 'Logo animation kit.',
            'price' => 3600,
            'quantity' => 1,
            'optional' => true,
            'order' => 2,
        ]);
        $optional->proposal()->associate($proposal);
        $optional->save();

        $this->get(route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('A Configurator proposal')
            ->assertSeeText('Brand identity system')
            ->assertSeeText('Halverson Studio')
            ->assertSeeText('Caleb Evans')
            ->assertSeeText('Logo design')
            ->assertSeeText('Motion identity')
            ->assertSeeText('VAT');
    }

    #[Test]
    public function the_preview_renders_an_empty_state_when_no_features()
    {
        [, , $proposal] = $this->proposalFixture();

        $this->get(route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Nothing to show yet.');
    }

    #[Test]
    public function the_preview_groups_child_features_under_their_parent()
    {
        [, , $proposal] = $this->proposalFixture();

        $parent = new FinalFeature([
            'name' => 'Blog',
            'description' => 'Blogging engine.',
            'price' => 1000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $parent->proposal()->associate($proposal);
        $parent->save();

        $child = new FinalFeature([
            'name' => 'Categories',
            'description' => 'Organise posts.',
            'price' => 200,
            'quantity' => 1,
            'optional' => true,
            'parent_id' => $parent->id,
            'order' => 0,
        ]);
        $child->proposal()->associate($proposal);
        $child->save();

        $this->get(route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Blog')
            ->assertSeeText('Categories');
    }
}
