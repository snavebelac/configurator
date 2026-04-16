<?php

namespace Tests\Feature;

use App\Livewire\Login;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_login_page_renders_with_brand_copy()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeLivewire(Login::class)
            ->assertSeeText('Welcome back.')
            ->assertSeeText('ConfiguPro · Sign in')
            ->assertSeeText('Forgot password?');
    }

    #[Test]
    public function email_and_password_are_required()
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['email', 'password']);
    }

    #[Test]
    public function invalid_credentials_surface_a_friendly_error_message()
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'caleb@example.com',
            'password' => Hash::make('correct-horse'),
            'active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'caleb@example.com')
            ->set('password', 'wrong')
            ->call('authenticate')
            ->assertSet('loginMessage', 'Email or Password not recognised');

        $this->assertGuest();
    }

    #[Test]
    public function valid_credentials_redirect_to_the_dashboard()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'caleb@example.com',
            'password' => Hash::make('correct-horse'),
            'active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'caleb@example.com')
            ->set('password', 'correct-horse')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function inactive_users_cannot_sign_in()
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'caleb@example.com',
            'password' => Hash::make('correct-horse'),
            'active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'caleb@example.com')
            ->set('password', 'correct-horse')
            ->call('authenticate')
            ->assertSet('loginMessage', 'Email or Password not recognised');

        $this->assertGuest();
    }
}
