<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use App\Models\Feature;
use App\Models\Setting;
use App\Models\Proposal;
use App\Models\FinalFeature;
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

        Feature::factory()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Navigation menu',
            'description' => 'Dynamic drop down menu (user definable)',
            'price' => 79,
            'quantity' => 1,
        ]);
        Feature::factory()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Navigation menu',
            'description' => 'Mega (dynamic with multiple lists)',
            'price' => 182,
            'quantity' => 1,
        ]);
        Feature::factory()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Search',
            'description' => 'Website search bar with search results page',
            'price' => 76,
            'quantity' => 1,
        ]);
        Feature::factory()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Advanced Search',
            'description' => 'Advanced Search (Faceted Search with List Filters and Sorting)',
            'price' => 395,
            'quantity' => 1,
        ]);
        Feature::factory()->create([
            'tenant_id' => $tenant1->id,
            'name' => 'Image gallery',
            'description' => 'Image gallery with lightbox',
            'price' => 247,
            'quantity' => 1,
        ]);

        Proposal::factory()->count(10)->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant1->id,
            'client_id' => $client->id,
        ]);

        Proposal::all()->each(function($proposal) use ($tenant1) {
            $features = Feature::where('tenant_id', $tenant1->id)->get()->random(rand(3, 10));
            foreach ($features as $feature) {
                $ff = new FinalFeature([
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'price' => $feature->price,
                    'quantity' => $feature->quantity,
                    'optional' => $feature->optional,
                    'order' => $feature->order,
                    'tenant_id' => $tenant1->id,
                ]);
                $ff->proposal()->associate($proposal);
                $ff->save();
            }
        });

        Proposal::factory()->count(12)->create();
        Setting::factory()->count(1)->create([
            'tenant_id' => $tenant1->id,
        ]);

    }
}
