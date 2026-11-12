<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSeller() || $user->isCustomer();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $review->user_id === $user->id;
    }

    /**
     * Hiding or publishing a review is an admin moderation action.
     */
    public function moderate(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
