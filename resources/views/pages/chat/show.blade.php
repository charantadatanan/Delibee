<?php

use App\Models\Conversation;
use App\Models\Message;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Livewire\Attributes\Title('Conversation')] class extends Component
{
    public Conversation $conversation;

    public string $newMessage = '';

    public function mount(Conversation $conversation): void
    {
        $this->authorize('view', $conversation);

        $this->conversation = $conversation->load(['business', 'user', 'messages.sender']);
    }

    #[Computed]
    public function otherParty()
    {
        return $this->conversation->user_id === auth()->id()
            ? $this->conversation->business
            : $this->conversation->user;
    }

    public function sendMessage(): void
    {
        $this->authorize('reply', $this->conversation);

        $this->validate([
            'newMessage' => ['required', 'string', 'max:4000'],
        ]);

        $message = $this->conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body' => $this->newMessage,
        ]);

        // If this is the seller's first reply, stamp it
        if ($this->conversation->first_reply_at === null &&
            auth()->id() === $this->conversation->business->owner_id) {
            $this->conversation->update([
                'first_reply_at' => now(),
            ]);
        }

        $this->conversation->update([
            'last_message_at' => now(),
        ]);

        $this->newMessage = '';

        $this->conversation = $this->conversation->fresh(['business', 'user', 'messages.sender']);
    }

    public function markAsRead(): void
    {
        $this->authorize('view', $this->conversation);

        $this->conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->conversation = $this->conversation->fresh(['business', 'user', 'messages.sender']);
    }
};
?>

<div class="space-y-6">
    @php($isCustomer = $conversation->user_id === auth()->id())
    @php($otherParty = $isCustomer ? $conversation->business : $conversation->user)

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ $isCustomer ? route('businesses.show', $otherParty->slug) : route('dashboard') }}"
               wire:navigate>
                <flux:avatar name="{{ $otherParty->name }}" class="size-10" />
            </a>
            <div>
                <h1 class="font-display text-lg font-semibold truncate">{{ $otherParty->name }}</h1>
                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                    @if ($isCustomer)
                        {{ $conversation->business->name }}
                    @else
                        {{ $conversation->user->name }}
                    @endif
                    @if ($conversation->subject)
                        , {{ $conversation->subject }}
                    @endif
                </p>
            </div>
        </div>

        <a href="{{ route('chat.index') }}"
           class="rounded-lg border border-comb bg-white/60 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-honey-deep/40 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-honey/40"
           wire:navigate>
            Back to conversations
        </a>
    </div>

    <div wire:poll.5s class="overflow-hidden rounded-2xl border border-comb/90 bg-white/70 dark:border-zinc-800 dark:bg-zinc-900/70">
        <div class="h-[400px] overflow-y-auto p-4 space-y-4" wire:scroll.near.bottom="$refresh">
            @if ($this->conversation->messages->isEmpty())
                <p class="text-center text-zinc-500 py-8">No messages yet. Start the conversation.</p>
            @else
                @foreach ($this->conversation->messages as $message)
                    @php($isOwn = $message->sender_id === auth()->id())
                    <div class="flex gap-3 {{ $isOwn ? 'flex-row-reverse' : '' }}">
                        <flux:avatar
                            name="{{ $message->sender->name }}"
                            initials="{{ $message->sender->initials() }}"
                            class="size-8 shrink-0 mt-1" />

                        <div class="max-w-[70%] {{ $isOwn ? 'text-right' : '' }}">
                            <div class="{{ $isOwn ? 'bg-honey text-ink' : 'bg-comb/50 text-ink dark:bg-zinc-800 dark:text-zinc-100' }}
                                        rounded-2xl px-4 py-2 {{ $isOwn ? 'rounded-tr-sm' : 'rounded-tl-sm' }}">
                                <p class="text-sm">{{ $message->body }}</p>
                            </div>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $message->created_at->format('g:i a') }}
                                @if ($isOwn && $message->read_at)
                                    <span class="ml-1 text-leaf dark:text-emerald-300">✓✓</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <form wire:submit="sendMessage" class="border-t border-comb/70 p-4 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <input type="text"
                       wire:model="newMessage"
                       placeholder="Type a message..."
                       class="flex-1 rounded-lg border border-comb bg-paper/60 px-4 py-2 text-sm placeholder:text-zinc-400 focus:border-honey-deep focus:outline-none focus:ring-2 focus:ring-honey/40 dark:border-zinc-700 dark:bg-zinc-950 dark:placeholder:text-zinc-500"
                       wire:keydown.enter="sendMessage">

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-paper transition hover:bg-honey hover:text-ink disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-honey dark:hover:text-ink">
                    Send
                </button>
            </div>
        </form>
    </div>
</div>