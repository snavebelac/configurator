<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::ucFirst($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'tenant_id' => Tenant::factory()->create(),
        ];
    }
}
