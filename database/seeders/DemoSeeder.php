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
use App\Models\PointsLedgerEntry;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\PointsService;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo data so the marketplace, the BeeRank and the reward ledger can be
 * exercised without waiting for real trading.
 *
 * This seeder is additive and idempotent. It is safe to run on top of an
 * environment that was seeded by an older version:
 *
 *  - users, businesses, products and images are created only when missing;
 *  - existing rows are never rewritten or deleted;
 *  - a full trading history is only created for a shop with no orders at all;
 *  - shops that already trade only receive the orders needed to reach the
 *    BeeRank minimum in both ranking windows, under deterministic numbers;
 *  - every points entry is keyed by its reference and skipped when present.
 */
class DemoSeeder extends Seeder
{
    /**
     * Shared shop catalogue. Each business gets a prefix of this list.
     *
     * @var array<int, array{name: string, category: string, description: string, price: int, stock: int}>
     */
    private const BASE_PRODUCTS = [
        [
            'name' => 'Fresh Apples',
            'category' => 'fresh-produce',
            'description' => 'Crisp, recently picked apples.',
            'price' => 18500,
            'stock' => 48,
        ],
        [
            'name' => 'Golden Mangoes',
            'category' => 'fresh-produce',
            'description' => 'Sweetest mangoes of the season.',
            'price' => 14900,
            'stock' => 36,
        ],
        [
            'name' => 'Free Range Eggs',
            'category' => 'dairy-eggs',
            'description' => 'A dozen eggs from free range hens.',
            'price' => 22000,
            'stock' => 42,
        ],
        [
            'name' => 'Wild Honey',
            'category' => 'pantry',
            'description' => 'Raw honey from our own hives.',
            'price' => 32000,
            'stock' => 27,
        ],
        [
            'name' => 'Sourdough Loaf',
            'category' => 'bakery',
            'description' => 'Baked fresh every morning.',
            'price' => 16500,
            'stock' => 25,
        ],
        [
            'name' => 'Cold Brew Coffee',
            'category' => 'drinks',
            'description' => 'Slow steeped for 18 hours.',
            'price' => 12000,
            'stock' => 60,
        ],
    ];

    /**
     * The extra produce that turns the demo catalogue into a full shelf.
     *
     * @var array<string, array<int, array{name: string, category: string, description: string, price: int, stock: int}>>
     */
    private const EXTRA_PRODUCTS = [
        'sunrise-orchard' => [
            [
                'name' => 'Honeycrisp Apples',
                'category' => 'fresh-produce',
                'description' => 'Premium apples with a honeyed crunch.',
                'price' => 22500,
                'stock' => 45,
            ],
            [
                'name' => 'Orchard Honey',
                'category' => 'pantry',
                'description' => 'Small-batch honey from our orchard hives.',
                'price' => 34500,
                'stock' => 28,
            ],
            [
                'name' => 'Cold Brew Bottle',
                'category' => 'drinks',
                'description' => 'A one-litre bottle of our slow-steeped cold brew.',
                'price' => 14500,
                'stock' => 36,
            ],
        ],
        'little-hive-organics' => [
            [
                'name' => 'Pasture Duck Eggs',
                'category' => 'dairy-eggs',
                'description' => 'Rich duck eggs from pasture-raised hens.',
                'price' => 26000,
                'stock' => 40,
            ],
            [
                'name' => 'Seeded Sourdough',
                'category' => 'bakery',
                'description' => 'A seeded loaf with a deep, nutty crust.',
                'price' => 18500,
                'stock' => 25,
            ],
            [
                'name' => 'Wildflower Honey',
                'category' => 'pantry',
                'description' => 'Light wildflower honey from the family farm.',
                'price' => 29500,
                'stock' => 32,
            ],
        ],
        'quickbite-corner' => [
            [
                'name' => 'Iced Coffee',
                'category' => 'drinks',
                'description' => 'Sweet iced coffee, brewed to order.',
                'price' => 9500,
                'stock' => 90,
            ],
            [
                'name' => 'Honey Toast',
                'category' => 'bakery',
                'description' => 'Thick-cut toast with butter and honey.',
                'price' => 8500,
                'stock' => 60,
            ],
            [
                'name' => 'Egg Sandwich',
                'category' => 'bakery',
                'description' => 'A hot egg sandwich on a soft roll.',
                'price' => 7500,
                'stock' => 55,
            ],
        ],
        'just-opened' => [
            [
                'name' => 'Carabao Mangoes',
                'category' => 'fresh-produce',
                'description' => 'Sweet, fibre-free Carabao mangoes.',
                'price' => 16500,
                'stock' => 30,
            ],
            [
                'name' => 'Sourdough Starter',
                'category' => 'homegrown',
                'description' => 'A jar of active starter for your own bake.',
                'price' => 12000,
                'stock' => 15,
            ],
            [
                'name' => 'Wild Honey Sticks',
                'category' => 'homegrown',
                'description' => 'Single-serve honey sticks for lunchboxes.',
                'price' => 6500,
                'stock' => 70,
            ],
        ],
    ];

