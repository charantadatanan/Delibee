# deliBEE code map

Where every UI, theme, domain and test file lives — and where to change things.

The app has two UI styles:

- **Public storefront** — class-based Livewire components in `app/Livewire/Storefront` with Blade views in `resources/views/livewire/storefront`, wrapped by route views in `resources/views/storefront`.
- **Signed-in app** — Livewire 4 **single-file pages** in `resources/views/pages`: PHP at the top of the file, Blade underneath.

---

## 1. Theme and visual system

### Start here

| What | File |
|---|---|
| Colors, fonts, radii, dark variant | `resources/css/app.css` — the `@theme` block |
| Honeycomb background pattern | `resources/css/app.css` — `.bg-comb` data URI |
| Global focus ring, selection, heading font | `resources/css/app.css` — bottom `@layer base` blocks |
| Fonts loaded from Bunny | `vite.config.js` — `fonts: [bunny('Inter'), bunny('Fredoka')]` |
| Logo / brand mark | `resources/views/components/app-logo.blade.php` |
| Public site shell (header, nav, footer) | `resources/views/components/storefront/layout.blade.php` |
| Product card | `resources/views/components/storefront/product-card.blade.php` |
| Signed-in app shell | `resources/views/layouts/app.blade.php` |
| App sidebar (all nav items) | `resources/views/layouts/app/sidebar.blade.php` |
| Auth shell | `resources/views/layouts/auth.blade.php` → `layouts/auth/simple.blade.php` |
| `<head>`, fonts, Vite and Flux includes | `resources/views/partials/head.blade.php` |
| Settings page heading | `resources/views/partials/settings-heading.blade.php` |
| Pagination look | `resources/views/vendor/livewire/tailwind.blade.php`, `resources/views/vendor/pagination/tailwind.blade.php` |
| Flux icon override | `resources/views/flux/icon/chevrons-up-down.blade.php` |
| Current screenshots | `docs/screenshots/figma-theme/` |
| Previous theme screenshots | `docs/screenshots/` |
| Previous theme tokens and restore steps | `docs/archive/theme-warm-hive.md` |

### Current theme tokens

| Token | Value | Used for |
|---|---|---|
| `--color-paper` | `#FFF8E7` | Page background |
| `--color-ink` | `#1E1E1E` | Primary text, dark buttons |
| `--color-honey` | `#F2C96D` | Primary accent, badges |
| `--color-honey-deep` | `#6B5745` | Links, secondary text, primary brown |
| `--color-comb` | `#E6DCC4` | Borders, honeycomb tint |
| `--color-leaf` | `#5B6B2E` | Success / delivered states |
| `--radius-lg` … `--radius-3xl` | 15 → 32 px | Inputs, cards, panels |
| Zinc scale | Warm browns | All neutral text/borders, dark mode |

Fonts: **Fredoka** for `font-display` (headings, nav), **Inter** for `font-sans` (body).

### How to change the theme

1. **Colors** — edit the `--color-*` values in the `@theme` block of `resources/css/app.css`. The whole app follows; most views use `bg-paper`, `text-ink`, `bg-honey`, `text-honey-deep`, `border-comb`, `bg-leaf`.
2. **Fonts** — change the `bunny(...)` entries in `vite.config.js` and the `--font-sans` / `--font-display` values in `app.css`.
3. **Radii** — change `--radius-*` in `app.css`; `rounded-lg` inputs, `rounded-2xl` cards and `rounded-3xl` heroes follow.
4. **Hero and panel fills** — the pale-yellow `bg-[#FFF4C5]` appears in:
   - `resources/views/livewire/storefront/marketplace.blade.php`
   - `resources/views/livewire/storefront/business-profile.blade.php`
   - `resources/views/pages/dashboard.blade.php`
   - `resources/views/components/storefront/product-card.blade.php`
   - `resources/views/livewire/storefront/product-details.blade.php`
   - `resources/views/layouts/app/sidebar.blade.php` (tint)
