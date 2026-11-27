# deliBEE — Order & Checkout + Points System Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Report date:** 27 November 2026
**Scope:** Honey Pot cart, one-order-per-seller checkout, Bee Flight orders, verified reviews, Honey Points and BeeRank snapshots
**Production:** https://delibee-production-6l26tu.laravel.cloud
**Status:** Complete for the MVP checkpoint; payment collection on hold

---

## Progress

**Honey Pot cart.** `app/Services/CartService.php` owns the per-user cart: repeated adds merge into one line, quantities are clamped to live stock, zero removes the line, and ownership is checked before update/remove (`app/Models/CartItem.php`). The page is `resources/views/pages/cart.blade.php`; `subtotalsByCurrency()` keeps multi-currency carts honest rather than adding unlike currencies together.

**One order per seller.** `CartService::groups()` groups lines by `business_id`; `CheckoutService::place()` runs one transaction and creates one `Order` per seller with its own `DB-XXXXXXXXXX` number, subtotal, flat shipping and total (`app/Services/CheckoutService.php`). The cart is cleared only after every seller order succeeds, so a failure rolls the whole checkout back.

**Stock locking.** Inside the transaction, `placeForBusiness()` sorts lines by `product_id`, re-reads each product with `lockForUpdate()`, re-checks stock and decrements it (`app/Services/CheckoutService.php`). Concurrent checkouts cannot oversell; a shortage throws `CheckoutException` and rolls back all orders and stock changes. PostgreSQL also enforces `stock >= 0` (`database/migrations/2026_11_09_000000_add_check_constraints.php`).

**Shipping and currency.** Flat shipping is charged once per seller order in the seller's currency, configured per currency in `config/commerce.php` (PHP 5000; USD/EUR/JPY 500; KRW 5000; default 5000). Orders store the seller's currency and the platform does not convert between currencies. `resources/views/pages/checkout.blade.php` groups subtotal, shipping and total by currency; money formatting lives in `app/Support/Money.php`.

**Order status timeline.** `app/Enums/OrderStatus.php` defines the six-step progression (pending, confirmed, processing, shipped, out for delivery, delivered) plus terminal cancelled/refunded states, the `timestampColumn()` mapping for `shipped_at`/`delivered_at`/`cancelled_at`, and cancellable/terminal helpers. Every change writes an `OrderStatusEvent` (`app/Models/OrderStatusEvent.php`); sellers advance exactly one step and cannot move backwards (`resources/views/pages/seller/orders.blade.php`). The customer timeline renders these events in `resources/views/pages/orders/show.blade.php`.

**Cancellation and stock restore.** `resources/views/pages/orders/show.blade.php::cancel()` authorises through `app/Policies/OrderPolicy.php`, restores each item's stock, sets `cancelled`/`cancelled_at`, writes a status event, and reverses the original purchase award exactly once via `PointsReason::Refund`. `tests/Feature/Domain/DelibeeDomainTest.php` covers stock restore and point reversal; `tests/Feature/Admin/AdminOrdersTest.php` covers admin cancellation.

**Bee Flight order pages.** Routes are `orders.index` and `orders.show` (`routes/web.php`). The list page paginates 15 orders, filters all/processing/shipped/delivered, and shows the post-checkout confirmation (`resources/views/pages/orders/index.blade.php`). The detail page shows the delivery timeline, item snapshots, shipping address, seller card, totals, cancellation and the per-item review form (`resources/views/pages/orders/show.blade.php`).

**Verified reviews.** Only a delivered order can be reviewed (`OrderPolicy::review`), and only its buyer can submit (`resources/views/pages/orders/show.blade.php::leaveReview`). One review per purchased item is enforced by the unique `(user_id, order_item_id)` index (`database/migrations/2026_11_08_000000_add_unique_index_to_reviews_table.php`); submissions are auto-published, rate-limited (10/minute by default), and earn the 25-point reward only on first creation. `Review::isVerifiedPurchase()` is `order_item_id !== null`; BeeRank counts only published, verified reviews inside the rolling window (`app/Services/BeeRankService.php`). `/reviews` lists the customer's reviews with a Verified Buyer badge (`resources/views/pages/reviews.blade.php`).

**Honey Points ledger.** `points_ledger` is append-only: one row per event with `delta`, `reason`, `reference_type` and `reference_id`; the balance is `SUM(delta)` and no balance is stored on the profile (`database/migrations/2026_10_21_001100_create_points_ledger_table.php`, `app/Models/PointsLedgerEntry.php`, `app/Models/User.php::pointsBalance()`). `app/Services/PointsService.php` exposes award/redeem/reverse, all writing new rows. `app/Enums/PointsReason.php` covers welcome bonus, purchase, review reward, redemption, refund reversal and manual adjustment. Current flows write welcome bonus (demo seed; registration is not wired yet), purchase at checkout (1 point per whole currency unit, `config/commerce.php`), review reward (25) and refund reversal on cancellation. `/rewards` shows balance, four tiers (Larva, Forager, Courtier, Queen) and ledger history (`resources/views/pages/rewards.blade.php`, `app/Support/BeeTier.php`).

