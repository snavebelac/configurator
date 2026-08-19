<?php

namespace App\Livewire\Admin;

use App\Enums\CurrencySymbol;
use App\Facades\Settings as SettingsFacade;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

#[Title('Settings')]
class Settings extends AdminComponent
{
    use WithFileUploads;

    #[Validate('nullable|string|max:255')]
    public ?string $companyName = null;

    /**
     * A newly-chosen file, before it is committed on save. The stored path
     * lives on the settings row, not here.
     *
     * SVG is deliberately excluded: it can carry script, and this file is
     * served from our own origin on the public client-facing proposal.
     */
    #[Validate('nullable|image|mimes:png,jpg,jpeg,webp|max:2048')]
    public $logo = null;

    #[Validate('required|string')]
    public string $currency = '';

    #[Validate('required|string|max:40')]
    public string $taxName = '';

    #[Validate('required|numeric|min:0|max:100')]
    public string $taxRate = '';

    public bool $taxInclusive = false;

    public function mount(): void
    {
        $setting = $this->setting();

        $this->companyName = $setting->company_name;
        $this->currency = $setting->currency->value;
        $this->taxName = $setting->tax_name;
        $this->taxRate = (string) $setting->tax_rate;
        $this->taxInclusive = (bool) $setting->tax_inclusive;
    }

    public function removeLogo(): void
    {
        $setting = $this->setting();

        if (filled($setting->logo)) {
            Storage::disk('public')->delete($setting->logo);
            $setting->logo = null;
            $setting->save();
        }

        $this->logo = null;

        $this->dispatch('toast', ...$this->warning(['text' => 'Logo removed']));
    }

    public function save(): void
    {
        $this->validate();

        $setting = $this->setting();

        if ($this->logo !== null) {
            // Replace rather than accumulate — the old file is no longer
            // referenced by anything once the row points at the new one.
            if (filled($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            $setting->logo = $this->logo->store('logos', 'public');
            $this->logo = null;
        }

        $setting->fill([
            'company_name' => $this->companyName,
            'currency' => $this->currency,
            'tax_name' => $this->taxName,
            'tax_rate' => (float) $this->taxRate,
            'tax_inclusive' => $this->taxInclusive,
        ])->save();

        // SettingsHelper is a request-lifetime singleton, so anything rendered
        // after this point would otherwise still show the pre-save values.
        SettingsFacade::forget();

        $this->dispatch('toast', ...$this->success(['text' => 'Settings updated']));
    }

    /**
     * The tenant's settings row, created on first visit if it doesn't exist.
     *
     * BelongsToTenant fills tenant_id from the session on create, and scopes
     * the lookup, so this is already tenant-safe.
     */
    private function setting(): Setting
    {
        return Setting::firstOrCreate([]);
    }

    /**
     * @return array<string, string>
     */
    private function currencyOptions(): array
    {
        $options = [];

        foreach (CurrencySymbol::cases() as $case) {
            $options[$case->value] = $case->name.' ('.$case->toSymbol().')';
        }

        return $options;
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'currencyOptions' => $this->currencyOptions(),
            'storedLogo' => $this->setting()->logo,
        ]);
    }
}
