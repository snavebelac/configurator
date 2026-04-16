<?php

namespace App\Livewire\Admin\Packages;

use App\Livewire\Admin\AdminComponent;
use App\Models\Package;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('New package')]
class PackageCreate extends AdminComponent
{
    #[Validate('required|max:255')]
    public string $name = '';

    #[Validate('nullable|max:2000')]
    public string $description = '';

    protected $messages = [
        'name.required' => 'Give this package a name',
    ];

    public function createPackage()
    {
        $this->validate();

        $package = Package::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
        ]);

        $this->dispatch('toast', ...$this->success(['text' => 'Package created — now add the features']));

        return redirect()->route('dashboard.package.edit', ['package' => $package->id]);
    }

    public function render(): View
    {
        return view('livewire.admin.packages.package-create');
    }
}
