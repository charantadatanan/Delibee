<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PointsReason;
use App\Exceptions\CheckoutException;
use App\Models\Business;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns the Honey Pot into orders.
 *
 * The cart can hold items from many businesses, but each seller ships
 * separately, so checkout creates **one order per seller**. BeeRank needs
 * per-seller outcomes, which is the other reason the split happens here
 * rather than at display time.
 */
class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PointsService $points,
    ) {}

    /**
     * Place the order(s) for everything currently in the cart.
     *
     * @param  array<string, mixed>  $shippingAddress
     * @return Collection<int, Order>
     */
    public function place(User $user, array $shippingAddress, ?string $note = null): Collection
    {
        $groups = $this->cart->groups($user);

        if ($groups->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        return DB::transaction(function () use ($user, $groups, $shippingAddress, $note): Collection {
            $orders = new Collection;

            foreach ($groups as $group) {
                $orders->push($this->placeForBusiness($user, $group, $shippingAddress, $note));
            }

            $this->cart->clear($user);

            return $orders;
        });
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @param  array<string, mixed>  $shippingAddress
     */
    private function placeForBusiness(User $user, Collection $items, array $shippingAddress, ?string $note): Order
    {
        $business = $items->first()->product->business;

        $subtotal = 0;
        // Flat shipping is charged once per order, not once per line.
        $shipping = $this->shippingFor($business);

        $order = Order::create([
            'number' => $this->number(),
            'business_id' => $business->id,
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'currency' => $business->currency,
            'subtotal_minor' => 0,
            'shipping_minor' => 0,
            'discount_minor' => 0,
            'total_minor' => 0,
            'shipping_address' => $shippingAddress,
            'customer_note' => $note,
            'ordered_at' => now(),
        ]);

        // Lock product rows in a stable order so concurrent checkouts cannot
        // oversell, and so two carts locking the same products cannot deadlock.
        foreach ($items->sortBy('product_id') as $item) {
            $product = Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if ($product === null) {
                throw new CheckoutException('A product in your cart is no longer available.');
            }

            if (! $product->isInStock()) {
                throw new CheckoutException("{$product->name} is out of stock and was not ordered.");
            }

            if ($item->quantity > $product->stock) {
                throw new CheckoutException("Only {$product->stock} of {$product->name} left in stock.");
            }

            $unitPrice = $product->price_minor;
            $lineTotal = $unitPrice * $item->quantity;

            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_minor' => $unitPrice,
                'quantity' => $item->quantity,
                'subtotal_minor' => $lineTotal,
            ]);

            $product->decrement('stock', $item->quantity);
        }

        $total = $subtotal + $shipping - $order->discount_minor;

        $order->update([
            'subtotal_minor' => $subtotal,
            'shipping_minor' => $shipping,
            'total_minor' => max(0, $total),
        ]);

        $order->statusEvents()->create([
            'status' => OrderStatus::Pending,
            'note' => 'Order placed.',
            'created_by' => $user->id,
        ]);

        $this->points->award(
            $user,
            $this->pointsFor($order),
            PointsReason::Purchase,
            Order::class,
            $order->id,
        );

        return $order->fresh(['items', 'business']);
    }

    /**
     * Flat shipping charged once per seller order.
     */
    private function shippingFor(Business $business): int
    {
        return (int) config('commerce.flat_shipping_minor', 0);
    }

    /**
     * Points earned by an order. Public so a cancellation can reverse the
     * exact amount that was awarded.
     */
    public function pointsFor(Order $order): int
    {
        $per = (int) config('commerce.points_per_currency_unit', 0);
        $major = Money::toMajor($order->total_minor, $order->currency);

        return (int) floor($major * $per);
    }

    private function number(): string
    {
        do {
            $number = 'DB-'.Str::upper(Str::random(10));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
