<?php

namespace Tests\Feature;

use App\Livewire\Admin\Features\FeaturesList;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeaturesListTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_features_page_renders_with_an_empty_state()
    {
        $this->signIn();

        $this->get(route('dashboard.features'))
            ->assertOk()
            ->assertSeeText('Features.')
            ->assertSeeText('No features yet');
    }

    #[Test]
    public function the_features_page_lists_existing_features()
    {
        [$tenant] = $this->signIn();

        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Logo design', 'optional' => false]);
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Extra revisions', 'optional' => true]);

        $this->get(route('dashboard.features'))
            ->assertOk()
            ->assertSeeText('Logo design')
            ->assertSeeText('Extra revisions')
            ->assertSeeText('Optional');
    }

    #[Test]
    public function the_search_matches_feature_names()
    {
        [$tenant] = $this->signIn();

        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Logo design']);
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Extra revisions']);

        Livewire::test(FeaturesList::class)
            ->set('search', 'Logo')
            ->assertSee('Logo design')
            ->assertDontSee('Extra revisions');
    }

    #[Test]
    public function a_feature_can_be_deleted_from_the_list()
    {
        [$tenant] = $this->signIn();

        $feature = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Doomed']);

        Livewire::test(FeaturesList::class)
            ->call('delete', $feature->id);

        $this->assertSoftDeleted('features', ['id' => $feature->id]);
    }
}
