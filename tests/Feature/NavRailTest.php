<?php

namespace Tests\Feature;

use App\Livewire\Admin\Shared\NavRail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NavRailTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
    }

    #[Test]
    public function a_new_user_starts_with_the_nav_expanded()
    {
        // The column default is what a real new user gets, so assert on the
        // stored value rather than the un-refreshed factory instance.
        $this->assertFalse($this->user->fresh()->nav_collapsed);

        Livewire::test(NavRail::class)->assertSet('collapsed', false);
    }

    #[Test]
    public function the_admin_layout_mounts_the_collapsible_rail()
    {
        // Asserting on the nav labels alone is not enough: the old fixed rail
        // carried the same words in its hover tooltips, so those assertions
        // passed whether or not this component was actually wired in.
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeLivewire(NavRail::class)
            ->assertSee('aria-label="Collapse navigation"', escape: false);
    }

    #[Test]
    public function the_expanded_nav_shows_a_text_label_for_every_item()
    {
        $response = $this->get(route('dashboard'))->assertOk();

        foreach (['Overview', 'Proposals', 'Clients', 'Features', 'Packages', 'Team', 'Settings'] as $label) {
            $response->assertSee('<span class="truncate text-[13.5px] font-medium">'.$label.'</span>', escape: false);
        }
    }

    #[Test]
    public function the_collapsed_nav_falls_back_to_tooltips_instead_of_labels()
    {
        $this->user->nav_collapsed = true;
        $this->user->save();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Expand navigation"', escape: false)
            ->assertDontSee('<span class="truncate text-[13.5px] font-medium">Overview</span>', escape: false);
    }

    #[Test]
    public function toggling_collapses_the_nav_and_persists_the_choice()
    {
        Livewire::test(NavRail::class)
            ->assertSet('collapsed', false)
            ->call('toggle')
            ->assertSet('collapsed', true);

        $this->assertTrue($this->user->fresh()->nav_collapsed);
    }

    #[Test]
    public function toggling_again_expands_it_and_persists_that_too()
    {
        Livewire::test(NavRail::class)
            ->call('toggle')
            ->call('toggle')
            ->assertSet('collapsed', false);

        $this->assertFalse($this->user->fresh()->nav_collapsed);
    }

    #[Test]
    public function the_stored_preference_is_used_on_a_later_visit()
    {
        $this->user->nav_collapsed = true;
        $this->user->save();

        Livewire::test(NavRail::class)->assertSet('collapsed', true);
    }

    #[Test]
    public function the_preference_belongs_to_the_user_not_the_workspace()
    {
        $colleague = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        Livewire::test(NavRail::class)->call('toggle');

        $this->assertTrue($this->user->fresh()->nav_collapsed);
        $this->assertFalse($colleague->fresh()->nav_collapsed);
    }
}
