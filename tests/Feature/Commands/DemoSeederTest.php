<?php

use App\Enums\BusinessStatus;
use App\Enums\PointsReason;
use App\Models\Business;
use App\Models\Order;
use App\Models\PointsLedgerEntry;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\RankSnapshot;
use App\Models\Review;
use App\Models\User;
use App\Services\PointsService;
use App\Support\BeeTier;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RoleSeeder;

/**
 * A production-safe demo seeder is part of the deploy contract: the Cloud
 * pipeline runs db:seed on every deploy, and the same seeder may be run
 * manually on an existing marketplace. This test proves that a fresh seed is
 * complete and that a second run changes nothing.
 */
it('seeds a complete demo and is a no-op on the second run', function () {
    $this->seed([RoleSeeder::class, CategorySeeder::class]);
    $this->seed(DemoSeeder::class);

    $snapshot = function (): array {
        $customer = User::query()->where('email', 'customer@delibee.test')->firstOrFail();

        return [
            'businesses' => Business::count(),
            'active_businesses' => Business::query()->where('status', BusinessStatus::Active->value)->count(),
            'products' => Product::count(),
            'active_products' => Product::query()->where('status', 'active')->count(),
            'images' => ProductImage::count(),
            'products_without_image' => Product::query()->whereDoesntHave('images')->count(),
            'orders' => Order::count(),
            'reviews' => Review::count(),
            'ledger' => PointsLedgerEntry::count(),
            'balance' => app(PointsService::class)->balance($customer),
            'max_order' => (int) Order::max('id'),
            'max_review' => (int) Review::max('id'),
            'max_ledger' => (int) PointsLedgerEntry::max('id'),
        ];
    };

    $first = $snapshot();

    expect($first['businesses'])->toBe(5)
        ->and($first['active_businesses'])->toBe(4)
        ->and($first['products'])->toBe(32)
        ->and($first['active_products'])->toBe(32)
        ->and($first['images'])->toBe(32)
        ->and($first['products_without_image'])->toBe(0)
        ->and($first['orders'])->toBeGreaterThanOrEqual(35)
        ->and($first['reviews'])->toBeGreaterThanOrEqual(33);

    $previousStart = now()->subMonthNoOverflow()->startOfMonth();
    $previousEnd = $previousStart->copy()->endOfMonth();
    $rollingStart = now()->subDays((int) config('beerank.window_days'));

    foreach (Business::query()->where('status', BusinessStatus::Active->value)->get() as $business) {
        expect($business->orders()->whereBetween('ordered_at', [$previousStart, $previousEnd])->count())
            ->toBeGreaterThanOrEqual(5)
            ->and($business->orders()->whereBetween('ordered_at', [$rollingStart, now()])->count())
            ->toBeGreaterThanOrEqual(5);
    }

    $customer = User::query()->where('email', 'customer@delibee.test')->firstOrFail();
    $byReason = $customer->pointsLedger()
        ->selectRaw('reason, count(*) as aggregate')
        ->groupBy('reason')
        ->pluck('aggregate', 'reason');

    expect($byReason[PointsReason::WelcomeBonus->value])->toBe(1)
        ->and($byReason[PointsReason::Purchase->value])->toBeGreaterThanOrEqual(10)
        ->and($byReason[PointsReason::ReviewReward->value])->toBeGreaterThanOrEqual(10)
        ->and($byReason[PointsReason::Redemption->value])->toBe(1)
        ->and($byReason[PointsReason::Refund->value])->toBe(1)
        ->and(BeeTier::for($first['balance'])['name'])->toBeIn(['Forager', 'Courtier']);

    // A second run must not add a single row.
    $this->seed(DemoSeeder::class);

    expect($snapshot())->toEqual($first);

    // The default period is the previous calendar month; every active shop
    // must have cleared the minimum inside it.
    $this->artisan('bee:rank', ['--period' => 'month'])->assertSuccessful();

    $periodStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
    $snapshots = RankSnapshot::query()
        ->where('period_type', 'month')
        ->whereDate('period_start', $periodStart)
        ->get();

    expect($snapshots)->toHaveCount(4)
        ->and($snapshots->pluck('business_id')->unique())->toHaveCount(4);
});
