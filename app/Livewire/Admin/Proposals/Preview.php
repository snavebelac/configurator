<?php

namespace App\Livewire\Admin\Proposals;

use App\Facades\Settings;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Preview extends Component
{
    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $proposal->load(['features', 'user', 'client']);
        $this->proposal = $proposal;
    }

    public function render(): View
    {
        $features = $this->proposal->features;

        $roots = $features->whereNull('parent_id')
            ->sortBy([['order', 'asc'], ['name', 'asc']])
            ->values();

        $groups = $roots->map(fn ($root) => [
            'root' => $root,
            'children' => $features->where('parent_id', $root->id)->sortBy('name')->values(),
        ]);

        $requiredTotal = (float) $features->where('optional', false)
            ->sum(fn ($f) => $f->price * $f->quantity);

        $optionalFeatures = $features->where('optional', true);
        $optionalTotal = (float) $optionalFeatures
            ->sum(fn ($f) => $f->price * $f->quantity);

        $optionalInitial = $optionalFeatures
            ->mapWithKeys(fn ($f) => [(string) $f->id => [
                'on' => true,
                'price' => (float) ($f->price * $f->quantity),
            ]])
            ->all();

        return view('livewire.admin.proposals.preview', [
            'groups' => $groups,
            'requiredTotal' => $requiredTotal,
            'optionalTotal' => $optionalTotal,
            'optionalCount' => $optionalFeatures->count(),
            'optionalInitial' => $optionalInitial,
            'taxName' => Settings::getTaxName(),
            'taxRate' => (float) Settings::getTaxRate(),
        ])->title($this->proposal->name.' — Proposal');
    }
}
