<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Feature;
use App\Models\Proposal;
use App\Facades\Formatter;
use Livewire\WithPagination;
use App\Models\FinalFeature;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use App\Livewire\Admin\AdminComponent;

#[Title('Create a new proposal')]
class ProposalCreate extends AdminComponent
{
    use WithPagination;

    public string $featureSearch = '';
    public int $pageLength = 16;
    public ?int $proposalId = null;
    #[Validate('required|array|min:1')]
    public array $selectedFeatureIds = [];
    #[Validate('required|max:255')]
    public string $name = '';
    #[Validate('required')]
    public ?int $clientId = null;

    protected $messages = [
        'selectedFeatureIds' => 'Please select at least one feature',
        'clientId' => 'Please select the client'
    ];

    public function createProposal(): void
    {
        $this->validate();

//        $selectedFeatures = Feature::whereIn('id', $this->selectedFeatureIds)
//            ->orderBy('name')
//            ->get();

        $proposal = new Proposal([
            'status' => Status::DRAFT,
            'name' => $this->name,
        ]);

        $proposal->client()->associate($this->clientId);
        $proposal->user()->associate(auth()->user());
        $proposal->save();
        // copy features to finalFeatures table so they can be edited
        // at will without changing the default features
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
        $proposal->total_price = 0; // mutator will calculate this for us (TODO: move to static:creating)
        $proposal->save();
    }

    public function selectFeature(int $featureId): void
    {
        $this->selectedFeatureIds[] = $featureId;

    }

    public function removeFeature(int $featureId): void
    {
        $this->selectedFeatureIds = array_diff($this->selectedFeatureIds, [$featureId]);
    }

    public function render()
    {
        $clients = Client::orderBy('name')->get();
        $selectedFeatures = Feature::whereIn('id', $this->selectedFeatureIds)
            ->orderBy('name')
            ->get();
        $totalForSelectedFeatures = empty($selectedFeatures) ? 0 : Formatter::currency($selectedFeatures->sum(fn($feature) => $feature->price * $feature->quantity));
        $features = Feature::when($this->featureSearch != '', fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->pageLength, pageName: 'features-page');

        return view('livewire.admin.proposals.proposal-create', [
            'features' => $features,
            'selectedFeatures' => $selectedFeatures,
            'totalForSelectedFeatures' => $totalForSelectedFeatures,
            'clients' => $clients,
        ]);
    }
}
