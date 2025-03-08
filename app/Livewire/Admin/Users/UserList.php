<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\AdminComponent;

#[Title('Users')]
class UserList extends AdminComponent
{
    public $users = [];

    public function mount(): void
    {
        $this->loadUsers();
    }

    #[On('refresh-users')]
    public function loadUsers(): void
    {
        $this->users = User::with('roles')->orderBy('last_name')->orderBy('name')->get();
    }

    public function delete(int $userId): void
    {
        $user = User::find($userId);
        if ($user && $user->canBeDeleted() && $user->id != Auth::id()) {
            $user->delete();
            $this->dispatch('toast', ...$this->success(['text' => 'User deleted successfully']));
            $this->dispatch('refresh-users');
        } else {
            $reason = 'Unable to delete user';

            if (!$user): $reason .= '. User cannot be found.';
            elseif (!$user->canBeDeleted()): $reason .= '. User has existing associated data.';
            elseif ($user->id == Auth::id()): $reason .= '. Cannot delete yourself.';
            endif;

            $this->dispatch('toast', ...$this->warning(['text' => $reason]));
        }
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-list');
    }
}
