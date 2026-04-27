<?php

namespace App\Livewire\Admin\Shared;

use App\Models\Client;
use App\Models\Feature;
use App\Models\Package;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $open = false;

    public string $query = '';

    private const RESULT_LIMIT = 5;

    #[On('open-palette')]
    public function openPalette(): void
    {
        $this->open = true;
    }

    public function closePalette(): void
    {
        $this->open = false;
        $this->query = '';
    }

    public function updatedOpen(bool $value): void
    {
        if (! $value) {
            $this->query = '';
        }
    }

    /**
     * @return array{
     *     suggested: array<int, array{label: string, route: string, hint?: string, icon: string}>,
     *     proposals: Collection<int, Proposal>,
     *     clients: Collection<int, Client>,
     *     features: Collection<int, Feature>,
     *     packages: Collection<int, Package>,
     * }
     */
    #[Computed]
    public function results(): array
    {
        $tenantId = session('tenant_id');
        $term = trim($this->query);

        if ($term === '') {
            return [
                'suggested' => $this->suggestedActions(),
                'proposals' => Proposal::query()->with('client')->latest('updated_at')->take(self::RESULT_LIMIT)->get(),
                'clients' => collect(),
                'features' => collect(),
                'packages' => collect(),
            ];
        }

        return [
            'suggested' => $this->matchingActions($term),
            'proposals' => Proposal::search($term)
                ->where('tenant_id', $tenantId)
                ->take(self::RESULT_LIMIT)
                ->query(fn ($q) => $q->with('client'))
                ->get(),
            'clients' => Client::search($term)
                ->where('tenant_id', $tenantId)
                ->take(self::RESULT_LIMIT)
                ->get(),
            'features' => Feature::search($term)
                ->where('tenant_id', $tenantId)
                ->take(self::RESULT_LIMIT)
                ->get(),
            'packages' => Package::search($term)
                ->where('tenant_id', $tenantId)
                ->take(self::RESULT_LIMIT)
                ->get(),
        ];
    }

    /**
     * @return array<int, array{label: string, route: string, hint?: string, icon: string}>
     */
    private function suggestedActions(): array
    {
        return [
            ['label' => 'Create new proposal', 'route' => 'dashboard.proposal.create', 'icon' => 'plus', 'hint' => 'Action'],
            ['label' => 'Add a new feature', 'route' => 'dashboard.features', 'icon' => 'stack', 'hint' => 'Action'],
            ['label' => 'Build a new package', 'route' => 'dashboard.package.create', 'icon' => 'cube', 'hint' => 'Action'],
            ['label' => 'Invite a teammate', 'route' => 'dashboard.users', 'icon' => 'user-circle', 'hint' => 'Action'],
        ];
    }

    /**
     * @return array<int, array{label: string, route: string, hint?: string, icon: string}>
     */
    private function matchingActions(string $term): array
    {
        $needle = mb_strtolower($term);

        return array_values(array_filter(
            $this->suggestedActions(),
            fn (array $action) => str_contains(mb_strtolower($action['label']), $needle),
        ));
    }

    public function render(): View
    {
        return view('livewire.admin.shared.command-palette', [
            'results' => $this->results,
        ]);
    }
}
