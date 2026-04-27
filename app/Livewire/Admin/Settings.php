<?php

namespace App\Livewire\Admin;

use App\Enums\CurrencySymbol;
use App\Helpers\SettingsHelper;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Workspace settings')]
class Settings extends AdminComponent
{
    #[Validate('required|in:pound,dollar,euro')]
    public string $currency = '';

    #[Validate('required|string|max:32')]
    public string $taxName = '';

    #[Validate('required|numeric|min:0|max:100')]
    public string $taxRate = '';

    public bool $taxInclusive = false;

    /**
     * Blank string is treated as "never" — stored as null.
     */
    #[Validate('nullable|integer|min:1|max:3650')]
    public string $defaultShareExpiryDays = '';

    public function mount(): void
    {
        $setting = Setting::firstOrCreate([], [
            'currency' => CurrencySymbol::GBP,
            'tax_rate' => 20,
            'tax_name' => 'VAT',
            'tax_inclusive' => false,
        ]);

        $this->currency = $setting->currency->value;
        $this->taxName = $setting->tax_name;
        $this->taxRate = (string) $setting->tax_rate;
        $this->taxInclusive = (bool) $setting->tax_inclusive;
        $this->defaultShareExpiryDays = $setting->default_share_expiry_days === null
            ? ''
            : (string) $setting->default_share_expiry_days;
    }

    public function save(): void
    {
        $this->validate();

        $setting = Setting::first();

        $setting->update([
            'currency' => $this->currency,
            'tax_name' => $this->taxName,
            'tax_rate' => (float) $this->taxRate,
            'tax_inclusive' => $this->taxInclusive,
            'default_share_expiry_days' => $this->defaultShareExpiryDays === '' ? null : (int) $this->defaultShareExpiryDays,
        ]);

        // Reset the singleton so subsequent requests pick up the new values.
        app()->forgetInstance(SettingsHelper::class);

        $this->dispatch('toast', ...$this->success(['text' => 'Settings updated']));
    }

    /**
     * @return array<string, string>
     */
    public function currencyOptions(): array
    {
        return [
            CurrencySymbol::GBP->value => 'GBP — £',
            CurrencySymbol::USD->value => 'USD — $',
            CurrencySymbol::EUR->value => 'EUR — €',
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.settings', [
            'currencyOptions' => $this->currencyOptions(),
        ]);
    }
}
