<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function a_model_has_a_tenant_id_on_the_migration()
    {
        $this->artisan('make:model Test -m');
        $filename = File::glob(database_path('migrations/*_create_tests_table.php'))[0];
        $this->assertFileExists($filename);
        $this->assertStringContainsString('$table->unsignedBigInteger(\'tenant_id\')->index();', File::get($filename));
        File::delete($filename);
        File::delete(app_path('Models/Test.php'));

    }

    #[Test]
    public function a_user_can_only_see_users_in_the_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1->id,
        ]);
        User::factory(9)->create([
            'tenant_id' => $tenant1->id,
        ]);

        User::factory(10)->create([
            'tenant_id' => $tenant2->id,
        ]);

        auth()->login($user1);

        $this->assertEquals(10, User::count());

    }

    #[Test]
    public function a_user_can_only_create_users_in_the_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1->id,
        ]);

        auth()->login($user1);

        $this->actingAs($user1)->session([
            'tenant_id' => $tenant1->id,
        ]);

        $createdUser = User::factory()->create();

        $this->assertSame($createdUser->tenant_id, $user1->tenant_id);
    }

    #[Test]
    public function a_user_can_only_create_features_in_the_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1->id,
        ]);

        auth()->login($user1);

        $this->actingAs($user1)->session([
            'tenant_id' => $tenant1->id,
        ]);

        $feature = Feature::factory()->create();

        $this->assertSame($feature->tenant_id, $user1->tenant_id);
    }

    #[Test]
    public function proposals_and_final_feature_snapshots_are_scoped_by_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $this->actingAs($user2)->session(['tenant_id' => $tenant2->id]);
        $proposal2 = Proposal::factory()->create(['user_id' => $user2->id]);
        $finalFeature2 = $proposal2->features()->create([
            'name' => 'Tenant 2 snapshot',
            'description' => 'desc',
            'price' => 1.00,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        auth()->logout();
        session()->forget('tenant_id');

        $this->actingAs($user1)->session(['tenant_id' => $tenant1->id]);
        $proposal1 = Proposal::factory()->create(['user_id' => $user1->id]);
        $finalFeature1 = $proposal1->features()->create([
            'name' => 'Tenant 1 snapshot',
            'description' => 'desc',
            'price' => 2.00,
            'quantity' => 2,
            'optional' => false,
            'order' => 1,
        ]);

        $this->assertSame($tenant1->id, $proposal1->fresh()->tenant_id);
        $this->assertSame($tenant1->id, $finalFeature1->fresh()->tenant_id);
        $this->assertSame($tenant2->id, $proposal2->fresh()->tenant_id);
        $this->assertSame($tenant2->id, $finalFeature2->fresh()->tenant_id);

        $this->assertSame(1, Proposal::count());
        $this->assertSame(1, FinalFeature::count());
        $this->assertNull(Proposal::find($proposal2->id));
        $this->assertNull(FinalFeature::find($finalFeature2->id));
        $this->assertCount(1, $proposal1->fresh()->features);
    }
}