5. **Honeycomb** — replace the data URI in `.bg-comb` inside `app.css`.
6. **Logo** — `components/app-logo.blade.php`; the mobile header also contains a small mark in `layouts/app/sidebar.blade.php`.
7. **Rebuild and verify**:

   ```bash
   npm run build
   php artisan test
   vendor/bin/pint --test
   vendor/bin/phpstan analyse
   ```

   Re-run an axe audit and refresh `docs/screenshots/figma-theme/` if the look changed.
8. **Restore the previous Warm Hive theme** — `git checkout ui-backup/warm-hive`, or copy the tokens from `docs/archive/theme-warm-hive.md`.

---

## 2. Routing — how to trace a page

| Route file | Contents |
|---|---|
| `routes/web.php` | Public storefront, dashboard, cart, checkout, orders, rewards, chat, seller desk, Hive Guard |
| `routes/settings.php` | Profile, security, appearance settings |
| `routes/console.php` | Scheduled BeeRank and retention jobs |

**Public page flow:**

```
routes/web.php → StorefrontController → resources/views/storefront/*.blade.php
               → <livewire:storefront.*> → app/Livewire/Storefront/* + resources/views/livewire/storefront/*
```

**Signed-in page flow:**

```
routes/web.php → Route::livewire('pages::chat.index')
               → resources/views/pages/chat/index.blade.php   (PHP + Blade in one file)
```

| URL | Route target | File |
|---|---|---|
| `/marketplace` | `StorefrontController@marketplace` | `app/Livewire/Storefront/Marketplace.php` |
| `/categories` | `StorefrontController@categories` | `resources/views/storefront/categories.blade.php` |
| `/categories/{category}` | `StorefrontController@category` | `resources/views/storefront/category.blade.php` |
| `/businesses/{business}` | `StorefrontController@business` | `app/Livewire/Storefront/BusinessProfile.php` |
| `/businesses/{business}/products/{product}` | `StorefrontController@product` | `app/Livewire/Storefront/ProductDetails.php` |
| `/ranking` | `StorefrontController@ranking` | `app/Livewire/Storefront/Ranking.php` |
| `/dashboard` | `Route::view` | `resources/views/pages/dashboard.blade.php` |
| `/cart`, `/checkout` | Livewire pages | `pages/cart.blade.php`, `pages/checkout.blade.php` |
| `/orders`, `/orders/{order}` | Livewire pages | `pages/orders/index.blade.php`, `pages/orders/show.blade.php` |
| `/rewards` | Livewire page | `pages/rewards.blade.php` |
| `/chat`, `/chat/{conversation}` | Livewire pages | `pages/chat/index.blade.php`, `pages/chat/show.blade.php` |
| `/seller/*` | Livewire pages | `pages/seller/*.blade.php` |
| `/admin/businesses`, `/admin/orders`, `/admin/reviews` | Livewire pages | `pages/admin/*.blade.php` |
| `/login`, `/register`, `/forgot-password`, … | Livewire pages | `pages/auth/*.blade.php` |

---

## 3. UI file index by area

| Area | Files |
|---|---|
| Brand / shell | `components/app-logo.blade.php`, `components/storefront/layout.blade.php`, `components/storefront/product-card.blade.php` |
| Public storefront | `app/Livewire/Storefront/{Marketplace,BusinessProfile,ProductDetails,Ranking}.php` + `resources/views/livewire/storefront/*.blade.php` |
| Storefront route wrappers | `resources/views/storefront/{marketplace,categories,category,business,product,ranking}.blade.php` |
| Customer app | `pages/dashboard.blade.php`, `pages/cart.blade.php`, `pages/checkout.blade.php`, `pages/orders/*`, `pages/rewards.blade.php`, `pages/chat/*` |
| Seller desk | `pages/seller/{onboarding,products,orders,pending}.blade.php` |
| Hive Guard (admin) | `pages/admin/{businesses,orders,reviews}.blade.php` |
| Auth | `pages/auth/*.blade.php`, `layouts/auth.blade.php`, `layouts/auth/simple.blade.php`, `components/auth-header.blade.php`, `components/auth-session-status.blade.php` |
| Settings | `pages/settings/*`, `partials/settings-heading.blade.php` |
| Navigation | `layouts/app/sidebar.blade.php`, `components/desktop-user-menu.blade.php`, `components/storefront/layout.blade.php` |
| Shared visual pieces | `resources/views/components/*`, `resources/views/partials/*` |
| Vendor overrides | `resources/views/vendor/livewire/*`, `resources/views/vendor/pagination/*`, `resources/views/flux/icon/*` |

