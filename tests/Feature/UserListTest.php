<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\UserList;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserListTest extends TestCase
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
        ]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        return [$tenant, $user];
    }

    #[Test]
    public function the_users_page_renders_with_the_signed_in_user()
    {
        [, $user] = $this->signIn();

        $this->get(route('dashboard.users'))
            ->assertOk()
            ->assertSeeText('Team.')
            ->assertSeeText($user->full_name)
            ->assertSeeText('You')
            ->assertSeeText('Active');
    }

    #[Test]
    public function inactive_users_are_shown_as_inactive()
    {
        [$tenant] = $this->signIn();

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'active' => false,
            'name' => 'Deactivated',
            'last_name' => 'Person',
        ]);

        $this->get(route('dashboard.users'))
            ->assertOk()
            ->assertSeeText('Deactivated Person')
            ->assertSeeText('Inactive');
    }

    #[Test]
    public function a_user_cannot_delete_themselves()
    {
        [, $user] = $this->signIn();

        Livewire::test(UserList::class)
            ->call('delete', $user->id);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    #[Test]
    public function another_user_can_be_deleted()
    {
        [$tenant] = $this->signIn();

        $victim = User::factory()->create([
            'tenant_id' => $tenant->id,
            'active' => true,
            'name' => 'Gone',
            'last_name' => 'Soon',
        ]);

        Livewire::test(UserList::class)
            ->call('delete', $victim->id);

        $this->assertDatabaseMissing('users', ['id' => $victim->id]);
    }
}
