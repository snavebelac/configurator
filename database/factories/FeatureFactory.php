<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::ucFirst($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'quantity' => $this->faker->numberBetween(1, 10),
            'parent_id' => null,
            'order' => $this->faker->numberBetween(1, 100),
            'tenant_id' => Tenant::factory()->create(),
        ];
    }
}
