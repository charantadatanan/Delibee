<?php

namespace Database\Seeders;

use App\Enums\BusinessStatus;
use App\Enums\OrderStatus;
use App\Enums\PointsReason;
use App\Enums\ProductStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusEvent;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo data so the marketplace, the BeeRank and the reward ledger can be
 * exercised without waiting for real trading.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Hive Admin',
            'email' => 'admin@delibee.test',
        ]);
        $admin->assignRole('admin');

        $customer = User::factory()->create([
            'name' => 'Cora Customer',
            'email' => 'customer@delibee.test',
        ]);
        $customer->assignRole('customer');

        $categories = Category::orderBy('position')->get();

        $established = $this->business(
            'Sunrise Orchard',
            'Fresh fruit and cold-pressed juice from our own 12-hectare orchard.',
            $categories,
            12,
            5,
            26,
        );

        $rising = $this->business(
            'Little Hive Organics',
            'Weekly vegetable boxes from a family farm north of the city.',
            $categories,
            9,
            5,
            2,
        );

        $struggling = $this->business(
            'QuickBite Corner',
            'Street food snacks, cooked to order.',
            $categories,
            7,
            2,
            14,
            2,
        );

        $tooSmall = $this->business(
            'Just Opened',
            'We are new here and still setting up our shelves.',
            $categories,
            2,
            5,
            1,
        );

        $this->business(
            'Mango Street Foods',
            'Filipino street food, now available for delivery.',
            $categories,
            0,
            5,
            0,
            0,
            BusinessStatus::Pending,
        );

        $this->trade($established, $customer, 12, 5, 0);
        $this->trade($rising, $customer, 9, 5, 0);
        $this->trade($struggling, $customer, 7, 2, 2);
        $this->trade($tooSmall, $customer, 2, 5, 0);

        // One welcome bonus per customer, not once per demo business.
        app(PointsService::class)->award(
            $customer,
            (int) config('commerce.welcome_bonus_points'),
            PointsReason::WelcomeBonus,
            User::class,
            $customer->id,
        );

        $this->command?->info('Seeded demo marketplace with 4 active businesses and 1 pending.');
    }

    /**
     * @param  Collection<int, Category>  $categories
     */
    private function business(
        string $name,
        string $description,
        Collection $categories,
        int $productCount,
        int $rating,
        int $monthsOld,
        int $cancelled = 0,
        BusinessStatus $status = BusinessStatus::Active,
    ): Business {
        $owner = User::factory()->create(['name' => $name.' Owner']);
        $owner->assignRole('seller');

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'currency' => 'PHP',
            'status' => $status,
            'created_at' => now()->subMonths($monthsOld),
            'updated_at' => now()->subMonths($monthsOld),
        ]);

        $catalogue = [
            ['Fresh Apples', 'Crisp, recently picked apples.', 18500],
            ['Golden Mangoes', 'Sweetest mangoes of the season.', 14900],
            ['Free Range Eggs', 'A dozen eggs from free range hens.', 22000],
            ['Wild Honey', 'Raw honey from our own hives.', 32000],
            ['Sourdough Loaf', 'Baked fresh every morning.', 16500],
            ['Cold Brew Coffee', 'Slow steeped for 18 hours.', 12000],
        ];

        foreach (array_slice($catalogue, 0, $productCount) as $index => [$productName, $productDescription, $price]) {
            $category = $categories->get($index % max($categories->count(), 1)) ?? Category::firstOrFail();

            Product::create([
                'business_id' => $business->id,
                'category_id' => $category->id,
                'name' => $productName,
                'slug' => Str::slug($productName).'-'.Str::lower(Str::random(4)),
                'description' => $productDescription,
                'price_minor' => $price,
                'currency' => 'PHP',
                'stock' => 50,
                'status' => ProductStatus::Active,
            ]);
        }

        return $business;
    }

    /**
     * Create the orders for a business and, for the completed ones, the
     * verified-purchase reviews that feed its rating.
     */
    private function trade(Business $business, User $customer, int $orders, int $rating, int $cancelled): void
    {
        $products = $business->products()->get();

        if ($products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $orders; $i++) {
            $isCancelled = $i < $cancelled;
            // Spread the orders evenly across roughly two months so both the
            // rolling 30-day window and the previous calendar month have
            // enough trading to be ranked.
            $orderedAt = now()
                ->subDays((int) floor(55 * $i / max($orders, 1)) + 1)
                ->subHours(random_int(0, 20));

            $product = $products->random();
            $quantity = random_int(1, 3);
            $subtotal = $product->price_minor * $quantity;

            $order = Order::create([
                'number' => 'DB-'.Str::upper(Str::random(10)),
                'business_id' => $business->id,
                'user_id' => $customer->id,
                'status' => OrderStatus::Pending,
                'currency' => 'PHP',
                'subtotal_minor' => $subtotal,
                'shipping_minor' => 5000,
                'discount_minor' => 0,
                'total_minor' => $subtotal + 5000,
                'shipping_address' => [
                    'name' => $customer->name,
                    'line1' => '12 Market Street',
                    'city' => 'Quezon City',
                    'postal_code' => '1100',
                ],
                'ordered_at' => $orderedAt,
            ]);

            $item = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_minor' => $product->price_minor,
                'quantity' => $quantity,
                'subtotal_minor' => $subtotal,
            ]);

            OrderStatusEvent::create([
                'order_id' => $order->id,
                'status' => OrderStatus::Pending,
                'note' => 'Order placed.',
                'created_by' => $customer->id,
                'created_at' => $orderedAt,
            ]);

            if ($isCancelled) {
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'cancelled_at' => $orderedAt->copy()->addDay(),
                ]);

                OrderStatusEvent::create([
                    'order_id' => $order->id,
                    'status' => OrderStatus::Cancelled,
                    'note' => 'Cancelled at the customer\'s request.',
                    'created_at' => $orderedAt->copy()->addDay(),
                ]);

                continue;
            }

            $shippedAt = $orderedAt->copy()->addDay();
            $deliveredAt = $shippedAt->copy()->addDays(random_int(1, 4));

            // Recent orders cannot have been delivered in the future yet.
            if ($shippedAt->isFuture()) {
                $shippedAt = now()->subHours(6);
            }

            if ($deliveredAt->isFuture()) {
                $deliveredAt = now()->subHours(2);
            }

            $order->update([
                'status' => OrderStatus::Delivered,
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
            ]);

            OrderStatusEvent::create([
                'order_id' => $order->id,
                'status' => OrderStatus::Shipped,
                'note' => 'Handed over to the courier.',
                'created_at' => $shippedAt,
            ]);

            OrderStatusEvent::create([
                'order_id' => $order->id,
                'status' => OrderStatus::Delivered,
                'note' => 'Delivered.',
                'created_at' => $deliveredAt,
            ]);

            Review::create([
                'user_id' => $customer->id,
                'business_id' => $business->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'rating' => $rating,
                'title' => 'Great quality',
                'body' => 'Arrived fresh and well packed. Would order again.',
                'status' => 'published',
                'created_at' => $deliveredAt->copy()->addDay(),
            ]);
        }

        if ($orders > 0) {
            app(PointsService::class)->award(
                $customer,
                100,
                PointsReason::WelcomeBonus,
                User::class,
                $customer->id,
            );
        }
    }
}
