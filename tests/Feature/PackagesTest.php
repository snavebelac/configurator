<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Livewire\Admin\Packages\PackageCreate;
use App\Livewire\Admin\Packages\PackageEdit;
use App\Livewire\Admin\Proposals\AddPackageModal;
use App\Livewire\Admin\Proposals\ProposalCreate;
use App\Models\Client;
use App\Models\Feature;
use App\Models\FeaturePackage;
use App\Models\Package;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PackagesTest extends TestCase
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

    private function proposalFixture(): array
    {
        [$tenant, $user] = $this->signIn();
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => Status::DRAFT,
        ]);

        return [$tenant, $user, $client, $proposal];
    }

    #[Test]
    public function a_package_has_many_features_with_pivot_overrides()
    {
        [$tenant] = $this->signIn();

        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);

        $package->features()->attach($feature->id, [
            'tenant_id' => $tenant->id,
            'quantity' => 5,
            'optional' => true,
            'price' => 15.00, // pounds — setter stores as pence
        ]);

        $attached = $package->features()->first();

        $this->assertNotNull($attached);
        $this->assertSame(5, $attached->pivot->quantity);
        $this->assertTrue($attached->pivot->optional);
        $this->assertSame(15.0, $attached->pivot->price);
    }

    #[Test]
    public function the_package_list_page_renders_with_an_empty_state()
    {
        $this->signIn();

        $this->get(route('dashboard.packages'))
            ->assertOk()
            ->assertSeeText('Packages.')
            ->assertSeeText('No packages yet');
    }

    #[Test]
    public function the_package_list_page_lists_existing_packages()
    {
        [$tenant] = $this->signIn();
        Package::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Standard brochure site']);

        $this->get(route('dashboard.packages'))
            ->assertOk()
            ->assertSeeText('Standard brochure site');
    }

    #[Test]
    public function create_package_persists_and_redirects_to_edit()
    {
        [$tenant] = $this->signIn();

        Livewire::test(PackageCreate::class)
            ->set('name', 'Standard brochure site')
            ->set('description', 'The usual set of pages.')
            ->call('createPackage');

        $package = Package::firstOrFail();
        $this->assertSame('Standard brochure site', $package->name);
        $this->assertSame($tenant->id, $package->tenant_id);
    }

    #[Test]
    public function feature_picker_events_add_features_to_the_package()
    {
        [$tenant] = $this->signIn();
        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(PackageEdit::class, ['package' => $package])
            ->call('addFeature', $feature->id);

        $this->assertSame(1, $package->features()->count());
    }

    #[Test]
    public function a_feature_already_on_a_package_is_not_added_again()
    {
        [$tenant] = $this->signIn();
        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $package->features()->attach($feature->id, ['tenant_id' => $tenant->id]);

        Livewire::test(PackageEdit::class, ['package' => $package])
            ->call('addFeature', $feature->id);

        $this->assertSame(1, $package->features()->count());
    }

    #[Test]
    public function pivot_overrides_can_be_updated()
    {
        [$tenant] = $this->signIn();
        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $package->features()->attach($feature->id, ['tenant_id' => $tenant->id]);

        Livewire::test(PackageEdit::class, ['package' => $package])
            ->call('updatePivot', $feature->id, 'quantity', '4')
            ->call('updatePivot', $feature->id, 'price', '25.50')
            ->call('updatePivot', $feature->id, 'optional', 'optional');

        $pivot = $package->features()->first()->pivot;
        $this->assertSame(4, $pivot->quantity);
        $this->assertSame(25.5, $pivot->price);
        $this->assertTrue($pivot->optional);
    }

    #[Test]
    public function adding_a_package_to_a_proposal_bulk_snapshots_its_features()
    {
        [$tenant, , , $proposal] = $this->proposalFixture();

        $package = Package::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Brochure site']);
        $home = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Home page']);
        $about = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'About page']);
        $package->features()->attach([
            $home->id => ['tenant_id' => $tenant->id],
            $about->id => ['tenant_id' => $tenant->id],
        ]);

        Livewire::test(AddPackageModal::class, ['proposalId' => $proposal->id])
            ->call('addPackage', $package->id);

        $this->assertSame(2, $proposal->features()->count());
        $this->assertTrue($proposal->features()->where('source_feature_id', $home->id)->exists());
        $this->assertTrue($proposal->features()->where('source_feature_id', $about->id)->exists());
    }

    #[Test]
    public function package_pivot_overrides_are_applied_when_snapshotting_to_a_proposal()
    {
        [$tenant, , , $proposal] = $this->proposalFixture();

        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 100,
            'quantity' => 1,
            'optional' => false,
        ]);

        $package->features()->attach($feature->id, [
            'tenant_id' => $tenant->id,
            'quantity' => 7,
            'price' => 25.00,
            'optional' => true,
        ]);

        Livewire::test(AddPackageModal::class, ['proposalId' => $proposal->id])
            ->call('addPackage', $package->id);

        $snapshot = $proposal->features()->where('source_feature_id', $feature->id)->first();
        $this->assertNotNull($snapshot);
        $this->assertSame(7, $snapshot->quantity);
        $this->assertSame(25.0, $snapshot->price);
        $this->assertTrue($snapshot->optional);
    }

    #[Test]
    public function adding_a_package_skips_features_already_on_the_proposal()
    {
        [$tenant, , , $proposal] = $this->proposalFixture();

        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $alreadyOn = Feature::factory()->create(['tenant_id' => $tenant->id]);
        $newOne = Feature::factory()->create(['tenant_id' => $tenant->id]);

        $package->features()->attach([
            $alreadyOn->id => ['tenant_id' => $tenant->id],
            $newOne->id => ['tenant_id' => $tenant->id],
        ]);

        // Seed the proposal with the "already on" feature already snapshotted
        $proposal->features()->create([
            'tenant_id' => $tenant->id,
            'name' => $alreadyOn->name,
            'description' => $alreadyOn->description,
            'price' => 10,
            'quantity' => 1,
            'optional' => false,
            'source_feature_id' => $alreadyOn->id,
            'order' => 1,
        ]);

        Livewire::test(AddPackageModal::class, ['proposalId' => $proposal->id])
            ->call('addPackage', $package->id);

        $this->assertSame(2, $proposal->features()->count());
        $this->assertSame(
            1,
            $proposal->features()->where('source_feature_id', $alreadyOn->id)->count()
        );
    }

    #[Test]
    public function adding_a_package_with_a_child_feature_auto_attaches_the_parent()
    {
        [$tenant, , , $proposal] = $this->proposalFixture();

        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        $child = Feature::factory()->childOf($parent)->create(['name' => 'Categories']);

        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $package->features()->attach($child->id, ['tenant_id' => $tenant->id]);

        Livewire::test(AddPackageModal::class, ['proposalId' => $proposal->id])
            ->call('addPackage', $package->id);

        $this->assertSame(2, $proposal->features()->count());
        $parentSnap = $proposal->features()->where('source_feature_id', $parent->id)->first();
        $childSnap = $proposal->features()->where('source_feature_id', $child->id)->first();

        $this->assertNotNull($parentSnap);
        $this->assertNotNull($childSnap);
        $this->assertSame($parentSnap->id, $childSnap->parent_id);
    }

    #[Test]
    public function picking_a_package_on_proposal_create_adds_its_features_with_overrides()
    {
        [$tenant, , $client] = $this->proposalFixture();

        $package = Package::factory()->create(['tenant_id' => $tenant->id]);
        $feature = Feature::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 100,
            'quantity' => 1,
        ]);
        $package->features()->attach($feature->id, [
            'tenant_id' => $tenant->id,
            'quantity' => 3,
            'price' => 99.00,
        ]);

        $component = Livewire::test(ProposalCreate::class)
            ->set('name', 'Pkg test')
            ->set('clientId', $client->id)
            ->call('handlePackagePicked', $package->id);

        $this->assertContains($feature->id, $component->get('selectedFeatureIds'));

        $component->call('createProposal');

        $proposal = Proposal::where('name', 'Pkg test')->firstOrFail();
        $snapshot = $proposal->features()->where('source_feature_id', $feature->id)->first();

        $this->assertNotNull($snapshot);
        $this->assertSame(3, $snapshot->quantity);
        $this->assertSame(99.0, $snapshot->price);
    }

    #[Test]
    public function packages_and_feature_package_pivots_are_scoped_by_tenant()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'active' => true]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'active' => true]);

        // Tenant B data first, then log out
        $this->actingAs($userB)->session(['tenant_id' => $tenantB->id]);
        $packageB = Package::factory()->create(['name' => 'Not mine']);
        $featureB = Feature::factory()->create();
        $packageB->features()->attach($featureB->id);
        auth()->logout();
        session()->forget('tenant_id');

        // Tenant A
        $this->actingAs($userA)->session(['tenant_id' => $tenantA->id]);
        $packageA = Package::factory()->create(['name' => 'Mine']);
        $featureA = Feature::factory()->create();
        $packageA->features()->attach($featureA->id);

        $packages = Package::pluck('name')->all();
        $this->assertContains('Mine', $packages);
        $this->assertNotContains('Not mine', $packages);

        $this->assertSame(1, FeaturePackage::count());
        $this->assertSame($tenantA->id, FeaturePackage::first()->tenant_id);
    }
}
