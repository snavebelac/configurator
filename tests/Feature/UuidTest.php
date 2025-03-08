<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Feature;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UuidTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function a_uuid_is_generated_for_a_user_on_creation()
    {
        $user = User::factory()->create();
        $this->assertTrue(strlen($user->uuid) === 36);
    }

    #[Test]
    public function a_uuid_is_generated_for_a_feature_on_creation()
    {
        $feature = Feature::factory()->create();
        $this->assertTrue(strlen($feature->uuid) === 36);
    }
}
