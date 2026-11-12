<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Hiding or publishing a review is an admin moderation action.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
