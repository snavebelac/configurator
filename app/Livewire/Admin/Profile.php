<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

#[Title('Profile')]
class Profile extends AdminComponent
{

    #[Locked]
    public int $userId;

    #[Validate('required')]
    public string $name;
    #[Validate('required')]
    public string $lastName;
    #[Validate('required|email')]
    public string $email;

    public function mount()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->lastName = $user->last_name;

    }

    public function updateUser(User $user): void
    {
        $this->validate();

        if ($user->id === Auth::id()) {
            $user->name = $this->name;
            $user->last_name = $this->lastName;
            $user->email = $this->email;
            $user->save();

            $this->dispatch('toast', ...$this->success(['text' => 'Profile updated successfully']));
        } else {
            $this->dispatch('toast', ...$this->warning(['text' => 'You can\'t update this profile']));
        }
    }

    public function render()
    {
        return view('livewire.admin.profile');
    }
}
