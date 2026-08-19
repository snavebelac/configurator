<?php

namespace Tests\Feature;

use App\Facades\Settings as SettingsFacade;
use App\Livewire\Admin\Settings;
use App\Models\Client;
use App\Models\FinalFeature;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsLogoTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        $this->actingAs($this->user)->session(['tenant_id' => $this->tenant->id]);
    }

    protected function tearDown(): void
    {
        SettingsFacade::forget();

        parent::tearDown();
    }

    #[Test]
    public function a_logo_can_be_uploaded_and_is_stored_on_the_public_disk()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('brand.png'))
            ->call('save')
            ->assertHasNoErrors();

        $stored = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        $this->assertNotNull($stored);
        $this->assertStringStartsWith('logos/', $stored);
        Storage::disk('public')->assertExists($stored);
    }

    #[Test]
    public function replacing_a_logo_deletes_the_previous_file()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('first.png'))
            ->call('save');

        $first = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('second.png'))
            ->call('save');

        $second = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    #[Test]
    public function a_logo_can_be_removed()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('brand.png'))
            ->call('save');

        $stored = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        Livewire::test(Settings::class)->call('removeLogo');

        $this->assertNull(Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo);
        Storage::disk('public')->assertMissing($stored);
    }

    #[Test]
    public function saving_without_choosing_a_file_leaves_the_existing_logo_alone()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('brand.png'))
            ->call('save');

        $stored = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        Livewire::test(Settings::class)
            ->set('taxName', 'GST')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($stored, Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo);
        Storage::disk('public')->assertExists($stored);
    }

    #[Test]
    public function a_non_image_is_rejected()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('logo');

        $this->assertNull(Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo);
    }

    #[Test]
    public function an_svg_is_rejected_because_it_can_carry_script()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'))
            ->call('save')
            ->assertHasErrors('logo');
    }

    #[Test]
    public function an_oversized_image_is_rejected()
    {
        Setting::factory()->create(['tenant_id' => $this->tenant->id]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('huge.png')->size(3000))
            ->call('save')
            ->assertHasErrors(['logo' => 'max']);
    }

    #[Test]
    public function the_logo_appears_on_the_client_facing_proposal()
    {
        Setting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Epic Fox Ltd',
        ]);

        Livewire::test(Settings::class)
            ->set('logo', UploadedFile::fake()->image('brand.png'))
            ->call('save');

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'name' => 'Brand identity system',
        ]);
        $feature = new FinalFeature([
            'name' => 'Core build',
            'description' => 'The build.',
            'price' => 1000,
            'quantity' => 1,
            'optional' => false,
            'order' => 1,
        ]);
        $feature->proposal()->associate($proposal);
        $feature->save();

        $stored = Setting::withoutGlobalScopes()->firstWhere('tenant_id', $this->tenant->id)->logo;

        auth()->logout();
        session()->flush();
        SettingsFacade::forget();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($stored), escape: false)
            ->assertDontSeeText('A Configurator proposal');
    }

    #[Test]
    public function without_a_logo_the_company_name_is_used_in_the_masthead()
    {
        Setting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Epic Fox Ltd',
        ]);

        $client = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        auth()->logout();
        session()->flush();
        SettingsFacade::forget();

        $this->get(route('proposal.view', ['proposal' => $proposal->uuid]))
            ->assertOk()
            ->assertSeeText('Epic Fox Ltd proposal');
    }
}