---

## 4. Domain functions

### Models — `app/Models`

| Model | Responsibility |
|---|---|
| `User` | Accounts, roles, `sellerBusiness()`, points helpers, initials |
| `Profile` | Optional profile record |
| `Business` | Seller shop, status, currency, products/orders/reviews relations |
| `Category` | Marketplace categories |
| `Product` | Listings, price/stock/currency, search scope |
| `ProductImage` | Product images |
| `CartItem` | One cart line per user/product |
| `Order` | One seller order, totals, status timestamps, status events |
| `OrderItem` | Immutable product name/price snapshot |
| `OrderStatusEvent` | Audit trail of status changes |
| `Review` | Verified-purchase rating, published scope |
| `PointsLedgerEntry` | Append-only Honey Points rows |
| `RankSnapshot` | Published BeeRank rows |
| `Conversation` | Customer/seller thread, response timing, `latestMessage()` |
| `Message` | Chat messages |

### Services — `app/Services`

| Service | Responsibility |
|---|---|
| `CartService` | Add/update/remove cart lines, stock clamping, per-currency subtotals |
| `CheckoutService` | Split cart by seller, lock stock, create orders, award points, per-currency shipping |
| `PointsService` | Award/reverse Honey Points with a ledger entry |
| `BeeRankService` | Rolling 30-day score components and thresholds |
| `RankingService` | Rank all businesses, publish/replace period snapshots, Rising Bees |

### Policies — `app/Policies`

| Policy | Gates |
|---|---|
| `BusinessPolicy` | View, create, update, delete, approve/updateStatus (admin) |
| `ProductPolicy` | Product ownership/management |
| `OrderPolicy` | View (customer/seller/admin), updateStatus, cancel, review |
| `ReviewPolicy` | View, create, update, delete, moderate (admin) |
| `ConversationPolicy` | View and reply to a conversation |

### Enums — `app/Enums`

`BusinessStatus`, `ProductStatus`, `OrderStatus`, `ReviewStatus`, `PointsReason` — labels, values and status helpers (progression/cancellable/terminal).

### Support — `app/Support`

- `Money.php` — minor/major conversion, currency exponents, formatting.
- `BeeRankResult.php` — value object returned by `BeeRankService`.

### HTTP and commands

| File | Role |
|---|---|
| `app/Http/Middleware/EnsureSeller.php` | Seller desk access (active/pending/no-business redirects) |
| `app/Http/Middleware/EnsureAdmin.php` | Hive Guard access |
| `app/Http/Middleware/SecurityHeaders.php` | Headers on every response |
| `app/Console/Commands/ComputeBeeRank.php` | `bee:rank` publication |
| `app/Console/Commands/SetBusinessStatus.php` | `business:status` CLI approval/suspension |
| `app/Console/Commands/PruneStaleRecords.php` | `delibee:prune` retention |
| `app/Console/Commands/DatabaseStatus.php` | `delibee:status` health report |

### Config

| File | Controls |
|---|---|
| `config/commerce.php` | Currency, per-currency shipping, points, review reward, review rate limit |
| `config/beerank.php` | BeeRank weights, thresholds, Rising Bee age |
| `config/chat.php` | Chat messages per minute |
| `config/fortify.php` | Auth features |
| `config/permission.php` | Spatie roles/permissions |

