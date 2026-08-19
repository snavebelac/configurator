<?php

namespace Tests\Feature;

use App\Facades\Settings;
use App\Livewire\Admin\Proposals\ClientAccess;
use App\Livewire\Public\ProposalView;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalPasscodeTest extends TestCase
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
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    private function proposal(array $attributes = []): Proposal
    {
        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create(array_merge([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
        ], $attributes));

        $feature = new FinalFeature([
            'name' => 'Logo design',
            'description' => 'Three rounds of exploration.',
            'price' => 4800,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $feature->proposal()->associate($proposal);
        $feature->save();

        return $proposal;
    }

    private function becomeVisitor(): void
    {
        auth()->logout();
        session()->flush();
        Settings::forget();
    }

    #[Test]
    public function a_proposal_with_no_passcode_renders_straight_through()
    {
        $proposal = $this->proposal();
        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Brand identity system')
            ->assertSeeText('Logo design');
    }

    #[Test]
    public function a_passcode_protected_proposal_shows_the_gate_and_leaks_no_content()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        $response = $this->get(route('proposal.view', ['proposal' => $proposal->uuid]));

        $response->assertOk()
            ->assertSeeText('Enter your passcode.')
            ->assertDontSeeText('Brand identity system')
            ->assertDontSeeText('Logo design');

        // Nothing about the proposal — including its hashed passcode — should
        // be anywhere in the payload, Livewire snapshot included.
        $this->assertStringNotContainsString('Brand identity system', $response->getContent());
        $this->assertStringNotContainsString('Logo design', $response->getContent());
        $this->assertStringNotContainsString($proposal->fresh()->access_password, $response->getContent());
    }

    #[Test]
    public function the_wrong_passcode_is_rejected_and_reveals_nothing()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->set('passcode', 'wrong')
            ->call('unlock')
            ->assertSet('unlocked', false)
            ->assertSet('passcodeError', 'That passcode is not right.')
            ->assertDontSee('Logo design');

        $this->assertFalse((bool) session($proposal->unlockSessionKey(), false));
    }

    #[Test]
    public function the_right_passcode_unlocks_the_proposal()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->set('passcode', 'open-sesame')
            ->call('unlock')
            ->assertSet('unlocked', true)
            ->assertSet('passcode', '')
            ->assertSee('Logo design');

        $this->assertTrue((bool) session($proposal->unlockSessionKey(), false));
    }

    #[Test]
    public function unlocking_survives_a_later_request_in_the_same_session()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->set('passcode', 'open-sesame')
            ->call('unlock')
            ->assertSet('unlocked', true);

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Brand identity system');
    }

    #[Test]
    public function unlocking_one_proposal_does_not_unlock_another()
    {
        $unlockMe = $this->proposal(['access_password' => 'open-sesame']);
        $other = $this->proposal([
            'access_password' => 'a-different-passcode',
            'name' => 'Secret other proposal',
        ]);
        $this->becomeVisitor();

        Livewire::test(ProposalView::class, ['proposal' => $unlockMe])
            ->set('passcode', 'open-sesame')
            ->call('unlock')
            ->assertSet('unlocked', true);

        $this->get(route('proposal.view', ['proposal' => $other->uuid]))
            ->assertOk()
            ->assertSeeText('Enter your passcode.')
            ->assertDontSeeText('Secret other proposal');
    }

    #[Test]
    public function an_expired_link_is_refused_even_with_the_right_passcode()
    {
        $proposal = $this->proposal([
            'access_password' => 'open-sesame',
            'access_expires_at' => now()->subDay(),
        ]);
        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('This link has expired.')
            ->assertDontSeeText('Brand identity system');

        Livewire::test(ProposalView::class, ['proposal' => $proposal])
            ->set('passcode', 'open-sesame')
            ->call('unlock')
            ->assertSet('unlocked', false);
    }

    #[Test]
    public function an_expired_link_is_refused_even_without_a_passcode()
    {
        $proposal = $this->proposal(['access_expires_at' => now()->subMinute()]);
        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('This link has expired.')
            ->assertDontSeeText('Brand identity system');
    }

    #[Test]
    public function a_future_expiry_still_allows_access()
    {
        $proposal = $this->proposal(['access_expires_at' => now()->addWeek()]);
        $this->becomeVisitor();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Brand identity system');
    }

    #[Test]
    public function repeated_wrong_passcodes_are_throttled()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);
        $this->becomeVisitor();

        $component = Livewire::test(ProposalView::class, ['proposal' => $proposal]);

        for ($i = 0; $i < 10; $i++) {
            $component->set('passcode', 'wrong')->call('unlock');
        }

        $component->set('passcode', 'wrong')
            ->call('unlock')
            ->assertSet('unlocked', false);

        $this->assertStringContainsString('Too many attempts', $component->get('passcodeError'));

        // Still throttled even once the correct passcode is offered.
        $component->set('passcode', 'open-sesame')
            ->call('unlock')
            ->assertSet('unlocked', false);

        RateLimiter::clear('proposal-unlock|127.0.0.1|'.$proposal->uuid);
    }

    #[Test]
    public function the_passcode_is_stored_hashed_not_in_plain_text()
    {
        $proposal = $this->proposal();

        Livewire::test(ClientAccess::class, ['proposal' => $proposal])
            ->set('passcode', 'open-sesame')
            ->call('setPasscode')
            ->assertHasNoErrors();

        $stored = $proposal->fresh()->access_password;

        $this->assertNotSame('open-sesame', $stored);
        $this->assertTrue(Hash::check('open-sesame', $stored));
    }

    #[Test]
    public function an_admin_can_clear_the_passcode()
    {
        $proposal = $this->proposal(['access_password' => 'open-sesame']);

        Livewire::test(ClientAccess::class, ['proposal' => $proposal])
            ->call('clearPasscode');

        $this->assertNull($proposal->fresh()->access_password);
        $this->assertFalse($proposal->fresh()->isPasscodeProtected());
    }

    #[Test]
    public function the_passcode_has_a_minimum_length()
    {
        $proposal = $this->proposal();

        Livewire::test(ClientAccess::class, ['proposal' => $proposal])
            ->set('passcode', 'ab')
            ->call('setPasscode')
            ->assertHasErrors(['passcode' => 'min']);

        $this->assertNull($proposal->fresh()->access_password);
    }

    #[Test]
    public function an_admin_can_set_and_clear_an_expiry()
    {
        $proposal = $this->proposal();

        Livewire::test(ClientAccess::class, ['proposal' => $proposal])
            ->set('expiresAt', now()->addWeek()->format('Y-m-d'))
            ->call('saveExpiry')
            ->assertHasNoErrors();

        $this->assertNotNull($proposal->fresh()->access_expires_at);

        Livewire::test(ClientAccess::class, ['proposal' => $proposal->fresh()])
            ->set('expiresAt', '')
            ->call('saveExpiry')
            ->assertHasNoErrors();

        $this->assertNull($proposal->fresh()->access_expires_at);
    }

    #[Test]
    public function an_expiry_in_the_past_is_rejected()
    {
        $proposal = $this->proposal();

        Livewire::test(ClientAccess::class, ['proposal' => $proposal])
            ->set('expiresAt', now()->subDay()->format('Y-m-d'))
            ->call('saveExpiry')
            ->assertHasErrors(['expiresAt' => 'after']);
    }
}
