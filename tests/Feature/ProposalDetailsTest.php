<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Facades\Settings;
use App\Livewire\Admin\Proposals\ProposalEdit;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProposalDetailsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    private Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
            'status' => Status::DRAFT,
        ]);

        $feature = new FinalFeature([
            'name' => 'Core build',
            'description' => 'The build.',
            'price' => 1000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $feature->proposal()->associate($this->proposal);
        $feature->save();
    }

    protected function tearDown(): void
    {
        Settings::forget();

        parent::tearDown();
    }

    #[Test]
    public function the_edit_screen_hydrates_the_existing_copy()
    {
        $this->proposal->description = 'An opening paragraph.';
        $this->proposal->additional = 'Some closing notes.';
        $this->proposal->save();

        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->assertSet('name', 'Brand identity system')
            ->assertSet('description', 'An opening paragraph.')
            ->assertSet('additional', 'Some closing notes.');
    }

    #[Test]
    public function the_copy_can_be_edited_and_saved()
    {
        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->set('name', 'Brand identity system v2')
            ->set('description', 'A fresh introduction.')
            ->set('additional', 'Timings and assumptions.')
            ->call('saveDetails')
            ->assertHasNoErrors();

        $fresh = $this->proposal->fresh();

        $this->assertSame('Brand identity system v2', $fresh->name);
        $this->assertSame('A fresh introduction.', $fresh->description);
        $this->assertSame('Timings and assumptions.', $fresh->additional);
    }

    #[Test]
    public function saved_copy_reaches_the_client_facing_view()
    {
        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->set('description', 'A fresh introduction.')
            ->set('additional', 'Timings and assumptions.')
            ->call('saveDetails')
            ->assertHasNoErrors();

        auth()->logout();
        session()->flush();
        Settings::forget();

        $this->get(route('proposal.view', ['proposal' => $this->proposal->uuid]))
            ->assertOk()
            ->assertSeeText('A fresh introduction.')
            ->assertSeeText('Timings and assumptions.');
    }

    #[Test]
    public function blank_copy_is_stored_as_null_so_the_client_view_omits_the_sections()
    {
        $this->proposal->description = 'An opening paragraph.';
        $this->proposal->additional = 'Some closing notes.';
        $this->proposal->save();

        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal->fresh()])
            ->set('description', '')
            ->set('additional', '')
            ->call('saveDetails')
            ->assertHasNoErrors();

        $fresh = $this->proposal->fresh();

        $this->assertNull($fresh->description);
        $this->assertNull($fresh->additional);
    }

    #[Test]
    public function the_title_is_required()
    {
        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->set('name', '')
            ->call('saveDetails')
            ->assertHasErrors(['name' => 'required']);

        $this->assertSame('Brand identity system', $this->proposal->fresh()->name);
    }

    #[Test]
    public function the_copy_fields_are_length_capped_to_their_columns()
    {
        Livewire::test(ProposalEdit::class, ['proposal' => $this->proposal])
            ->set('description', str_repeat('a', 4001))
            ->call('saveDetails')
            ->assertHasErrors(['description' => 'max']);
    }
}