    /**
     * How much trading each shop gets when its history is empty, and the
     * rating its verified reviews carry.
     *
     * @var array<string, array{rating: int, cancelled: int, previous_extra: int, recent_extra: int}>
     */
    private const TRADE_PLANS = [
        'sunrise-orchard' => ['rating' => 5, 'cancelled' => 0, 'previous_extra' => 2, 'recent_extra' => 5],
        'little-hive-organics' => ['rating' => 5, 'cancelled' => 0, 'previous_extra' => 1, 'recent_extra' => 2],
        'quickbite-corner' => ['rating' => 2, 'cancelled' => 2, 'previous_extra' => 1, 'recent_extra' => 2],
        'just-opened' => ['rating' => 5, 'cancelled' => 0, 'previous_extra' => 0, 'recent_extra' => 2],
    ];

    public function run(): void
    {
        $this->demoUser('Hive Admin', 'admin@delibee.test', 'admin');
        $customer = $this->demoUser('Cora Customer', 'customer@delibee.test', 'customer');

        $categories = Category::query()->get()->keyBy('slug');

        $established = $this->business(
            'Sunrise Orchard',
            'Fresh fruit and cold-pressed juice from our own 12-hectare orchard.',
            12,
        );

        $rising = $this->business(
            'Little Hive Organics',
            'Weekly vegetable boxes from a family farm north of the city.',
            9,
        );

        $struggling = $this->business(
            'QuickBite Corner',
            'Street food snacks, cooked to order.',
            7,
        );

        $tooSmall = $this->business(
            'Just Opened',
            'We are new here and still setting up our shelves.',
            2,
        );

        $this->business(
            'Mango Street Foods',
            'Filipino street food, now available for delivery.',
            0,
            BusinessStatus::Pending,
        );

        $this->seedCatalogue($established, 'sunrise-orchard', 6, $categories);
        $this->seedCatalogue($rising, 'little-hive-organics', 6, $categories);
        $this->seedCatalogue($struggling, 'quickbite-corner', 6, $categories);
        $this->seedCatalogue($tooSmall, 'just-opened', 2, $categories);

        $this->trade($established, $customer, 'sunrise-orchard');
        $this->trade($rising, $customer, 'little-hive-organics');
        $this->trade($struggling, $customer, 'quickbite-corner');
        $this->trade($tooSmall, $customer, 'just-opened');

        $this->seedHoneyPoints($customer);

        $this->call(DemoPhotoSeeder::class);

        $this->command?->info(
            'Seeded demo marketplace: 4 active businesses, 1 pending, 32 products, '
            .'a full BeeRank window and a varied Honey Points ledger.'
        );
    }

