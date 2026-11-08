<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * The customer who placed the order, the seller it belongs to, and admins
     * can all see it.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || $order->user_id === $user->id
            || $order->business?->owner_id === $user->id;
    }

    /**
     * Only the seller responsible for fulfilment may move an order along its
     * Bee Flight timeline.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->business?->owner_id === $user->id;
    }

    /**
     * The customer can cancel while the order has not shipped yet; a seller
     * can cancel for their own orders; admins can always cancel.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $order->business?->owner_id === $user->id) {
            return true;
        }

        return $order->user_id === $user->id && $order->status->isCancellable();
    }

    /**
     * A review may only be left once the order has been delivered.
     */
    public function review(User $user, Order $order): bool
    {
        return $order->user_id === $user->id && $order->status === OrderStatus::Delivered;
    }
}
