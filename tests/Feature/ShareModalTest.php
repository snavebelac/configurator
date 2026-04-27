<?php

namespace Tests\Feature;

use App\Livewire\Admin\Proposals\ShareModal;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShareModalTest extends TestCase
{
    use RefreshDatabase;

    private function signInAndCreateProposal(array $proposalAttrs = []): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $setting = Setting::factory()->create(['tenant_id' => $tenant->id, 'default_share_expiry_days' => null]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $proposal = Proposal::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
        ], $proposalAttrs));

        return [$tenant, $user, $proposal, $setting];
    }

    #[Test]
    public function the_modal_pre_populates_expiry_from_tenant_default_when_proposal_has_none(): void
    {
        [, , $proposal, $setting] = $this->signInAndCreateProposal();
        $setting->update(['default_share_expiry_days' => 14]);

        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->assertSet('expiresAtDate', now()->addDays(14)->toDateString());
    }

    #[Test]
    public function blank_expiry_persists_as_no_expiry_on_save(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal(['expires_at' => now()->addDays(30)]);

        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->set('expiresAtDate', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($proposal->fresh()->expires_at);
    }

    #[Test]
    public function generating_a_code_does_not_write_to_the_db_until_save(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->call('generateCode')
            ->assertSet('codeRequired', true)
            ->assertNotSet('generatedCode', null);

        $this->assertNull($proposal->fresh()->access_code_hash);
    }

    #[Test]
    public function saving_with_a_generated_code_writes_a_hashed_value(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        $component = Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->call('generateCode');
        $code = $component->get('generatedCode');

        $component->call('save')->assertHasNoErrors();

        $proposal->refresh();
        $this->assertNotNull($proposal->access_code_hash);
        $this->assertTrue(Hash::check($code, $proposal->access_code_hash));
    }

    #[Test]
    public function unchecking_code_required_clears_an_existing_hash_on_save(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal(['access_code_hash' => Hash::make('123456')]);

        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->assertSet('codeRequired', true)
            ->set('codeRequired', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($proposal->fresh()->access_code_hash);
    }

    #[Test]
    public function expiry_in_the_past_is_rejected(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->set('expiresAtDate', now()->subDay()->toDateString())
            ->call('save')
            ->assertHasErrors(['expiresAtDate']);
    }

    #[Test]
    public function ticking_code_required_immediately_generates_a_code(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        $component = Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->assertSet('codeRequired', false)
            ->assertSet('generatedCode', null)
            ->set('codeRequired', true);

        $this->assertNotNull($component->get('generatedCode'));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $component->get('generatedCode'));
    }

    #[Test]
    public function ticking_code_required_does_not_overwrite_an_already_generated_code(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        $component = Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->call('generateCode');
        $original = $component->get('generatedCode');

        $component->set('codeRequired', false)
            ->set('codeRequired', true);

        $this->assertSame($original, $component->get('generatedCode'));
    }

    #[Test]
    public function ticking_code_required_does_not_clobber_an_existing_persisted_code(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal(['access_code_hash' => Hash::make('999999')]);

        $component = Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->assertSet('codeRequired', true)
            ->assertSet('generatedCode', null)
            ->set('codeRequired', false)
            ->set('codeRequired', true);

        // Still null — the admin sees the "regenerate" button instead.
        $this->assertNull($component->get('generatedCode'));
    }

    #[Test]
    public function saving_with_code_required_but_no_generated_code_still_locks_the_proposal(): void
    {
        [, , $proposal] = $this->signInAndCreateProposal();

        // Simulates a malformed flow where the live binding never fired
        // (e.g. an admin scripting the component or a future regression).
        Livewire::test(ShareModal::class, ['proposalId' => $proposal->id])
            ->set('codeRequired', true, false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNotNull($proposal->fresh()->access_code_hash);
    }
}
