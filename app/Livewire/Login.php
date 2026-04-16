<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Login')]
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
            'active' => true,
        ], $this->remember)) {
            session()->regenerate();
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
