<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Password;

#[Title('Reset your password')]
class ForgottenPassword extends Component
{
    #[Validate('required|email')]
    public $email = '';

    public ?string $successMessage;
    public ?string $errorMessage;

    public function resetPassword(): void
    {
        $this->reset('successMessage', 'errorMessage');
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);
        if ($status === Password::ResetLinkSent) {
            $this->successMessage = __($status);
        } else {
            $this->errorMessage = __($status);
        }
    }

    public function render()
    {
        return view('livewire.forgotten-password');
    }
}
