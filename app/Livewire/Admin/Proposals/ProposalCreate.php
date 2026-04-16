<?php

namespace App\Livewire\Admin\Proposals;

use App\Enums\Status;
use App\Livewire\Admin\AdminComponent;
use App\Models\Client;
use App\Models\Feature;
use App\Models\FinalFeature;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Create a new proposal')]
class ProposalCreate extends AdminComponent
{
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

    #[On('feature-picked')]
    public function handlePicked(int $featureId): void
    {
        $this->selectFeature($featureId);
    }

    public function selectFeature(int $featureId): void
    {
        if (in_array($featureId, $this->selectedFeatureIds, true)) {
            return;
        }

        $feature = Feature::find($featureId);
        if (! $feature) {
            return;
        }

        if ($feature->parent_id && ! in_array($feature->parent_id, $this->selectedFeatureIds, true)) {
            $this->selectedFeatureIds[] = $feature->parent_id;
            $parent = $feature->parent;
            if ($parent) {
                $this->dispatch('toast', ...$this->success([
                    'text' => "Added \"{$parent->name}\" — required for \"{$feature->name}\"",
                ]));
            }
        }

        $this->selectedFeatureIds[] = $featureId;
    }

    public function removeFeature(int $featureId): void
    {
        $toRemove = [$featureId];

        $childIds = Feature::where('parent_id', $featureId)
            ->whereIn('id', $this->selectedFeatureIds)
            ->pluck('id')
            ->all();

        if (! empty($childIds)) {
            $toRemove = array_merge($toRemove, $childIds);
            $count = count($childIds);
            $this->dispatch('toast', ...$this->success([
                'text' => "Also removed {$count} child ".Str::plural('feature', $count),
            ]));
        }

        $this->selectedFeatureIds = array_values(array_diff($this->selectedFeatureIds, $toRemove));
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
        $featureToFinal = [];

        $roots = $features->whereNull('parent_id')->sortBy('name')->values();
        foreach ($roots as $index => $feature) {
            $featureToFinal[$feature->id] = $this->snapshotFeature($feature, $proposal, null, $index + 1)->id;
        }

        foreach ($features->whereNotNull('parent_id') as $feature) {
            $parentFinalId = $featureToFinal[$feature->parent_id] ?? null;
            $featureToFinal[$feature->id] = $this->snapshotFeature($feature, $proposal, $parentFinalId, 0)->id;
        }

        $this->dispatch('toast', ...$this->success(['text' => 'Proposal created — now fine-tune the details']));

        return redirect()->route('dashboard.proposal.edit', ['proposal' => $proposal->id]);
    }

    private function snapshotFeature(Feature $feature, Proposal $proposal, ?int $parentFinalId, int $order): FinalFeature
    {
        $finalFeature = new FinalFeature([
            'name' => $feature->name,
            'description' => $feature->description,
            'price' => $feature->price,
            'quantity' => $feature->quantity,
            'optional' => $feature->optional,
            'parent_id' => $parentFinalId,
            'source_feature_id' => $feature->id,
            'order' => $order,
        ]);
        $finalFeature->proposal()->associate($proposal);
        $finalFeature->save();

        return $finalFeature;
    }

    public function render(): View
    {
        $clients = Client::orderBy('name')->get();

        $selectedFeatures = Feature::whereIn('id', $this->selectedFeatureIds)->get();
        $selectedTotal = $selectedFeatures->sum(fn ($feature) => $feature->price * $feature->quantity);

        $selectedGroups = $this->groupFeaturesByParent($selectedFeatures);

        return view('livewire.admin.proposals.proposal-create', [
            'selectedFeatures' => $selectedFeatures,
            'selectedGroups' => $selectedGroups,
            'selectedTotal' => $selectedTotal,
            'clients' => $clients,
        ]);
    }

    private function groupFeaturesByParent(Collection $features): Collection
    {
        $roots = $features->whereNull('parent_id')->sortBy('name')->values();

        return $roots->map(fn ($root) => [
            'root' => $root,
            'children' => $features->where('parent_id', $root->id)->sortBy('name')->values(),
        ]);
    }
}
