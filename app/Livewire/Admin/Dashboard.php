<?php

namespace App\Livewire\Admin;

use App\Enums\Status;
use App\Models\Proposal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends AdminComponent
{
    public function render(): View
    {
        $proposals = Proposal::with(['user', 'client', 'features'])->get();

        $open = $proposals->whereIn('status', [Status::DRAFT, Status::DELIVERED]);
        $accepted = $proposals->where('status', Status::ACCEPTED);
        $rejected = $proposals->where('status', Status::REJECTED);

        $monthStart = Carbon::now()->startOfMonth();
        $wonThisMonth = $accepted->filter(fn (Proposal $p) => $p->updated_at >= $monthStart);

        $closedCount = $accepted->count() + $rejected->count();
        $conversion = $closedCount > 0 ? ($accepted->count() / $closedCount) * 100 : null;

        $closed = $accepted->merge($rejected);
        $avgValue = $closed->isNotEmpty()
            ? $closed->sum(fn (Proposal $p) => $p->total()) / $closed->count()
            : 0;

        $stuckDelivered = $proposals
            ->where('status', Status::DELIVERED)
            ->filter(fn (Proposal $p) => $p->updated_at->lt(Carbon::now()->subDays(14)));

        $staleDrafts = $proposals
            ->where('status', Status::DRAFT)
            ->filter(fn (Proposal $p) => $p->updated_at->lt(Carbon::now()->subDays(7)));

        $needsAttention = $stuckDelivered
            ->concat($staleDrafts)
            ->sortByDesc('updated_at')
            ->values();

        $recent = $proposals->sortByDesc('updated_at')->take(8)->values();

        $counts = [
            'all' => $proposals->count(),
            'draft' => $proposals->where('status', Status::DRAFT)->count(),
            'delivered' => $proposals->where('status', Status::DELIVERED)->count(),
            'accepted' => $accepted->count(),
            'rejected' => $rejected->count(),
        ];

        return view('livewire.admin.dashboard', [
            'user' => Auth::user(),
            'openValue' => $open->sum(fn (Proposal $p) => $p->total()),
            'wonThisMonth' => $wonThisMonth->sum(fn (Proposal $p) => $p->total()),
            'wonCount' => $wonThisMonth->count(),
            'monthAccepted' => $accepted->filter(fn (Proposal $p) => $p->updated_at >= $monthStart)->count(),
            'conversion' => $conversion,
            'closedCount' => $closedCount,
            'avgValue' => $avgValue,
            'needsAttention' => $needsAttention,
            'recent' => $recent,
            'counts' => $counts,
        ]);
    }
}
