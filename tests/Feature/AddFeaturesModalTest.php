<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Livewire\Admin\Proposals\AddFeaturesModal;
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

class AddFeaturesModalTest extends TestCase
{
    use RefreshDatabase;

    private function proposalFixture(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        Setting::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        return [$tenant, $user, $proposal];
    }

    #[Test]
    public function picking_a_feature_snapshots_it_onto_the_proposal()
    {
        [$tenant, , $proposal] = $this->proposalFixture();
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Consult']);

        Livewire::test(AddFeaturesModal::class, ['proposalId' => $proposal->id])
            ->call('addFeature', $feature->id);

        $this->assertSame(1, $proposal->features()->count());
        $snapshot = $proposal->features()->first();
        $this->assertSame('Consult', $snapshot->name);
        $this->assertSame($feature->id, $snapshot->source_feature_id);
    }

    #[Test]
    public function picking_a_child_auto_attaches_its_parent_when_missing()
    {
        [$tenant, , $proposal] = $this->proposalFixture();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $child = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        Livewire::test(AddFeaturesModal::class, ['proposalId' => $proposal->id])
            ->call('addFeature', $child->id);

        $this->assertSame(2, $proposal->features()->count());
        $parentSnap = $proposal->features()->where('source_feature_id', $parent->id)->first();
        $childSnap = $proposal->features()->where('source_feature_id', $child->id)->first();

        $this->assertNotNull($parentSnap);
        $this->assertNotNull($childSnap);
        $this->assertNull($parentSnap->parent_id);
        $this->assertSame($parentSnap->id, $childSnap->parent_id);
    }

    #[Test]
    public function picking_a_child_reuses_an_existing_parent_snapshot()
    {
        [$tenant, , $proposal] = $this->proposalFixture();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $child = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        $existingParentSnap = new FinalFeature([
            'name' => 'Blog',
            'description' => '—',
            'price' => 10,
            'quantity' => 1,
            'optional' => false,
            'source_feature_id' => $parent->id,
            'order' => 1,
        ]);
        $existingParentSnap->proposal()->associate($proposal);
        $existingParentSnap->save();

        Livewire::test(AddFeaturesModal::class, ['proposalId' => $proposal->id])
            ->call('addFeature', $child->id);

        $this->assertSame(2, $proposal->features()->count());
        $parentSnaps = $proposal->features()->where('source_feature_id', $parent->id)->get();
        $this->assertCount(1, $parentSnaps);

        $childSnap = $proposal->features()->where('source_feature_id', $child->id)->first();
        $this->assertSame($existingParentSnap->id, $childSnap->parent_id);
    }

    #[Test]
    public function a_feature_already_on_the_proposal_is_not_added_again()
    {
        [$tenant, , $proposal] = $this->proposalFixture();
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);

        $snap = new FinalFeature([
            'name' => $feature->name,
            'description' => '—',
            'price' => 10,
            'quantity' => 1,
            'optional' => false,
            'source_feature_id' => $feature->id,
            'order' => 1,
        ]);
        $snap->proposal()->associate($proposal);
        $snap->save();

        Livewire::test(AddFeaturesModal::class, ['proposalId' => $proposal->id])
            ->call('addFeature', $feature->id);

        $this->assertSame(1, $proposal->features()->count());
    }

    #[Test]
    public function new_parent_rows_are_appended_to_the_end_of_the_order_sequence()
    {
        [$tenant, , $proposal] = $this->proposalFixture();
        $existing = new FinalFeature([
            'name' => 'Existing', 'description' => '—', 'price' => 10,
            'quantity' => 1, 'optional' => false, 'order' => 1,
        ]);
        $existing->proposal()->associate($proposal);
        $existing->save();

        $feature = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'New parent']);

        Livewire::test(AddFeaturesModal::class, ['proposalId' => $proposal->id])
            ->call('addFeature', $feature->id);

        $snapshot = $proposal->features()->where('source_feature_id', $feature->id)->first();
        $this->assertSame(2, $snapshot->order);
    }
}
