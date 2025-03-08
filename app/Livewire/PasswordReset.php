<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

#[Title('Reset Password')]
class PasswordReset extends Component
{
    #[Validate('required')]
    public string $token;
    #[Validate('required|email')]
    public $email = '';
    #[Validate('required|min:8')]
    public $password = '';
    #[Validate('min:8|required_with:password|same:password')]
    public $passwordConfirmation = '';

    public $message;

    public function updatePassword(): void
    {
        $this->validate();

        $status = Password::reset([
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
            'token' => $this->token,
        ], function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();
        });

        if ($status === Password::PasswordReset) {
            redirect()->route('login');
        } else {
            $this->message = __($status);
        }
    }

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function render()
    {
        return view('livewire.password-reset');
    }
}
