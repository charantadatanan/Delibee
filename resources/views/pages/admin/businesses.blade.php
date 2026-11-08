<?php

use App\Enums\BusinessStatus;
use App\Models\Business;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Hive Guard — Businesses')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    #[Computed]
    public function businesses()
    {
        return Business::query()
            ->with('owner')
            ->withCount('orders')
            ->when(
                $this->statusFilter !== 'all',
                fn ($query) => $query->where('status', $this->statusFilter),
            )
            ->orderBy('created_at')
            ->paginate(20);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function approve(int $businessId): void
    {
        $business = $this->findBusiness($businessId);
        Gate::authorize('updateStatus', $business);

        if ($business->status !== BusinessStatus::Pending) {
            return;
        }

        $business->update(['status' => BusinessStatus::Active]);

        Flux::toast("{$business->name} approved.", variant: 'success');

        unset($this->businesses);
    }

    public function suspend(int $businessId): void
    {
        $business = $this->findBusiness($businessId);
        Gate::authorize('updateStatus', $business);

        if ($business->status !== BusinessStatus::Active) {
            return;
        }

        $business->update(['status' => BusinessStatus::Suspended]);

        Flux::toast("{$business->name} suspended.", variant: 'success');

        unset($this->businesses);
    }

    public function reactivate(int $businessId): void
    {
        $business = $this->findBusiness($businessId);
        Gate::authorize('updateStatus', $business);

        if ($business->status !== BusinessStatus::Suspended) {
            return;
        }

        $business->update(['status' => BusinessStatus::Active]);

        Flux::toast("{$business->name} reactivated.", variant: 'success');

        unset($this->businesses);
    }

    private function findBusiness(int $businessId): Business
    {
        return Business::query()->findOrFail($businessId);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Hive Guard</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
                Approve new shops, suspend sellers who break the rules, and bring them back when they are ready.
            </p>
        </div>

        <label class="block">
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</span>
            <select wire:model.live="statusFilter"
                    class="mt-1.5 rounded-lg border border-comb bg-paper/60 px-3 py-2 text-sm focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950">
                <option value="all">All statuses</option>
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </label>
    </div>

    @if ($this->businesses()->isEmpty())
        <div class="rounded-2xl border border-dashed border-comb bg-white/50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
            <p class="font-display text-xl font-semibold">No shops match this filter.</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Try another status.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-comb/90 dark:border-zinc-800">
            <ul class="divide-y divide-comb/70 dark:divide-zinc-800">
                @foreach ($this->businesses() as $business)
                    @php($statusClasses = match ($business->status) {
                        BusinessStatus::Active => 'bg-leaf/10 text-leaf dark:bg-leaf/20 dark:text-emerald-300',
                        BusinessStatus::Pending => 'bg-honey/15 text-honey-deep dark:bg-honey/15 dark:text-honey',
                        BusinessStatus::Suspended => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                    })

                    <li class="flex flex-wrap items-center gap-4 bg-white/70 p-4 dark:bg-zinc-900/70">
                        <span class="clip-hex grid size-10 shrink-0 place-items-center bg-honey font-display text-sm font-bold text-ink">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->name, 0, 1)) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($business->status === BusinessStatus::Active)
                                    <a href="{{ route('businesses.show', $business->slug) }}"
                                       class="font-display text-lg font-semibold transition-colors hover:text-honey-deep dark:hover:text-honey"
                                       wire:navigate>
                                        {{ $business->name }}
                                    </a>
                                @else
                                    <span class="font-display text-lg font-semibold">{{ $business->name }}</span>
                                @endif

                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ $business->status->label() }}
                                </span>
                            </div>

                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $business->owner?->name ?? 'Unknown owner' }}, {{ $business->owner?->email }},
                                {{ $business->orders_count }} {{ \Illuminate\Support\Str::plural('order', $business->orders_count) }},
                                joined {{ $business->created_at?->format('j M Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($business->status === BusinessStatus::Pending)
                                <button type="button"
                                        wire:click="approve({{ $business->id }})"
                                        class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                                    Approve
                                </button>
                            @elseif ($business->status === BusinessStatus::Active)
                                <button type="button"
                                        wire:click="suspend({{ $business->id }})"
                                        wire:confirm="Suspend {{ $business->name }}? Their products will be hidden from the marketplace."
                                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-500/10">
                                    Suspend
                                </button>
                            @else
                                <button type="button"
                                        wire:click="reactivate({{ $business->id }})"
                                        class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                                    Reactivate
                                </button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6">{{ $this->businesses()->links() }}</div>
    @endif
</div>
