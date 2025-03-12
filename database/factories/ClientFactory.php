<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact' => $this->faker->name,
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => $this->faker->unique()->phoneNumber(),
            'tenant_id' => Tenant::factory()->create(),
        ];
    }
}