**BeeRank snapshots.** `app/Models/RankSnapshot.php` stores period type, period start, score, rank and the component metrics that produced it, unique per `(business, period_type, period_start)` (`database/migrations/2026_10_21_001200_create_rank_snapshots_table.php`). `app/Services/RankingService.php::publish()` ranks eligible sellers and replaces the whole period transactionally, so stale ranks cannot survive. `bee:rank --period=month|year` is scheduled monthly on the 1st at 02:15 and yearly on 1 January at 03:30 (`app/Console/Commands/ComputeBeeRank.php`, `routes/console.php`). Scoring is a 30-day rolling window with a 5-order minimum and weights defined in `config/beerank.php`.

**Demo orders and reviews.** `database/seeders/DemoSeeder.php` creates 5 businesses (4 active, 1 pending), 20 products, 4 + 9 + 7 + 2 = **30 orders** and **28 verified-purchase reviews**; the two cancelled QuickBite orders deliberately have no reviews. `DemoPhotoSeeder` attaches one photo per product.

---

## Final output (live, demo data)

Production URL: `https://delibee-production-6l26tu.laravel.cloud`. Public `/up`, `/marketplace`, `/ranking` and `/login` respond HTTP 200; production demo data is **30 orders / 28 reviews** (`docs/reports/database-integration-progress-report-2026-11-27.md`).

| Customer surface | Live output |
|---|---|
| `/cart` (Honey Pot) | Seller-grouped lines, stock-capped quantities, per-currency subtotal |
| `/checkout` | Address form, seller-split item summary, per-currency shipping/totals, order placement |
| `/orders` (Bee Flight) | Paginated order list, status filters, post-checkout confirmation |
| `/orders/{number}` | Six-step timeline, item/price snapshots, address, seller card, cancel and review actions |
| `/rewards` | Point balance, tier progress, append-only ledger history |
| `/reviews` | Published reviews with Verified Buyer badges |

Demo orders are spread across roughly two months so the 30-day ranking window is exercised; the database currently holds three published snapshots (two for September 2026, one for October 2026). Order-page visual evidence: `screens/final-mobile-orders.png`.

---

## Verification

| Verification | Result |
|---|---|
| Order/cart/points/BeeRank suites | **37 tests, 86 assertions, passed** (`tests/Feature/Domain/DelibeeDomainTest.php`, `CartServiceTest.php`, `PointsServiceTest.php`, `BeeRankTest.php`, `CurrencyDisplayTest.php`, `tests/Feature/Admin/AdminOrdersTest.php`) |
| Constraint and command suites | **6 tests, 8 assertions, passed** (`tests/Feature/Domain/DatabaseConstraintsTest.php`, `tests/Feature/Commands/BeeRankCommandTest.php`) |
| Customer page smoke tests | **4 tests, 16 assertions, passed** (`tests/Feature/Http/PageSmokeTest.php`: public storefront, every customer page, seller pages, onboarding/pending) |
| Live authenticated smoke test | `/dashboard`, `/cart`, `/checkout`, `/orders`, `/orders/{number}`, `/rewards`, `/reviews` all HTTP 200 as the demo customer on the running local build |
| Production smoke test | `/up`, `/marketplace`, `/ranking`, `/login` all HTTP 200 |
| Demo row counts | **30 orders / 28 reviews**, all 28 reviews published and verified (12/9/5/2 by seller) |
| Stock and points invariants | Tests confirm multi-seller split + decrement, oversell rollback, cancel restores stock and reverses points once, review reward paid once, per-seller-currency shipping |
| Database guards | 7 CHECK constraints plus the unique review index reject negative stock, invalid ratings and duplicate reviews |

Targeted verification total: **47 tests and 110 assertions passed** on 27 November 2026. Ledger entries exist only through `PointsService` writes; the `/rewards` history renders the same rows.

---

## Open items

1. **Payments are on hold.** `config/commerce.php` sets `payment_gateway` to `offline`; `/checkout` records the order without collecting payment and tells the customer payment will be arranged when the gateway is connected. The gateway and payout model (platform-collect, cash on delivery or split) remain undecided.
2. **Email is unrelated.** The Resend transport work (commit `d6c2f54`, `config/mail.php`, `composer.json`) and the production OTP/password-reset email block recorded in `docs/reports/accounts-authentication-progress-report-2026-11-27.md` do not touch cart, checkout, Bee Flight, reviews, Honey Points or BeeRank. No order/checkout/points behaviour depends on outbound email.
