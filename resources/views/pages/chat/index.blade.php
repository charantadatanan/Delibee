<?php

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\Conversation;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Livewire\Attributes\Title('Bee Chat')] class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function conversations()
    {
        $query = Conversation::query()
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereHas('business', fn ($q) => $q->where('owner_id', auth()->id()));
            })
            ->with(['business', 'user', 'messages'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('business', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('subject', 'like', "%{$this->search}%");
            });
        }

        return $query->paginate(20);
    }

    public function startConversation(int $businessId): void
    {
        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', BusinessStatus::Active)
            ->firstOrFail();

        $conversation = Conversation::firstOrCreate([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
        ]);

        $this->redirect(route('chat.show', $conversation), navigate: true);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Bee Chat</h1>
            <p class="mt-1 text-sm text-zinc-500">Messages with sellers and customers.</p>
        </div>

        <div class="relative max-w-xs flex-1">
            <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-zinc-400" />
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search conversations..."
                   class="w-full rounded-lg border border-zinc-300 bg-white pl-10 pr-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900">
        </div>
    </div>

    @if ($this->conversations()->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <p class="text-lg font-medium">No conversations yet.</p>
            <p class="mt-1 text-sm text-zinc-500">Start chatting with a seller from their product page.</p>
            <a href="{{ route('marketplace') }}"
               class="mt-4 inline-block rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white hover:bg-amber-600"
               wire:navigate>
                Browse the marketplace
            </a>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($this->conversations() as $conversation)
                @php
                    $isCustomer = $conversation->user_id === auth()->id();
                    $otherParty = $isCustomer ? $conversation->business->name : $conversation->user->name;
                    $lastMessage = $conversation->messages->last();
                @endphp
                <a href="{{ route('chat.show', $conversation) }}"
                   class="block rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-amber-300 hover:bg-amber-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-amber-800 dark:hover:bg-amber-500/10"
                   wire:navigate>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <flux:avatar name="{{ $otherParty }}" class="size-10" />
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $otherParty }}</p>
                                <p class="text-xs text-zinc-500 truncate">
                                    @if ($conversation->subject)
                                        {{ $conversation->subject }}
                                    @else
                                        @if ($isCustomer)
                                            Conversation with {{ $conversation->business->name }}
                                        @else
                                            Conversation with {{ $conversation->user->name }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1 text-right min-w-[100px]">
                            @if ($lastMessage)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 truncate max-w-[200px]">
                                    {{ $lastMessage->body }}
                                </p>
                                <p class="text-xs text-zinc-400">
                                    {{ $lastMessage->created_at->diffForHumans() }}
                                </p>
                            @endif
                            @if ($lastMessage && ! $lastMessage->read_at && $lastMessage->sender_id !== auth()->id())
                                <span class="size-2 rounded-full bg-amber-500" />
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </ul>

        <div class="mt-6">{{ $this->conversations()->links() }}</div>
    @endif
</div>