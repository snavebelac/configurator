<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use App\Models\Feature;
use App\Models\Setting;
use App\Models\Proposal;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant1 = Tenant::factory()->create([
            'name' => 'Fantasea',
            'subdomain' => 'fantasea'
        ]);

        $super = Role::create(['name' => 'superadmin']);
        $admin = Role::create(['name' => 'administrator']);
        $editor = Role::create(['name' => 'editor']);
        $user = User::factory()->create([
            'name' => 'Caleb',
            'last_name' => 'Evans',
            'email' => 'caleb@epicfox.co.uk',
            'password' => Hash::make('hoagie123'),
            'tenant_id' => $tenant1->id,
        ]);
        $user->assignrole($super);

        User::factory()->count(10)->create([
            'tenant_id' => $tenant1->id,
        ]);
        User::factory()->count(10)->create();

        Client::factory()->count(19)->create();
        $client = Client::factory()->create(['tenant_id' => $tenant1->id]);

        Feature::factory()->count(10)->create([
            'tenant_id' => $tenant1->id,
        ]);
        Feature::factory()->count(10)->create();

        Proposal::factory()->count(10)->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant1->id,
            'client_id' => $client->id,
        ]);
        Proposal::all()->each(function($proposal) use ($tenant1) {
            $proposal->features()->attach(Feature::all()->random(rand(3, 10))->pluck('id'), ['tenant_id' => $tenant1->id]);
        });
        Proposal::factory()->count(12)->create();
        Setting::factory()->count(1)->create([
            'tenant_id' => $tenant1->id,
        ]);

    }
}
