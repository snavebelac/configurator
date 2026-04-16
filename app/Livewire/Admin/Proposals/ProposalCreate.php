<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Livewire\Admin\AdminComponent;
use App\Models\Client;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

#[Title('Create a new proposal')]
class ProposalCreate extends AdminComponent
{
    use WithPagination;

    public string $featureSearch = '';

    public int $pageLength = 12;

    /** @var array<int> */
    #[Validate('required|array|min:1')]
    public array $selectedFeatureIds = [];

    #[Validate('required|max:255')]
    public string $name = '';

    #[Validate('required')]
    public ?int $clientId = null;

    protected $messages = [
        'selectedFeatureIds.required' => 'Please select at least one feature',
        'selectedFeatureIds.min' => 'Please select at least one feature',
        'clientId.required' => 'Please choose a client',
        'name.required' => 'Give this proposal a name',
    ];

    public function updatedFeatureSearch(): void
    {
        $this->resetPage(pageName: 'features-page');
    }

    public function selectFeature(int $featureId): void
    {
        if (! in_array($featureId, $this->selectedFeatureIds, true)) {
            $this->selectedFeatureIds[] = $featureId;
        }
    }

    public function removeFeature(int $featureId): void
    {
        $this->selectedFeatureIds = array_values(array_diff($this->selectedFeatureIds, [$featureId]));
    }

    public function createProposal()
    {
        $this->validate();

        $proposal = new Proposal([
            'status' => Status::DRAFT,
            'name' => $this->name,
        ]);

        $proposal->client()->associate($this->clientId);
        $proposal->user()->associate(auth()->user());
        $proposal->save();

        $features = Feature::whereIn('id', $this->selectedFeatureIds)->get();
        foreach ($features as $feature) {
            $ff = new FinalFeature([
                'name' => $feature->name,
                'description' => $feature->description,
                'price' => $feature->price,
                'quantity' => $feature->quantity,
                'optional' => $feature->optional,
                'order' => $feature->order,
            ]);
            $ff->proposal()->associate($proposal);
            $ff->save();
        }

        $this->dispatch('toast', ...$this->success(['text' => 'Proposal created — now fine-tune the details']));

        return redirect()->route('dashboard.proposal.edit', ['proposal' => $proposal->id]);
    }

    public function render(): View
    {
        $clients = Client::orderBy('name')->get();

        $selectedFeatures = Feature::whereIn('id', $this->selectedFeatureIds)
            ->orderBy('name')
            ->get();

        $selectedTotal = $selectedFeatures->sum(fn ($feature) => $feature->price * $feature->quantity);

        $features = Feature::when($this->featureSearch !== '', fn ($query) => $query->where('name', 'like', '%'.$this->featureSearch.'%'))
            ->orderBy('name')
            ->paginate($this->pageLength, pageName: 'features-page');

        return view('livewire.admin.proposals.proposal-create', [
            'features' => $features,
            'selectedFeatures' => $selectedFeatures,
            'selectedTotal' => $selectedTotal,
            'clients' => $clients,
        ]);
    }
}
