<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Business $business): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSeller();
    }

    public function update(User $user, Business $business): bool
    {
        return $user->isAdmin() || $business->owner_id === $user->id;
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->isAdmin();
    }

    /**
     * Approving a business moves it from pending to active, which is what
     * allows its products to appear in the marketplace.
     */
    public function approve(User $user, Business $business): bool
    {
        return $user->isAdmin();
    }

    /**
     * Approving, suspending and reactivating are all admin moderation actions.
     */
    public function updateStatus(User $user, Business $business): bool
    {
        return $user->isAdmin();
    }
}
