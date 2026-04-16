<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Livewire\Admin\Proposals\ProposalCreate;
use App\Livewire\Admin\Proposals\ProposalFeatureForm;
use App\Models\Client;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_create_page_renders_with_empty_state()
    {
        $this->signIn();

        $this->get(route('dashboard.proposal.create'))
            ->assertOk()
            ->assertSeeText('New proposal.')
            ->assertSeeText('Feature library')
            ->assertSeeText('Nothing selected yet');
    }

    #[Test]
    public function features_from_the_library_can_be_selected_and_removed()
    {
        [$tenant] = $this->signIn();
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Wireframes']);

        $component = Livewire::test(ProposalCreate::class)
            ->call('selectFeature', $feature->id);

        $this->assertContains($feature->id, $component->get('selectedFeatureIds'));

        $component->call('removeFeature', $feature->id);

        $this->assertNotContains($feature->id, $component->get('selectedFeatureIds'));
    }

    #[Test]
    public function creating_a_proposal_requires_name_client_and_features()
    {
        $this->signIn();

        Livewire::test(ProposalCreate::class)
            ->call('createProposal')
            ->assertHasErrors(['name', 'clientId', 'selectedFeatureIds']);
    }

    #[Test]
    public function a_proposal_is_created_with_feature_snapshots_and_redirects_to_edit()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $featureA = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $featureB = Feature::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(ProposalCreate::class)
            ->set('name', 'Brand identity system')
            ->set('clientId', $client->id)
            ->call('selectFeature', $featureA->id)
            ->call('selectFeature', $featureB->id)
            ->call('createProposal')
            ->assertRedirectToRoute('dashboard.proposal.edit', ['proposal' => Proposal::first()->id]);

        $proposal = Proposal::first();
        $this->assertSame('Brand identity system', $proposal->name);
        $this->assertSame(Status::DRAFT, $proposal->status);
        $this->assertSame($client->id, $proposal->client_id);
        $this->assertSame($user->id, $proposal->user_id);
        $this->assertSame(2, $proposal->features()->count());
    }

    #[Test]
    public function the_edit_page_renders_with_the_proposal_name_and_meta()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Northwind Trading']);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
            'name' => 'Brand identity system',
        ]);

        $this->get(route('dashboard.proposal.edit', ['proposal' => $proposal->id]))
            ->assertOk()
            ->assertSeeText('Brand identity system')
            ->assertSeeText('Northwind Trading')
            ->assertSeeText('Editing · Draft');
    }

    #[Test]
    public function editing_a_final_feature_updates_the_database()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);
        $finalFeature = new FinalFeature([
            'name' => 'Original',
            'description' => 'old',
            'price' => 100,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $finalFeature->proposal()->associate($proposal);
        $finalFeature->save();

        Livewire::test(ProposalFeatureForm::class, ['finalFeatureId' => $finalFeature->id])
            ->set('name', 'Updated name')
            ->set('quantity', 3)
            ->set('optional', true);

        $finalFeature->refresh();
        $this->assertSame('Updated name', $finalFeature->name);
        $this->assertSame(3, $finalFeature->quantity);
        $this->assertTrue($finalFeature->optional);
    }

    #[Test]
    public function a_final_feature_can_be_removed_from_the_proposal()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);
        $finalFeature = new FinalFeature([
            'name' => 'Doomed',
            'description' => 'x',
            'price' => 100,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $finalFeature->proposal()->associate($proposal);
        $finalFeature->save();

        Livewire::test(ProposalFeatureForm::class, ['finalFeatureId' => $finalFeature->id])
            ->call('removeFinalFeature');

        $this->assertSoftDeleted('final_features', ['id' => $finalFeature->id]);
    }
}
