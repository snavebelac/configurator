<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Livewire\Admin\Features\FeatureModal;
use App\Livewire\Admin\Features\FeaturesList;
use App\Livewire\Admin\Proposals\ProposalCreate;
use App\Livewire\Admin\Proposals\ProposalEdit;
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

class NestedFeaturesTest extends TestCase
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
    public function a_feature_has_parent_and_children_relationships()
    {
        [$tenant] = $this->signIn();

        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $categories = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);
        $tags = Feature::factory()->childOf($parent)->create(['name' => 'Tags']);

        $this->assertTrue($parent->isRoot());
        $this->assertFalse($categories->isRoot());
        $this->assertTrue($categories->isChild());
        $this->assertSame($parent->id, $categories->parent->id);
        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->pluck('id')->contains($tags->id));
    }

    #[Test]
    public function the_roots_scope_only_returns_standalone_or_parent_features()
    {
        [$tenant] = $this->signIn();

        $standalone = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $child = Feature::factory()->childOf($parent)->create();

        $roots = Feature::roots()->pluck('id');

        $this->assertTrue($roots->contains($standalone->id));
        $this->assertTrue($roots->contains($parent->id));
        $this->assertFalse($roots->contains($child->id));
    }

    #[Test]
    public function deleting_a_parent_feature_cascades_to_its_children()
    {
        [$tenant] = $this->signIn();

        $parent = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $child = Feature::factory()->childOf($parent)->create();

        $parent->delete();

        $this->assertSoftDeleted('features', ['id' => $parent->id]);
        $this->assertSoftDeleted('features', ['id' => $child->id]);
    }

    #[Test]
    public function the_feature_modal_creates_a_child_under_an_existing_parent()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);

        Livewire::test(FeatureModal::class)
            ->set('name', 'Categories')
            ->set('description', 'Group posts into buckets')
            ->set('price', '25.00')
            ->set('quantity', 1)
            ->set('parentId', $parent->id)
            ->call('save');

        $child = Feature::where('name', 'Categories')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
    }

    #[Test]
    public function the_feature_modal_rejects_a_child_as_a_parent()
    {
        [$tenant] = $this->signIn();
        $root = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $child = Feature::factory()->childOf($root)->create();

        Livewire::test(FeatureModal::class)
            ->set('name', 'New thing')
            ->set('description', 'Something')
            ->set('price', '10.00')
            ->set('quantity', 1)
            ->set('parentId', $child->id)
            ->call('save')
            ->assertHasErrors(['parentId']);
    }

    #[Test]
    public function editing_a_standalone_feature_can_reparent_it_under_another_feature()
    {
        [$tenant] = $this->signIn();
        $newParent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Categories']);

        Livewire::test(FeatureModal::class, ['featureId' => $feature->id])
            ->set('parentId', $newParent->id)
            ->call('save');

        $this->assertSame($newParent->id, $feature->fresh()->parent_id);
    }

    #[Test]
    public function editing_a_child_feature_can_promote_it_back_to_standalone()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $child = Feature::factory()->childOf($parent)->create();

        Livewire::test(FeatureModal::class, ['featureId' => $child->id])
            ->set('parentId', null)
            ->call('save');

        $this->assertNull($child->fresh()->parent_id);
    }

    #[Test]
    public function a_feature_with_children_cannot_itself_be_placed_under_a_parent()
    {
        [$tenant] = $this->signIn();
        $otherRoot = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id]);
        Feature::factory()->childOf($parent)->create();

        Livewire::test(FeatureModal::class, ['featureId' => $parent->id])
            ->set('parentId', $otherRoot->id)
            ->call('save')
            ->assertHasErrors(['parentId']);

        $this->assertNull($parent->fresh()->parent_id);
    }

    #[Test]
    public function the_features_list_groups_children_beneath_their_parent()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        Livewire::test(FeaturesList::class)
            ->assertSee('Blog')
            ->assertSee('Categories')
            ->assertSee('1 child');
    }

    #[Test]
    public function selecting_a_child_auto_attaches_its_parent()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $child = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        $component = Livewire::test(ProposalCreate::class)
            ->call('selectFeature', $child->id);

        $selected = $component->get('selectedFeatureIds');
        $this->assertContains($parent->id, $selected);
        $this->assertContains($child->id, $selected);
    }

    #[Test]
    public function removing_a_parent_cascades_to_its_selected_children()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $childA = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);
        $childB = Feature::factory()->childOf($parent)->create(['name' => 'Tags']);

        $component = Livewire::test(ProposalCreate::class)
            ->call('selectFeature', $parent->id)
            ->call('selectFeature', $childA->id)
            ->call('selectFeature', $childB->id)
            ->call('removeFeature', $parent->id);

        $selected = $component->get('selectedFeatureIds');
        $this->assertNotContains($parent->id, $selected);
        $this->assertNotContains($childA->id, $selected);
        $this->assertNotContains($childB->id, $selected);
    }

    #[Test]
    public function finalising_a_proposal_preserves_the_parent_link_on_snapshots()
    {
        [$tenant] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $child = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        Livewire::test(ProposalCreate::class)
            ->set('name', 'Nested test')
            ->set('clientId', $client->id)
            ->call('selectFeature', $child->id)
            ->call('createProposal');

        $proposal = Proposal::firstOrFail();
        $snapshots = $proposal->features()->get();

        $this->assertSame(2, $snapshots->count());

        $rootSnapshot = $snapshots->firstWhere('name', 'Blog');
        $childSnapshot = $snapshots->firstWhere('name', 'Categories');

        $this->assertNotNull($rootSnapshot);
        $this->assertNotNull($childSnapshot);
        $this->assertNull($rootSnapshot->parent_id);
        $this->assertSame($rootSnapshot->id, $childSnapshot->parent_id);
    }

    #[Test]
    public function snapshot_assigns_alphabetical_order_to_parent_rows()
    {
        [$tenant] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $zeta = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Zeta']);
        $alpha = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alpha']);
        $midChild = Feature::factory()->childOf($alpha)->create(['name' => 'Mid child']);

        Livewire::test(ProposalCreate::class)
            ->set('name', 'Order test')
            ->set('clientId', $client->id)
            ->call('selectFeature', $zeta->id)
            ->call('selectFeature', $midChild->id)
            ->call('createProposal');

        $proposal = Proposal::firstOrFail();
        $parents = $proposal->features()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->pluck('name', 'order')
            ->all();

        $this->assertSame('Alpha', $parents[1]);
        $this->assertSame('Zeta', $parents[2]);
    }

    #[Test]
    public function reorder_parents_updates_final_feature_order_values()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        $makeParent = function (string $name, int $order) use ($proposal) {
            $ff = new FinalFeature([
                'name' => $name,
                'description' => '—',
                'price' => 10,
                'quantity' => 1,
                'optional' => false,
                'order' => $order,
            ]);
            $ff->proposal()->associate($proposal);
            $ff->save();

            return $ff;
        };

        $alpha = $makeParent('Alpha', 1);
        $beta = $makeParent('Beta', 2);
        $gamma = $makeParent('Gamma', 3);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('reorderParents', $gamma->id, 0);

        $ordered = $proposal->features()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->pluck('name')
            ->all();

        $this->assertSame(['Gamma', 'Alpha', 'Beta'], $ordered);
    }

    #[Test]
    public function reorder_parents_keeps_children_grouped_under_their_parent()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        $makeParent = function (string $name, int $order) use ($proposal) {
            $ff = new FinalFeature([
                'name' => $name, 'description' => '—', 'price' => 10,
                'quantity' => 1, 'optional' => false, 'order' => $order,
            ]);
            $ff->proposal()->associate($proposal);
            $ff->save();

            return $ff;
        };
        $makeChild = function (string $name, FinalFeature $parent) use ($proposal) {
            $ff = new FinalFeature([
                'name' => $name, 'description' => '—', 'price' => 5,
                'quantity' => 1, 'optional' => true,
                'parent_id' => $parent->id, 'order' => 0,
            ]);
            $ff->proposal()->associate($proposal);
            $ff->save();

            return $ff;
        };

        $alpha = $makeParent('Alpha', 1);
        $beta = $makeParent('Beta', 2);
        $makeChild('Alpha-child', $alpha);
        $makeChild('Beta-child', $beta);

        Livewire::test(ProposalEdit::class, ['proposal' => $proposal])
            ->call('reorderParents', $beta->id, 0);

        // Beta is now first; children are still keyed to their parents
        $this->assertSame($alpha->fresh()->parent_id, null);
        $this->assertSame($beta->fresh()->parent_id, null);
        $this->assertSame(
            ['Alpha-child'],
            $alpha->children()->pluck('name')->all()
        );
        $this->assertSame(
            ['Beta-child'],
            $beta->children()->pluck('name')->all()
        );
    }

    #[Test]
    public function deleting_a_parent_final_feature_cascades_to_its_child_snapshots()
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        $parentFinal = new FinalFeature([
            'name' => 'Blog',
            'description' => 'The blog',
            'price' => 100,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $parentFinal->proposal()->associate($proposal);
        $parentFinal->save();

        $childFinal = new FinalFeature([
            'name' => 'Categories',
            'description' => 'Buckets',
            'price' => 25,
            'quantity' => 1,
            'optional' => true,
            'parent_id' => $parentFinal->id,
            'order' => 2,
        ]);
        $childFinal->proposal()->associate($proposal);
        $childFinal->save();

        Livewire::test(ProposalFeatureForm::class, ['finalFeatureId' => $parentFinal->id])
            ->call('removeFinalFeature');

        $this->assertSoftDeleted('final_features', ['id' => $parentFinal->id]);
        $this->assertSoftDeleted('final_features', ['id' => $childFinal->id]);
    }
}
