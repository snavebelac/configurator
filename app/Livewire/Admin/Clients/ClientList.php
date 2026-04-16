<?php

namespace App\Livewire\Admin\Clients;

use App\Livewire\Admin\AdminComponent;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ClientList extends AdminComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    private int $pageLength = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[On('refresh-clients')]
    public function loadClients(): void
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

    public function render(): View
    {
        $clients = Client::when($this->search !== '', function ($query) {
            $term = '%'.$this->search.'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhere('contact', 'like', $term)
                    ->orWhere('contact_email', 'like', $term);
            });
        })
            ->orderBy('name')
            ->paginate($this->pageLength);

        return view('livewire.admin.clients.client-list', [
            'clients' => $clients,
            'total' => Client::count(),
        ]);
    }
}
