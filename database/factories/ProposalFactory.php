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
     * A proposal's owner and client both have to sit in the proposal's own
     * tenant, which is why this used to resolve the tenant eagerly — it needed
     * the id to hand. The closures below get the same guarantee without the
     * cost: Laravel expands attributes in declaration order and passes the
     * already-resolved ones in, so `tenant_id` is a real id by the time
     * `user_id` and `client_id` are evaluated.
     *
     * The upshot is that overriding any of the three skips its creation
     * entirely, instead of building a record and discarding it.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::ucFirst($this->faker->sentence(4, true)),
            'status' => Arr::random(Status::cases()),
            'tenant_id' => Tenant::factory(),
            'user_id' => fn (array $attributes) => User::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'client_id' => fn (array $attributes) => Client::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
        ];
    }
}
