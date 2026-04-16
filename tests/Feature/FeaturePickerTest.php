<?php

namespace Tests\Feature;

use App\Livewire\Admin\Features\FeaturePicker;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeaturePickerTest extends TestCase
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
    public function the_picker_lists_features_grouped_by_parent()
    {
        [$tenant] = $this->signIn();
        $parent = Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Blog']);
        Feature::factory()->childOf($parent)->create(['name' => 'Categories']);
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Standalone thing']);

        Livewire::test(FeaturePicker::class)
            ->assertSee('Blog')
            ->assertSee('Categories')
            ->assertSee('Standalone thing');
    }

    #[Test]
    public function picking_a_feature_dispatches_feature_picked()
    {
        [$tenant] = $this->signIn();
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(FeaturePicker::class)
            ->call('pick', $feature->id)
            ->assertDispatched('feature-picked', featureId: $feature->id);
    }

    #[Test]
    public function picking_a_disabled_feature_does_not_dispatch()
    {
        [$tenant] = $this->signIn();
        $feature = Feature::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(FeaturePicker::class, ['disabledIds' => [$feature->id]])
            ->call('pick', $feature->id)
            ->assertNotDispatched('feature-picked');
    }

    #[Test]
    public function the_search_filter_narrows_the_list()
    {
        [$tenant] = $this->signIn();
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Findable widget']);
        Feature::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Other thing']);

        Livewire::test(FeaturePicker::class)
            ->set('search', 'Findable')
            ->assertSee('Findable widget')
            ->assertDontSee('Other thing');
    }
}
