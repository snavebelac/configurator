<?php

namespace Tests\Feature;

use App\Livewire\Admin\Profile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'active' => true,
            'name' => 'Caleb',
            'last_name' => 'Evans',
            'email' => 'caleb@example.com',
        ]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_profile_page_renders_pre_populated()
    {
        $this->signIn();

        $this->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSeeText('Your profile.')
            ->assertSee('Caleb')
            ->assertSee('Evans')
            ->assertSee('caleb@example.com');
    }

    #[Test]
    public function the_profile_can_be_updated()
    {
        [, $user] = $this->signIn();

        Livewire::test(Profile::class)
            ->set('name', 'Calebra')
            ->set('lastName', 'Evanstein')
            ->set('email', 'updated@example.com')
            ->call('updateUser', $user);

        $user->refresh();
        $this->assertSame('Calebra', $user->name);
        $this->assertSame('Evanstein', $user->last_name);
        $this->assertSame('updated@example.com', $user->email);
    }

    #[Test]
    public function validation_errors_are_surfaced()
    {
        [, $user] = $this->signIn();

        Livewire::test(Profile::class)
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->call('updateUser', $user)
            ->assertHasErrors(['name', 'email']);
    }
}
