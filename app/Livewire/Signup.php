<?php

namespace App\Livewire;

use App\Helpers\TenantProvisioner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create your workspace')]
class Signup extends Component
{
    public string $company = '';

    public string $name = '';

    public string $lastName = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed:passwordConfirmation', Password::default()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'company' => 'company name',
            'name' => 'first name',
            'lastName' => 'last name',
        ];
    }

    public function register(TenantProvisioner $provisioner): void
    {
        $this->validate();

        $user = $provisioner->provision([
            'company' => $this->company,
            'name' => $this->name,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
        ]);

        // Fires the Login event, which SetTenantIdInSession picks up to put
        // tenant_id into the session — everything tenant-scoped depends on it.
        Auth::login($user);

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.signup');
    }
}
