<?php

namespace App\Livewire\Admin\Clients;


use App\Livewire\Admin\AdminComponent;
use App\Models\Client;
use Livewire\Attributes\Locked;
use LivewireUI\Modal\ModalComponent;

class ClientModal extends ModalComponent
{

    #[Locked]
    public ?int $clientId = null;
    public string $name = '';
    public string $contact = '';
    public string $contactEmail = '';
    public string $contactPhone = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'contact' => 'required|string',
            'contactEmail' => 'required|max:255|email',
            'contactPhone' => 'max:255',
        ];
    }

    public function mount(?int $clientId = null): void
    {
        if ($clientId) {
            $this->clientId = $clientId;
            $client = Client::find($clientId);
            if ($client) {
                $this->name = $client->name;
                $this->contact = $client->contact;
                $this->contactEmail = $client->contact_email;
                $this->contactPhone = $client->contact_phone;
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $updateData = [
            'name' => $this->name,
            'contact' => $this->contact,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
        ];

        if ($this->clientId) {
            Client::findOrFail($this->clientId)->update($updateData);
        } else {
            Client::create($updateData);
        }

        $this->dispatch('toast', ...AdminComponent::success(['text' => ($this->clientId ? 'Client updated successfully' : 'Client created successfully')]));
        $this->dispatch('closeModal'); // Close modal after save
        $this->reset();
        $this->dispatch('refresh-clients');
    }

    public function render()
    {
        return view('livewire.admin.clients.client-modal');
    }
}
