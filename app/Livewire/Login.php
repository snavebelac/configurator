<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

#[Title("Login")]
class Login extends Component
{
    #[Validate('required|email')]
    public $email = '';
    #[Validate('required')]
    public $password = '';
    public bool $remember = false;

    public $loginMessage = '';

    public function authenticate(): void
    {
        $this->validate();

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'active' => true
        ], $this->remember)) {
            Request::session()->regenerate();
            $this->redirectIntended(route('dashboard'));
        } else {
            $this->loginMessage = 'Email or Password not recognised';
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}
