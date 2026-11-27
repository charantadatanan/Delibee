<?php

use App\Models\Conversation;
use App\Models\Message;
use Flux\Flux;
use Illuminate\Support\Facades\RateLimiter;
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

        $limit = (int) config('chat.rate_limit_per_minute', 20);
        $key = 'chat-send:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            Flux::toast('You are sending messages too quickly. Please wait a moment.', variant: 'danger');

            return;
        }

        RateLimiter::hit($key, 60);

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

<div class="space-y-5">
    @php($isCustomer = $conversation->user_id === auth()->id())
    @php($otherParty = $isCustomer ? $conversation->business : $conversation->user)

    <x-ui.mobile-header :title="$otherParty->name" :back="route('chat.index')" />

    <div class="hidden flex-wrap items-center justify-between gap-4 lg:flex">
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
           class="ui-btn ui-btn-ghost ui-btn-sm"
           wire:navigate>
            Back to conversations
        </a>
    </div>

    <div wire:poll.5s class="ui-card overflow-hidden bg-paper dark:bg-zinc-900">
        <div class="h-[55svh] min-h-[320px] overflow-y-auto p-4 space-y-4" wire:scroll.near.bottom="$refresh">
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
                            <div class="{{ $isOwn ? 'border-ink bg-honey text-ink' : 'border-ink/20 bg-white text-ink dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100' }}
                                        rounded-2xl border px-4 py-2 {{ $isOwn ? 'rounded-tr-sm' : 'rounded-tl-sm' }}">
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

        <form wire:submit="sendMessage" class="border-t border-ink/15 p-3 dark:border-zinc-800 sm:p-4">
            <div class="flex items-center gap-2">
                <input type="text"
                       wire:model="newMessage"
                       placeholder="Type a message..."
                       aria-label="Message"
                       class="ui-field min-h-11 flex-1 py-2.5"
                       wire:keydown.enter="sendMessage">

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="ui-btn ui-btn-sm shrink-0">
                    Send
                </button>
            </div>
        </form>
    </div>
</div>