    /**
     * Create a demo user without the dev-only model factory, so this seeder
     * also runs in a production build that omits Faker.
     */
    private function demoUser(string $name, string $email, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->forceFill([
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole($role);
        $user->profile()->firstOrCreate([]);

        return $user;
    }

    /**
     * Return the demo business, creating it only when it does not exist.
     * Existing shops keep their owner, status and age untouched.
     */
    private function business(
        string $name,
        string $description,
        int $monthsOld,
        BusinessStatus $status = BusinessStatus::Active,
    ): Business {
        $slug = Str::slug($name);
        $existing = Business::query()->where('slug', $slug)->first();

        if ($existing !== null) {
            // The owner still needs their demo role/profile on a fresh login.
            $owner = $this->demoUser($name.' Owner', $slug.'@delibee.test', 'seller');
            $existing->setRelation('owner', $owner);

            return $existing;
        }

        $owner = $this->demoUser($name.' Owner', $slug.'@delibee.test', 'seller');

        $business = new Business([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'currency' => 'PHP',
            'status' => $status,
        ]);

        // created_at is not fillable, and "how old is this shop?" is a real
        // BeeRank input (Rising Bees), so it is forced on first insert.
        $business->forceFill([
            'created_at' => now()->subMonths($monthsOld),
            'updated_at' => now()->subMonths($monthsOld),
        ])->save();

        return $business;
    }

    /**
     * Add any missing products. Existing products (including the random-slug
     * rows written by the old seeder) are left exactly as they are.
     *
     * @param  Collection<string, Category>  $categories
     */
    private function seedCatalogue(Business $business, string $key, int $baseCount, Collection $categories): void
    {
        $entries = array_merge(
            array_slice(self::BASE_PRODUCTS, 0, $baseCount),
            self::EXTRA_PRODUCTS[$key] ?? [],
        );

        foreach ($entries as $entry) {
            $slug = Str::slug($entry['name']);

            $product = Product::query()
                ->where('business_id', $business->id)
                ->where(function ($query) use ($entry, $slug): void {
                    $query->where('name', $entry['name'])->orWhere('slug', $slug);
                })
                ->first();

            $category = $categories->get($entry['category']) ?? Category::query()->firstOrFail();

            if ($product !== null) {
                // Repair the category labels written by the old cycling seeder
                // without touching price, stock, description or slug.
                if ($product->category_id !== $category->id) {
                    $product->update(['category_id' => $category->id]);
                }

                continue;
            }

            Product::create([
                'business_id' => $business->id,
                'category_id' => $category->id,
                'name' => $entry['name'],
                'slug' => $slug,
                'description' => $entry['description'],
                'price_minor' => $entry['price'],
                'currency' => 'PHP',
                'stock' => $entry['stock'],
                'status' => ProductStatus::Active,
            ]);
        }
    }

    /**
     * Give an empty shop its full demo history, then make sure every active
     * shop clears the BeeRank minimum in both relevant windows. A shop that
     * already trades is only topped up, never rebuilt.
     */
    private function trade(Business $business, User $customer, string $key): void
    {
        if ($business->status !== BusinessStatus::Active) {
            return;
        }

        if (! $business->orders()->exists()) {
            $this->seedTradingHistory($business, $customer, $key);
        }

        $this->ensureRankingWindow($business, $customer, $key);
    }

    private function seedTradingHistory(Business $business, User $customer, string $key): void
    {
        $plan = self::TRADE_PLANS[$key];
        $products = $this->activeProducts($business);

        if ($products->isEmpty()) {
            return;
        }

        foreach ($this->orderDates($plan['previous_extra'], $plan['recent_extra']) as $index => $orderedAt) {
            $product = $products->values()->get($index % $products->count());

            if ($product === null) {
                continue;
            }

            $quantity = [1, 2, 3][$index % 3];
            $number = 'DB-'.Str::upper(Str::slug($business->slug, ''))
                .'-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            $this->createOrder(
                $business,
                $customer,
                $number,
                $orderedAt,
                $product,
                $quantity,
                $index < $plan['cancelled'],
                $plan['rating'],
            );
        }
    }

    /**
     * Add only the orders needed to reach the BeeRank minimum inside the
     * previous calendar month (the period `bee:rank --period=month` publishes)
     * and inside the rolling 30-day window.
     *
     * Orders placed in the overlap of the two windows satisfy both. The
     * deterministic number includes the published period, so a second run in
     * the same month cannot duplicate them; once the counts are met this
     * method returns without writing anything.
     */
    private function ensureRankingWindow(Business $business, User $customer, string $key): void
    {
        $plan = self::TRADE_PLANS[$key];
        $products = $this->activeProducts($business);

        if ($products->isEmpty()) {
            return;
        }

        $minimum = (int) config('beerank.minimum_orders');
        $previousStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $previousStart->copy()->endOfMonth();
        $rollingStart = now()->subDays((int) config('beerank.window_days'));

        $previousCount = $business->orders()
            ->whereBetween('ordered_at', [$previousStart, $previousEnd])
            ->count();
        $rollingCount = $business->orders()
            ->whereBetween('ordered_at', [$rollingStart, now()])
            ->count();

        $neededPrevious = max(0, $minimum - $previousCount);
        $neededRolling = max(0, $minimum - $rollingCount);

        if ($neededPrevious === 0 && $neededRolling === 0) {
            return;
        }

        $dates = [];

        $overlapStart = $rollingStart->greaterThan($previousStart)
            ? $rollingStart->copy()
            : $previousStart->copy();
        $overlapEnd = $previousEnd->greaterThan(now()) ? now()->copy() : $previousEnd->copy();

        $shared = 0;

        if ($overlapStart->lessThan($overlapEnd)) {
            $shared = min($neededPrevious, $neededRolling);

            if ($shared > 0) {
                $dates = array_merge($dates, $this->spread(
                    $overlapStart->copy()->addMinutes(30),
                    $overlapEnd->copy()->subMinutes(30),
                    $shared,
                ));
            }
        }

        $remainingPrevious = $neededPrevious - $shared;

        if ($remainingPrevious > 0) {
            $start = $previousStart->copy()->addHours(12);
            $end = $overlapStart->copy()->subHour();

            if ($end->lessThanOrEqualTo($start)) {
                $start = $previousStart->copy()->addHour();
                $end = $previousStart->copy()->addHours(23);
            }

            $dates = array_merge($dates, $this->spread($start, $end, $remainingPrevious));
        }

        $remainingRolling = $neededRolling - $shared;

        if ($remainingRolling > 0) {
            $start = $rollingStart->copy()->addHours(12);
            $afterPreviousMonth = $previousEnd->copy()->addHour();

            if ($afterPreviousMonth->greaterThan($start) && $afterPreviousMonth->lessThan(now())) {
                $start = $afterPreviousMonth;
            }

            $end = now()->copy()->subHour();

            if ($end->lessThanOrEqualTo($start)) {
                $start = $rollingStart->copy();
                $end = now()->copy();
            }

            $dates = array_merge($dates, $this->spread($start, $end, $remainingRolling));
        }

        usort($dates, fn (CarbonInterface $a, CarbonInterface $b): int => $a->getTimestamp() <=> $b->getTimestamp());

        $period = $previousStart->format('Ym');
        $sequence = 0;

        foreach ($dates as $orderedAt) {
            $sequence++;
            $product = $products->values()->get(($sequence - 1) % $products->count());

            if ($product === null) {
                continue;
            }

            $quantity = [1, 2, 3][($sequence - 1) % 3];
            $number = 'DB-TOPUP-'.Str::upper(Str::slug($business->slug, ''))
                .'-'.$period.'-'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);

            $this->createOrder(
                $business,
                $customer,
                $number,
                $orderedAt,
                $product,
                $quantity,
                false,
                $plan['rating'],
            );
        }
    }

