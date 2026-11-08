<?php

namespace App\Livewire\Storefront;

use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Ranking extends Component
{
    public function render(RankingService $ranking): View
    {
        $periodStart = now()->subMonth()->startOfMonth();

        $published = $ranking->published('month', $periodStart);
        $rising = $ranking->risingBees();

        return view('livewire.storefront.ranking', [
            'published' => $published,
            'rising' => $rising,
            'periodStart' => $periodStart,
        ]);
    }
}
