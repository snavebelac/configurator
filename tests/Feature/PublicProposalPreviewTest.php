<?php

namespace Tests\Feature;

use App\Livewire\Public\ProposalPreview as PublicPreview;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicProposalPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function newProposal(array $attrs = []): Proposal
    {
        $tenant = $attrs['tenant'] ?? Tenant::factory()->create();
        Setting::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $proposal = Proposal::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
        ], collect($attrs)->except('tenant')->all()));

        FinalFeature::forceCreate([
            'tenant_id' => $tenant->id,
            'proposal_id' => $proposal->id,
            'name' => 'Logo design',
            'description' => 'Mark + wordmark exploration',
            'price' => 200000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);

        return $proposal->fresh();
    }

    #[Test]
    public function the_share_route_is_accessible_without_authentication(): void
    {
        $proposal = $this->newProposal();

        $this->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Brand identity system')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    #[Test]
    public function an_unknown_uuid_returns_404(): void
    {
        $this->get(route('proposal.share', ['uuid' => '00000000-0000-0000-0000-000000000000']))
            ->assertNotFound();
    }

    #[Test]
    public function an_expired_share_link_renders_the_expired_view_not_the_proposal(): void
    {
        $proposal = $this->newProposal(['expires_at' => Carbon::now()->subDay()]);

        $this->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('This share link has expired')
            ->assertDontSeeText('Logo design');
    }

    #[Test]
    public function a_proposal_requiring_a_code_renders_the_gate_first(): void
    {
        $proposal = $this->newProposal(['access_code_hash' => Hash::make('123456')]);

        $this->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Enter your access code')
            ->assertDontSeeText('Logo design');
    }

    #[Test]
    public function the_correct_code_unlocks_the_preview_and_an_incorrect_one_does_not(): void
    {
        $proposal = $this->newProposal(['access_code_hash' => Hash::make('123456')]);

        Livewire::test(PublicPreview::class, ['uuid' => $proposal->uuid])
            ->assertSet('state', 'gate')
            ->set('code', '999999')
            ->call('submitCode')
            ->assertHasErrors(['code'])
            ->assertSet('state', 'gate')
            ->set('code', '123456')
            ->call('submitCode')
            ->assertHasNoErrors()
            ->assertSet('state', 'preview');
    }

    #[Test]
    public function results_render_using_the_proposal_tenants_settings_not_the_first_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        Setting::factory()->create(['tenant_id' => $tenantA->id, 'tax_name' => 'TenantATax']);

        $proposal = $this->newProposal();
        Setting::query()->where('tenant_id', $proposal->tenant_id)->update(['tax_name' => 'CorrectTax']);

        $this->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('CorrectTax')
            ->assertDontSeeText('TenantATax');
    }

    #[Test]
    public function an_authenticated_user_from_a_different_tenant_can_still_view_a_share_link(): void
    {
        $proposal = $this->newProposal();

        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id, 'active' => true]);
        $this->actingAs($otherUser)->session(['tenant_id' => $otherTenant->id]);

        $this->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Brand identity system');
    }

    #[Test]
    public function changing_the_access_code_invalidates_existing_unlock_cookies(): void
    {
        $proposal = $this->newProposal(['access_code_hash' => Hash::make('111111')]);

        $component = Livewire::test(PublicPreview::class, ['uuid' => $proposal->uuid])
            ->set('code', '111111')
            ->call('submitCode')
            ->assertSet('state', 'preview');

        $cookieName = 'share_access_'.$proposal->id;
        $oldToken = hash_hmac('sha256', $proposal->id.'|'.$proposal->fresh()->access_code_hash, config('app.key') ?: 'fallback');

        $proposal->update(['access_code_hash' => Hash::make('222222')]);

        $this->withUnencryptedCookie($cookieName, $oldToken)
            ->get(route('proposal.share', ['uuid' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Enter your access code')
            ->assertDontSeeText('Logo design');

        unset($component);
    }

    #[Test]
    public function admin_in_app_preview_ignores_expiry_and_code_gates(): void
    {
        $tenant = Tenant::factory()->create();
        Setting::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'active' => true]);
        $client = Client::factory()->create(['tenant_id' => $tenant->id]);

        $proposal = Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Locked-down proposal',
            'expires_at' => Carbon::now()->subDay(),
            'access_code_hash' => Hash::make('555555'),
        ]);

        FinalFeature::forceCreate([
            'tenant_id' => $tenant->id,
            'proposal_id' => $proposal->id,
            'name' => 'Logo design',
            'description' => 'Mark + wordmark exploration',
            'price' => 200000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);

        $this->actingAs($user)->session(['tenant_id' => $tenant->id]);

        $this->get(route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Locked-down proposal')
            ->assertSeeText('Logo design')
            ->assertDontSeeText('Enter your access code')
            ->assertDontSeeText('This share link has expired');
    }
}
