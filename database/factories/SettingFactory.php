<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tax_rate' => 20.0,
            'tax_name' => 'VAT',
            'tenant_id' => Tenant::factory()->create(),
        ];
    }
}
