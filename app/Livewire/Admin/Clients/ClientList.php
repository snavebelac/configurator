<?php

namespace App\Livewire\Admin\Clients;

use App\Livewire\Admin\AdminComponent;
use App\Models\Client;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ClientList extends AdminComponent
{
    use WithPagination;

    public $search = '';
    private $pageLength = 5;

    #[On('refresh-features')]
    public function loadClientss(): void
    {
        $this->resetPage();
    }

    public function delete(int $clientId): void
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->delete();
            $this->dispatch('toast', ...$this->success(['text' => 'Client deleted successfully']));
            $this->dispatch('refresh-clients');
        } else {
            $reason = 'Unable to delete. Client cannot be found';
            $this->dispatch('toast', ...$this->warning(['text' => $reason]));
        }
    }

    public function render()
    {
        $clients = Client::when($this->search != '', fn ($query) => $query
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('contact', 'like', '%' . $this->search . '%')
            ->orWhere('contact_email', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name')
            ->paginate($this->pageLength);
        return view('livewire.admin.clients.client-list', [
            'clients' => $clients
        ]);
    }
}