    /**
     * @return Collection<int, Product>
     */
    private function activeProducts(Business $business): Collection
    {
        return $business->products()
            ->where('status', ProductStatus::Active)
            ->orderBy('id')
            ->get();
    }

    private function createOrder(
        Business $business,
        User $customer,
        string $number,
        CarbonInterface $orderedAt,
        Product $product,
        int $quantity,
        bool $isCancelled,
        int $rating,
    ): Order {
        $subtotal = $product->price_minor * $quantity;

        [$shippedAt, $deliveredAt, $cancelledAt] = $this->fulfilment($orderedAt, $isCancelled);

        $order = Order::firstOrCreate(
            ['number' => $number],
            [
                'business_id' => $business->id,
                'user_id' => $customer->id,
                'status' => $isCancelled ? OrderStatus::Cancelled : OrderStatus::Delivered,
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
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'cancelled_at' => $cancelledAt,
            ],
        );

        $item = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'product_name' => $product->name,
                'unit_price_minor' => $product->price_minor,
                'quantity' => $quantity,
                'subtotal_minor' => $subtotal,
            ],
        );

        $this->statusEvent($order, OrderStatus::Pending, 'Order placed.', $customer->id, $orderedAt);

        if ($isCancelled) {
            $this->statusEvent(
                $order,
                OrderStatus::Cancelled,
                'Cancelled at the customer\'s request.',
                null,
                $cancelledAt ?? $orderedAt,
            );

            return $order;
        }

        $this->statusEvent(
            $order,
            OrderStatus::Shipped,
            'Handed over to the courier.',
            null,
            $shippedAt ?? $orderedAt,
        );

        $this->statusEvent(
            $order,
            OrderStatus::Delivered,
            'Delivered.',
            null,
            $deliveredAt ?? $orderedAt,
        );

        $this->review($order, $item, $business, $customer, $product, $rating, $deliveredAt ?? $orderedAt);

        return $order;
    }

    /**
     * Place orders in the overlap of the previous calendar month and the
     * rolling BeeRank window, then spread extras on either side. Every active
     * shop therefore has at least five orders in both windows, even when the
     * seeder runs on the first or last day of a month.
     *
     * @return list<CarbonInterface>
     */
    private function orderDates(int $previousExtra, int $recentExtra): array
    {
        $previousStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $previousStart->copy()->endOfMonth();
        $rollingStart = now()->subDays((int) config('beerank.window_days'));

        $overlapStart = $rollingStart->greaterThan($previousStart)
            ? $rollingStart->copy()
            : $previousStart->copy();
        $overlapEnd = $previousEnd->copy();
        $hasOverlap = $overlapStart->lessThan($overlapEnd);

        $dates = $hasOverlap
            ? $this->spread($overlapStart->copy()->addMinutes(30), $overlapEnd->copy()->subMinutes(30), 5)
            : $this->spread($previousStart->copy()->addHours(12), $previousEnd->copy()->subHours(12), 5);

        if ($previousExtra > 0) {
            $start = $previousStart->copy()->addHours(12);
            $end = $hasOverlap
                ? $overlapStart->copy()->subHour()
                : $previousEnd->copy()->subHours(12);

            if ($end->lessThanOrEqualTo($start)) {
                $start = $previousStart->copy()->addHour();
                $end = $previousStart->copy()->addHours(23);
            }

            $dates = array_merge($dates, $this->spread($start, $end, $previousExtra));
        }

        if ($recentExtra > 0) {
            $dates = array_merge(
                $dates,
                $this->spread(
                    now()->subDays(2 + 3 * ($recentExtra - 1)),
                    now()->subHours(2),
                    $recentExtra,
                ),
            );
        }

        // With no overlap (running on the last day or two of a month) the
        // rolling window needs its own recent orders.
        if (! $hasOverlap) {
            $dates = array_merge($dates, $this->spread(now()->subDays(18), now()->subHours(3), 5));
        }

        usort($dates, fn (CarbonInterface $a, CarbonInterface $b): int => $a->getTimestamp() <=> $b->getTimestamp());

        return $dates;
    }

    /**
     * @return list<CarbonInterface>
     */
    private function spread(CarbonInterface $start, CarbonInterface $end, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $span = (int) $start->diffInSeconds($end);
        $dates = [];

        for ($i = 0; $i < $count; $i++) {
            $offset = $count === 1
                ? intdiv($span, 2)
                : (int) round($span * $i / ($count - 1));

            $dates[] = $start->copy()->addSeconds($offset);
        }

        return $dates;
    }

    /**
     * @return array{0: CarbonInterface|null, 1: CarbonInterface|null, 2: CarbonInterface|null}
     */
    private function fulfilment(CarbonInterface $orderedAt, bool $isCancelled): array
    {
        if ($isCancelled) {
            return [null, null, $orderedAt->copy()->addDay()];
        }

        $shippedAt = $orderedAt->copy()->addDay();
        $deliveredAt = $orderedAt->copy()->addDays(3);

        // Recent orders cannot have been delivered in the future yet.
        if ($shippedAt->isFuture()) {
            $shippedAt = now()->subHours(6);
        }

        if ($deliveredAt->isFuture()) {
            $deliveredAt = now()->subHours(2);
        }

        if ($deliveredAt->lessThanOrEqualTo($shippedAt)) {
            $deliveredAt = $shippedAt->copy()->addHours(2);
        }

        return [$shippedAt, $deliveredAt, null];
    }

    private function statusEvent(
        Order $order,
        OrderStatus $status,
        string $note,
        ?int $actorId,
        CarbonInterface $at,
    ): void {
        $event = OrderStatusEvent::firstOrCreate(
            ['order_id' => $order->id, 'status' => $status->value],
            ['note' => $note, 'created_by' => $actorId],
        );

        if ($event->wasRecentlyCreated) {
            $event->forceFill(['created_at' => $at])->saveQuietly();
        }
    }

    private function review(
        Order $order,
        OrderItem $item,
        Business $business,
        User $customer,
        Product $product,
        int $rating,
        CarbonInterface $deliveredAt,
    ): void {
        [$title, $body] = $rating >= 4
            ? ['Great quality', 'Arrived fresh and well packed. Would order again.']
            : ['Not as fresh as expected', 'The order arrived, but the produce was past its best.'];

        $review = Review::firstOrCreate(
            ['user_id' => $customer->id, 'order_item_id' => $item->id],
            [
                'business_id' => $business->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'rating' => $rating,
                'title' => $title,
                'body' => $body,
                'status' => 'published',
            ],
        );

        if ($review->wasRecentlyCreated) {
            $reviewedAt = $deliveredAt->copy()->addDay();

            if ($reviewedAt->isFuture()) {
                $reviewedAt = now()->subMinutes(5);
            }

            $review->forceFill(['created_at' => $reviewedAt])->saveQuietly();
        }
    }

    /**
     * Build a varied ledger on top of whatever is already there: purchase and
     * review rewards tied to real rows, one refunded order and one redemption.
     *
     * The welcome bonus is guarded by reason rather than reference, so the
     * five rows written by the old seeder are recognised and never repeated.
     * Every other entry is keyed by its order/review reference.
     */
    private function seedHoneyPoints(User $customer): void
    {
        if (! $this->hasLedgerReason($customer, PointsReason::WelcomeBonus)) {
            $this->awardOnce(
                $customer,
                (int) config('commerce.welcome_bonus_points'),
                PointsReason::WelcomeBonus,
                User::class,
                $customer->id,
                $customer->created_at,
            );
        }

        $businessIds = Business::query()->approved()->pluck('id');

        $purchaseOrders = Order::query()
            ->whereIn('business_id', $businessIds)
            ->where('user_id', $customer->id)
            ->where('status', OrderStatus::Delivered)
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        foreach ($purchaseOrders as $order) {
            $this->awardOnce(
                $customer,
                $this->pointsFor($order),
                PointsReason::Purchase,
                Order::class,
                $order->id,
                $order->delivered_at ?? $order->ordered_at,
            );

            $review = $order->reviews()->where('user_id', $customer->id)->first();

            if ($review !== null) {
                $this->awardOnce(
                    $customer,
                    (int) config('commerce.review_reward_points'),
                    PointsReason::ReviewReward,
                    Review::class,
                    $review->id,
                    $review->created_at?->copy()->addMinutes(5) ?? $order->delivered_at,
                );
            }
        }

        $refunded = Order::query()
            ->whereIn('business_id', $businessIds)
            ->where('user_id', $customer->id)
            ->where('status', OrderStatus::Cancelled)
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->first();

        if ($refunded !== null) {
            $refundPoints = $this->pointsFor($refunded);

            $this->awardOnce(
                $customer,
                $refundPoints,
                PointsReason::Purchase,
                Order::class,
                $refunded->id,
                $refunded->ordered_at,
            );

            $this->reverseOnce(
                $customer,
                $refundPoints,
                PointsReason::Refund,
                Order::class,
                $refunded->id,
                $refunded->cancelled_at?->copy()->addDay() ?? $refunded->ordered_at,
            );
        }

        $this->redeemDemoReward($customer);
    }

    /**
     * Redeem once, leaving a Courtier-sized balance with room to climb. On an
     * existing ledger the amount is derived from the current balance, so the
     * old 500-point welcome total is respected rather than duplicated.
     */
    private function redeemDemoReward(User $customer): void
    {
        if ($this->hasLedgerReason($customer, PointsReason::Redemption)) {
            return;
        }

        $balance = app(PointsService::class)->balance($customer);

        if ($balance <= 0) {
            return;
        }

        $redemption = min(
            $balance,
            max(500, (int) (floor(max(0, $balance - 2000) / 100) * 100)),
        );

        $this->backdate(
            app(PointsService::class)->redeem(
                $customer,
                $redemption,
                PointsReason::Redemption,
                'demo_reward',
                1,
            ),
            now()->subMinutes(30),
        );
    }

    private function awardOnce(
        User $user,
        int $points,
        PointsReason $reason,
        ?string $referenceType,
        ?int $referenceId,
        ?CarbonInterface $at = null,
    ): ?PointsLedgerEntry {
        if ($points <= 0 || $this->hasLedgerEntry($user, $reason, $referenceType, $referenceId)) {
            return null;
        }

        return $this->backdate(
            app(PointsService::class)->award($user, $points, $reason, $referenceType, $referenceId),
            $at,
        );
    }

    private function reverseOnce(
        User $user,
        int $points,
        PointsReason $reason,
        ?string $referenceType,
        ?int $referenceId,
        ?CarbonInterface $at = null,
    ): ?PointsLedgerEntry {
        if ($points <= 0 || $this->hasLedgerEntry($user, $reason, $referenceType, $referenceId)) {
            return null;
        }

        return $this->backdate(
            app(PointsService::class)->reverse($user, $points, $reason, $referenceType, $referenceId),
            $at,
        );
    }

    private function hasLedgerReason(User $user, PointsReason $reason): bool
    {
        return $user->pointsLedger()->where('reason', $reason->value)->exists();
    }

    private function hasLedgerEntry(
        User $user,
        PointsReason $reason,
        ?string $referenceType,
        ?int $referenceId,
    ): bool {
        $query = $user->pointsLedger()->where('reason', $reason->value);

        $referenceType === null
            ? $query->whereNull('reference_type')
            : $query->where('reference_type', $referenceType);

        $referenceId === null
            ? $query->whereNull('reference_id')
            : $query->where('reference_id', $referenceId);

        return $query->exists();
    }

    private function backdate(?PointsLedgerEntry $entry, ?CarbonInterface $at): ?PointsLedgerEntry
    {
        if ($entry !== null && $at !== null) {
            $entry->forceFill(['created_at' => $at])->saveQuietly();
        }

        return $entry;
    }

    private function pointsFor(Order $order): int
    {
        $per = (int) config('commerce.points_per_currency_unit', 0);

        return (int) floor(Money::toMajor($order->total_minor, $order->currency) * $per);
    }
}
