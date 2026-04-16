<?php

namespace Tests\Feature;

use App\Livewire\Admin\Clients\ClientModal;
use App\Livewire\Admin\Features\FeatureModal;
use App\Livewire\Admin\Users\UserModal;
use App\Models\Client;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModalTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_client_modal_creates_a_new_client()
    {
        $this->signIn();

        Livewire::test(ClientModal::class)
            ->set('name', 'Halverson Studio')
            ->set('contact', 'Avery Halverson')
            ->set('contactEmail', 'avery@halverson.studio')
            ->set('contactPhone', '01234 567890')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'name' => 'Halverson Studio',
            'contact' => 'Avery Halverson',
            'contact_email' => 'avery@halverson.studio',
        ]);
    }

    #[Test]
    public function the_client_modal_validates_required_fields()
    {
        $this->signIn();

        Livewire::test(ClientModal::class)
            ->set('contactEmail', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['name', 'contact', 'contactEmail']);
    }

    #[Test]
    public function the_client_modal_loads_an_existing_client_for_edit()
    {
        [$tenant] = $this->signIn();
        $client = Client::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Northwind Trading',
            'contact' => 'Priya Ram',
        ]);

        Livewire::test(ClientModal::class, ['clientId' => $client->id])
            ->assertSet('name', 'Northwind Trading')
            ->assertSet('contact', 'Priya Ram');
    }

    #[Test]
    public function the_feature_modal_creates_a_new_feature()
    {
        $this->signIn();

        Livewire::test(FeatureModal::class)
            ->set('name', 'Logo design')
            ->set('description', 'A mark that travels.')
            ->set('price', '1200.00')
            ->set('quantity', '1')
            ->set('optional', false)
            ->call('save');

        $this->assertDatabaseHas('features', [
            'name' => 'Logo design',
            'description' => 'A mark that travels.',
        ]);
    }

    #[Test]
    public function the_feature_modal_validates_required_fields()
    {
        $this->signIn();

        Livewire::test(FeatureModal::class)
            ->call('save')
            ->assertHasErrors(['name', 'description', 'price', 'quantity']);
    }

    #[Test]
    public function the_feature_modal_loads_an_existing_feature_for_edit()
    {
        [$tenant] = $this->signIn();
        $feature = Feature::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Extra revisions',
            'optional' => true,
        ]);

        Livewire::test(FeatureModal::class, ['featureId' => $feature->id])
            ->assertSet('name', 'Extra revisions')
            ->assertSet('optional', true);
    }

    #[Test]
    public function the_user_modal_creates_a_new_user_with_a_role()
    {
        $this->signIn();
        Role::create(['name' => 'admin']);

        Livewire::test(UserModal::class)
            ->set('name', 'Maya')
            ->set('lastName', 'Ko')
            ->set('email', 'maya@example.com')
            ->set('role', 'admin')
            ->set('password', 'super-secret-12')
            ->set('password_confirmation', 'super-secret-12')
            ->set('active', true)
            ->call('save');

        $created = User::where('email', 'maya@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
    }

    #[Test]
    public function the_user_modal_validates_required_fields()
    {
        $this->signIn();

        Livewire::test(UserModal::class)
            ->call('save')
            ->assertHasErrors(['name', 'lastName', 'role', 'email', 'password']);
    }
}
