<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Only the customer and the seller the conversation belongs to can read
     * it, plus admins handling support.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin()
            || $conversation->user_id === $user->id
            || $conversation->business?->owner_id === $user->id;
    }

    public function reply(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
