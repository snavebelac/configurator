<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Feature>
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
            'price' => $this->faker->numberBetween(600, 9999) / 100,
            'quantity' => $this->faker->numberBetween(1, 3),
            'optional' => $this->faker->boolean(),
            'parent_id' => null,
            'order' => $this->faker->numberBetween(1, 100),
            'tenant_id' => Tenant::factory()->create(),
        ];
    }
}