---

## 5. Feature → files map

| Feature | UI | Logic | Data |
|---|---|---|---|
| Marketplace search/filters | `livewire/storefront/marketplace.blade.php` | `Marketplace.php` (search/sort scopes) | `products`, `categories` |
| Product page / add to cart | `livewire/storefront/product-details.blade.php` | `ProductDetails.php`, `CartService` | `products`, `cart_items` |
| Business profile / ratings | `livewire/storefront/business-profile.blade.php` | `BusinessProfile.php`, `BeeRankService` | `businesses`, `reviews`, `rank_snapshots` |
| Cart | `pages/cart.blade.php` | `CartService` | `cart_items` |
| Checkout | `pages/checkout.blade.php` | `CheckoutService` | `orders`, `order_items`, `order_status_events` |
| Orders + cancellation | `pages/orders/{index,show}.blade.php` | `OrderPolicy`, cancel action, `PointsService` | `orders`, `order_items`, `points_ledger` |
| Reviews + moderation | `pages/orders/show.blade.php`, `pages/admin/reviews.blade.php` | `Review`, `ReviewPolicy` | `reviews` |
| BeeRank | `livewire/storefront/ranking.blade.php` | `BeeRankService`, `RankingService`, `bee:rank` | `rank_snapshots`, `orders`, `reviews`, `conversations` |
| Honey Points | `pages/rewards.blade.php` | `PointsService` | `points_ledger` |
| Bee Chat | `pages/chat/{index,show}.blade.php` | `Conversation`, `Message`, `ConversationPolicy`, rate limiter | `conversations`, `messages` |
| Seller desk | `pages/seller/*` | `EnsureSeller`, `Business`, `Product`, `Order` | `businesses`, `products`, `orders` |
| Hive Guard | `pages/admin/*` | `EnsureAdmin`, `BusinessPolicy`, `ReviewPolicy` | `businesses`, `orders`, `reviews` |
| Auth / settings | `pages/auth/*`, `pages/settings/*` | Fortify, `Profile`, `User` | `users`, `profiles`, `sessions` |

---

## 6. Database

- Migrations: `database/migrations/` — 21 files, from `0001_01_01_*` (users/cache/jobs) through the domain tables to the review unique index, FK indexes and CHECK constraints.
- Factories: `database/factories/`.
- Seeders: `database/seeders/` (`DatabaseSeeder`, `RoleSeeder`).
- Money is stored as integer **minor units** plus a currency code; use `Money::format()` for display.
- Status columns cast to the enums in `app/Enums`.

**Adding a field:** create a migration, update the model’s `#[Fillable]`/casts and factory, then add a feature test. Run `php artisan migrate` and `php artisan test`.

---

## 7. Tests

| Folder | Covers |
|---|---|
| `tests/Feature/Domain/` | BeeRank, cart, chat, checkout, currency, database constraints, points |
| `tests/Feature/Http/` | Page smoke tests, dashboard, security headers, home redirect |
| `tests/Feature/Admin/` | Hive Guard businesses, orders, reviews |
| `tests/Feature/Seller/` | Seller access rules |
| `tests/Feature/Commands/` | `bee:rank`, `business:status`, `delibee:prune`, `delibee:status` |
| `tests/Feature/Auth/`, `tests/Feature/Settings/` | Fortify auth and settings flows |
| `tests/Unit/` | Money helper |

Run one file: `php artisan test tests/Feature/Admin/AdminReviewsTest.php`. Tests use the `delibee_test` database and `RefreshDatabase`.

**Where to add a test:** put domain/service behavior under `Domain`, page rendering under `Http`, console commands under `Commands`, and role-gated screens under `Admin`/`Seller`.

---

## 8. Assets and build

