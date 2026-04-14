<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = Status::cases();
        $randomStatus = Arr::random($statuses);
        $tenant = Tenant::factory()->create();

        return [
            'name' => Str::ucFirst($this->faker->sentence(4, true)),
            'status' => $randomStatus,
            'user_id' => User::factory()->create(),
            'tenant_id' => $tenant->id,
            'client_id' => Client::factory()->create(['tenant_id' => $tenant->id]),
        ];
    }
}
