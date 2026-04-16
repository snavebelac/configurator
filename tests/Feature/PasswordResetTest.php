<?php

namespace Tests\Feature;

use App\Livewire\ForgottenPassword;
use App\Livewire\PasswordReset;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_forgotten_password_page_renders_with_brand_copy()
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSeeLivewire(ForgottenPassword::class)
            ->assertSeeText('Reset your password.')
            ->assertSeeText('ConfiguPro · Recovery')
            ->assertSeeText('Back to sign in');
    }

    #[Test]
    public function a_reset_link_is_sent_for_a_known_email()
    {
        Notification::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'caleb@example.com',
        ]);

        Livewire::test(ForgottenPassword::class)
            ->set('email', 'caleb@example.com')
            ->call('resetPassword')
            ->assertSet('successMessage', __(Password::ResetLinkSent));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function the_password_reset_page_renders_with_brand_copy()
    {
        $this->get(route('password.reset', ['token' => 'abc123']))
            ->assertOk()
            ->assertSeeLivewire(PasswordReset::class)
            ->assertSeeText('Set a new password.')
            ->assertSeeText('ConfiguPro · New password');
    }

    #[Test]
    public function the_password_can_be_updated_with_a_valid_token()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'caleb@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        Livewire::test(PasswordReset::class, ['token' => $token])
            ->set('email', 'caleb@example.com')
            ->set('password', 'brand-new-password')
            ->set('passwordConfirmation', 'brand-new-password')
            ->call('updatePassword')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    #[Test]
    public function mismatched_confirmation_surfaces_a_validation_error()
    {
        Livewire::test(PasswordReset::class, ['token' => 'whatever'])
            ->set('email', 'caleb@example.com')
            ->set('password', 'one-password')
            ->set('passwordConfirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['passwordConfirmation']);
    }
}