| Path | Contents |
|---|---|
| `resources/css/app.css` | Tailwind import, Flux CSS, theme tokens, utilities |
| `resources/js/app.js` | JavaScript entry (currently minimal) |
| `vite.config.js` | Laravel Vite plugin, Bunny fonts, Tailwind |
| `public/build/` | Generated assets (gitignored) |
| `public/favicon.*`, `apple-touch-icon.png` | Favicons |

```bash
npm run dev      # hot reload during development
npm run build    # production build (run after CSS/JS changes)
```

---

## 9. Scripts and operations

| Script | Purpose |
|---|---|
| `scripts/backup/backup-database.ps1` / `.sh` | Verified dump, optional off-site copy, retention |
| `scripts/backup/verify-backup.ps1` / `.sh` | Restore drill: scratch DB, count comparison, cleanup |
| `scripts/backup/register-backup-task.ps1` | Windows nightly task registration |
| `docs/operations/database-operations.md` | Backups, PITR, `listen_addresses`, SSL, checklist |
| `php artisan delibee:status` | Database size, tables, indexes, migrations, queues, latest backup |
| `php artisan delibee:prune` | Sessions >7 days, failed jobs >30 days |
| `php artisan bee:rank --period=month` | Publish BeeRank snapshots |
| `php artisan business:status {slug} {status}` | Approve/suspend a seller |

---

## 10. Documentation index

| File | Contents |
|---|---|
| `docs/README.md` | Documentation index |
| `docs/guides/code-map.md` | This guide |
| `docs/operations/database-operations.md` | Database runbook |
| `docs/reports/project-progress-report-2026-11-12.md` | Full build progress |
| `docs/reports/database-progress-report-2026-11-09.md` | Database report + presentation blueprint |
| `docs/archive/theme-warm-hive.md` | Previous theme tokens and restore steps |

---

## 11. Quick “where do I change X?”

| I want to change… | Go to |
|---|---|
| Brand colors | `resources/css/app.css` → `@theme` `--color-*` |
| Fonts | `vite.config.js` + `@theme` `--font-*` |
| Corner radius / pill shape | `app.css` → `--radius-*` |
| Logo | `components/app-logo.blade.php` |
| Header/footer/nav | `components/storefront/layout.blade.php` (public), `layouts/app/sidebar.blade.php` (app) |
| Hero background | the `bg-[#FFF4C5]` lines listed in §1 |
| Honeycomb pattern | `.bg-comb` in `app.css` |
| Product card | `components/storefront/product-card.blade.php` |
| A page’s text or layout | the matching file in §2/§3 (Livewire pages hold PHP + Blade together) |
| Business rules | services in `app/Services`, policies in `app/Policies` |
| Prices/shipping/points | `config/commerce.php`, `app/Support/Money.php` |
| BeeRank weights | `config/beerank.php`, `app/Services/BeeRankService.php` |
| Chat/review limits | `config/chat.php`, `config/commerce.php`, the relevant Livewire action |
| Navigation item | `layouts/app/sidebar.blade.php` or `components/storefront/layout.blade.php` |
| Auth flow | `pages/auth/*`, `config/fortify.php` |
| Database schema | `database/migrations/`, model + factory |
| Tests | the matching folder in §7 |
| Backups/ops | `scripts/backup/*`, `docs/operations/database-operations.md` |

---

## 12. Conventions

- Prefer theme tokens (`bg-paper`, `text-ink`, `bg-honey`) over hardcoded hex values; the only intentional hex fills are the hero/placeholder cream in §1.
- Signed-in pages are Livewire 4 single-file components — keep PHP and Blade together.
- Public storefront components are class-based in `app/Livewire/Storefront`; their views live in `resources/views/livewire/storefront`.
- Authorization belongs in policies; queries and money/stock rules belong in services.
- Every behavioral change needs a feature test; run `php artisan test`, Pint and PHPStan before committing.
- After theme/CSS changes run `npm run build`, then re-check accessibility and refresh the screenshots.
