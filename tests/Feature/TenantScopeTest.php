<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Feature;
use App\Models\Proposal;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    public function a_proposal_a_feature_and_their_pivot_table_share_the_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1->id,
        ]);

        $user2 = User::factory()->create([
            'tenant_id' => $tenant2->id,
        ]);

        auth()->login($user2);
        $this->actingAs($user2)->session([
            'tenant_id' => $tenant2->id,
        ]);
        $feature2 = Feature::factory()->create();
        $proposal2 = Proposal::factory()->create([
            'user_id' => $user2->id,
        ]);
        $proposal2->features()->attach($feature2);
        auth()->logout();

        auth()->login($user1);
        $this->actingAs($user1)->session([
            'tenant_id' => $tenant1->id,
        ]);
        $feature1 = Feature::factory()->create();
        $proposal1 = Proposal::factory()->create([
            'user_id' => $user1->id,
        ]);
        $feature1->proposals()->attach($proposal1);

        $proposalPivot1 = $feature1->proposals()->first();
        $featurePivot1 = $proposal1->features()->first();

        $this->assertFalse($proposal1->tenant_id === $user2->tenant_id);
        $this->assertFalse($proposal2->tenant_id === $user1->tenant_id);
        $this->assertFalse($feature2->tenant_id === $user1->tenant_id);
        $this->assertFalse($feature1->tenant_id === $user2->tenant_id);

        $this->assertSame($feature1->tenant_id, $user1->tenant_id);
        $this->assertSame($proposal1->tenant_id, $user1->tenant_id);
        $this->assertSame($proposalPivot1->pivot->tenant_id, $user1->tenant_id);
        $this->assertSame($featurePivot1->pivot->tenant_id, $user1->tenant_id);
    }
}
