<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * The Honey Pot. A cart may hold items from many businesses; checkout is
 * what splits it into one order per seller.
 */
class CartService
{
    /**
     * @return Collection<int, CartItem>
     */
    public function items(User $user): Collection
    {
        return CartItem::query()
            ->with(['product.business', 'product.images'])
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Cart items grouped by the seller that will fulfil them.
     *
     * @return SupportCollection<int, Collection<int, CartItem>>
     */
    public function groups(User $user): SupportCollection
    {
        /** @var SupportCollection<int, Collection<int, CartItem>> $groups */
        $groups = new SupportCollection;

        foreach ($this->items($user) as $item) {
            $businessId = (int) $item->product->business_id;

            $group = $groups->get($businessId) ?? new Collection;
            $group->push($item);

            $groups->put($businessId, $group);
        }

        return $groups;
    }

    public function add(User $user, Product $product, int $quantity = 1): CartItem
    {
        if (! $product->isInStock()) {
            throw new \RuntimeException('This product is out of stock.');
        }

        $quantity = max(1, $quantity);

        $item = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $quantity]);
        } else {
            $item = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return $item->fresh(['product.business']);
    }

    public function updateQuantity(User $user, CartItem $item, int $quantity): void
    {
        $this->assertOwnedBy($user, $item);

        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $stock = (int) $item->product->stock;

        if ($stock <= 0) {
            $item->delete();

            return;
        }

        // Never let the cart hold more than the seller can fulfil, even if the
        // client-side max attribute is bypassed.
        $item->update(['quantity' => min($quantity, $stock)]);
    }

    public function remove(User $user, CartItem $item): void
    {
        $this->assertOwnedBy($user, $item);

        $item->delete();
    }

    public function clear(User $user): void
    {
        CartItem::query()->where('user_id', $user->id)->delete();
    }

    public function count(User $user): int
    {
        return (int) CartItem::query()->where('user_id', $user->id)->sum('quantity');
    }

    public function subtotalMinor(User $user): int
    {
        $total = 0;

        foreach ($this->items($user) as $item) {
            $total += $item->subtotalMinor();
        }

        return $total;
    }

    /**
     * Minor-unit subtotals keyed by currency, because a cart can span sellers
     * in different currencies.
     *
     * @return array<string, int>
     */
    public function subtotalsByCurrency(User $user): array
    {
        $totals = [];

        foreach ($this->items($user) as $item) {
            $currency = $item->product->currency;

            $totals[$currency] = ($totals[$currency] ?? 0) + $item->subtotalMinor();
        }

        ksort($totals);

        return $totals;
    }

    /**
     * How many units of a product the customer already has in the cart, so
     * the product page can cap the quantity it will accept.
     */
    public function quantityOf(User $user, Product $product): int
    {
        return (int) CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->value('quantity');
    }

    private function assertOwnedBy(User $user, CartItem $item): void
    {
        if ($item->user_id !== $user->id) {
            throw new \RuntimeException('This cart item belongs to someone else.');
        }
    }
}
