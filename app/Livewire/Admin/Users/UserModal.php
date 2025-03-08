<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\View\View;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Hash;
use App\Livewire\Admin\AdminComponent;

class UserModal extends ModalComponent
{
    public $roles = [];
    public ?User $user = null;
    public string $name = '';
    public string $lastName = '';
    public string $role = '';
    public string $email = '';
    public bool $active = true;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'role' => 'required',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($this->user?->id ?? 0),
        ];

        if ($this->user) {
            $rules['password'] = 'nullable|min:8';
            $rules['password_confirmation'] = 'nullable|same:password';
        } else {
            $rules['password'] = 'required|min:8';
            $rules['password_confirmation'] = 'required|same:password';
        }
        return $rules;
    }

    public function mount(?int $userId = null)
    {
        if ($userId) {
            $this->user = User::find($userId);
            if ($this->user) {
                $this->name = $this->user->name;
                $this->lastName = $this->user->last_name;
                $this->email = $this->user->email;
                $this->active = $this->user->active;
                $this->role = $this->user->roles->first()?->name ?? '';
            }
        }

        $this->roles = Role::all();
    }

    public function save()
    {
        $this->validate();

        if ($this->user) {
            $updateData = [
                'name' => $this->name,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'active' => $this->active
            ];

            if (!empty($this->password)) {
                $updateData['password'] = Hash::make($this->password);
            }

            $this->user->update($updateData);

            $this->user->syncRoles($this->role);
        } else {
            $user = User::create([
                'name' => $this->name,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'active' => $this->active,
                'password' => Hash::make($this->password)
            ]);

            $user->assignRole($this->role);
        }

        $this->dispatch('toast', ...AdminComponent::success(['text' => ($this->user ? 'User updated successfully' : 'User created successfully')]));
        $this->dispatch('closeModal'); // Close modal after save
        $this->reset();
        $this->dispatch('refresh-users');
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-modal');
    }
}
