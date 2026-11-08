# deliBEE Build Progress — For Claude Desktop

> **Project:** deliBEE — a generalized multi-vendor e-commerce marketplace with bee/hive theming  
> **Stack:** Laravel 13 + Livewire + Tailwind + PostgreSQL  
> **Last updated:** 2026-11-01  
> **Current Phase:** **Phase 2** (Scaffold + Models) — mostly complete; ready to move to Phase 3 (Auth, Roles, Seller Onboarding)

---

## ✅ Phase 0 — Local Environment & Repo Setup (COMPLETE)

| Task | Status | Notes |
|------|--------|-------|
| PHP 8.3+ installed & verified | ✅ Done | Via Laravel Herd on Windows |
| Composer installed | ✅ Done | |
| Node.js / npm installed | ✅ Done | For Vite + Tailwind |
| PostgreSQL installed & running | ✅ Done | Local instance on port **5433** |
| PostgreSQL PHP drivers enabled (`pdo_pgsql`, `pgsql`) | ✅ Done | In `php.ini` |
| Git repo `delibee` initialized | ✅ Done | On GitHub as `charantadatanan/delibee` |
| VS Code extensions installed | ✅ Done | Intelephense, Laravel Blade, Tailwind CSS IntelliSense, GitLens, Error Lens, GitHub Copilot, Laravel extension |

**Environment details** (from `.env`):
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=delibee
DB_USERNAME=postgres
DB_PASSWORD=***
```

---

## ✅ Phase 1 — Data Model & Wireframes (COMPLETE)

- **ER diagram / migrations planned** for all core tables (see Phase 2)
- **Wireframes** created in Figma (free account) — mobile-first PWA layout
- **Brand/system names** finalized (Hive Marketplace, Flower Fields, Honey Rewards, BeeRank, Annual Business Rating, Rising Bees, Honey Rating, Honey Pot, Bee Flight, Bee Chat, Hive Guard)

---

## ✅ Phase 2 — Scaffold + Models (MOSTLY COMPLETE)

### Project scaffolding
- `laravel new delibee` with **Livewire starter kit** and **PostgreSQL** — done
- Key packages installed:
  - `spatie/laravel-permission` ^8.3 (roles/permissions)
  - `livewire/flux` ^2.13, `livewire/blaze` ^1.0, `livewire/livewire` ^4.1
  - `laravel/fortify` ^1.37 (auth)
  - `pestphp/pest` ^5.0 (testing)
  - `laravel/pint`, `larastan/larastan` (linting / static analysis)

### Database migrations (16 tables created)
| Migration | Purpose | Status |
|-----------|---------|--------|
| `users` | Core users (Laravel default) | ✅ |
| `cache` / `jobs` | Framework tables | ✅ |
| `profiles` | Extended user profile | ✅ |
| `businesses` | Seller businesses (owner, slug, status, created_at for "new" logic) | ✅ |
| `categories` (Flower Fields) | Product categories with position ordering | ✅ |
| `products` | Business products (price in minor units, stock, status) | ✅ |
| `product_images` | Product image gallery | ✅ |
| `cart_items` (Honey Pot) | Cart items per user | ✅ |
| `orders` | **One per seller** (split at checkout); timestamps `ordered_at`, `shipped_at`, `delivered_at`, `cancelled_at` | ✅ |
| `order_items` | Line items per order | ✅ |
| `order_status_events` (Bee Flight) | Timeline events per order | ✅ |
| `reviews` (Honey Rating) | Verified-purchase link to `order_item`; targets product and/or business | ✅ |
| `points_ledger` (Honey Rewards) | Append-only ledger: `user_id`, `delta`, `reason`, `reference_type`, `reference_id` | ✅ |
| `rank_snapshots` | Monthly/annual BeeRank snapshots with component metrics JSON | ✅ |
| `conversations` / `messages` (Bee Chat) | Chat threads; stores `first_reply_at` for response-time metric | ✅ |
| `permission_tables` | Spatie roles/permissions | ✅ |

### Eloquent Models (all created with relationships)
| Model | Key Features |
|-------|--------------|
| `User` | `HasRoles` (spatie), relationships to profile, businesses, orders, reviews, cart, points ledger, conversations, messages; `pointsBalance()` sums ledger; role helpers `isSeller()`, `isCustomer()`, `isAdmin()` |
| `Business` | Status enum (`pending`, `active`, `suspended`); `isRising()` (age < 6 months), `isRankable()` (not pending); route key = `slug` |
| `Product` | Active scope, belongs to business + category, has images |
| `Order` | Status enum; `isOnTime()` (delivery window from config), `isCompleted()`; scopes `forBusiness()`, `between()` |
| `Review` | Verified purchase via `order_item_id`; status enum |
| `PointsLedgerEntry` | Append-only; `delta` can be positive/negative |
| `RankSnapshot` | Period type `month`/`year`, score, rank, component metrics JSON |
| `Conversation` / `Message` | `first_reply_at` on conversation for BeeRank response-time metric |

### Config files
- `config/beerank.php` — **complete** with all tunables:
  - Window: 30 days
  - Minimum orders: 5
  - Weights: rating 0.40, completion 0.25, on_time 0.20, response_time 0.15
  - Bayesian smoothing: prior_mean 4.0, prior_weight 5, max_rating 5
  - Chat response: fast 2h, slow 24h
  - Delivery window: 5 days
  - Rising Bee age limit: 6 months
  - Score scale: 0–100

### Core Services (implemented)
| Service | Purpose | Status |
|---------|---------|--------|
| `BeeRankService` | **Core ranking engine** — computes score from 4 components over rolling window, enforces minimum orders, Bayesian smoothing, renormalizes weights when components missing | ✅ Done |
| `RankingService` | Reads published snapshots for storefront display (monthly + Rising Bees) | ✅ Done |
| `CartService` | Add/update/remove cart items; merges quantities | ✅ Done |
| `CheckoutService` | **Splits cart into one order per seller**; creates orders + order items + status events; clears cart | ✅ Done |
| `PointsService` | Earn/spend ledger entries; balance = sum | ✅ Done |

### Storefront (public-facing) — Routes + Controllers + Livewire
| Route | Component/Controller | Status |
|-------|---------------------|--------|
| `/` → `/marketplace` | `StorefrontController@marketplace` + `Marketplace` Livewire | ✅ |
| `/categories` | `StorefrontController@categories` | ✅ |
| `/categories/{category}` | `StorefrontController@category` | ✅ |
| `/businesses/{business}` | `StorefrontController@business` (guards `isRankable()`) | ✅ |
| `/businesses/{business}/products/{product}` | `StorefrontController@product` | ✅ |
| `/ranking` | `StorefrontController@ranking` + `Ranking` Livewire | ✅ |

**Livewire components (Storefront):**
- `Marketplace` — search, category filter, price range, sort (newest/price/relevance), pagination
- `Ranking` — displays published monthly BeeRank + Rising Bees via `RankingService`
- `BusinessProfile`, `ProductDetails` — placeholder components created

### Authenticated routes (wired, views pending)
| Area | Routes | Livewire Components |
|------|--------|---------------------|
| Dashboard | `/dashboard` | Blade view |
| Honey Pot (Cart) | `/cart` | `pages::cart` |
| Checkout | `/checkout` | `pages::checkout` |
| Bee Flight (Orders) | `/orders`, `/orders/{order}` | `pages::orders.index`, `pages::orders.show` |
| Honey Rewards | `/rewards` | `pages::rewards` |
| Bee Chat | `/chat`, `/chat/{conversation}` | `pages::chat.index`, `pages::chat.show` |
| Seller Onboarding | `/seller/onboarding`, `/seller/pending` | `pages::seller.onboarding`, `pages::seller.pending` |
| Seller Dashboard (products, orders) | `/seller/products`, `/seller/orders` | `pages::seller.products`, `pages::seller.orders` |

---

## ⏳ Phase 3 — Auth, Roles, Seller Onboarding (NEXT)

**What's done:** Routes defined, middleware groups set up (`auth`, `verified`, `seller`), Spatie permission tables migrated.

**What remains:**
1. **Fortify authentication UI** — login, register, email verification, password reset (Flux components available)
2. **Role assignment** — on registration, assign `customer` role; seller onboarding flow assigns `seller` role + creates `Business` (status = `pending`)
3. **Admin approval** — admin reviews pending businesses, flips status to `active`
4. **Seller middleware** — `EnsureUserIsSeller` gate/policy to protect seller routes
5. **Seller onboarding Livewire pages** — business details, logo upload, payout info (placeholder for now)

---

## 🔲 Phase 4 — Marketplace, Categories, Product Pages, Search (NOT STARTED)

- Category landing pages (`Flower Fields`)
- Product detail pages with images, stock, add-to-cart
- Full-text search (PostgreSQL `tsvector` / `websearch_to_tsquery`)
- Business profile page with products, ratings, BeeRank badge

---

## 🔲 Phase 5 — Cart (Honey Pot), Checkout, Per-Seller Orders (NOT STARTED)

**Critical open questions (must decide before building):**

| Question | Options | Decision Needed |
|----------|---------|-----------------|
| **Launch country** | PH, US, SG, etc. | Determines payment gateway, courier, tax/legal |
| **Payout model** | A) Platform collects → manual payout (MVP) <br> B) Cash on delivery <br> C) Stripe Connect / split payments | **Ask user** |

**Current implementation:** `CheckoutService` splits cart into one order per seller and creates `Order` + `OrderItem` + `OrderStatusEvent(ordered_at)`. Payment integration not yet added.

---

## 🔲 Phase 6 — Order Tracking (Bee Flight) + Ratings (Honey Rating) (NOT STARTED)

- Seller updates order status: `pending → confirmed → shipped → delivered` (or `cancelled`)
- Customer sees timeline (`OrderStatusEvent`)
- Post-delivery: prompt for verified-purchase review (product + business)
- Rating display on product & business pages

---

## 🔲 Phase 7 — BeeRank, Rising Bees, Annual Rating (NOT STARTED)

**Engine ready** (`BeeRankService`), but needs:
- **Scheduler job** — monthly (1st of month) computes BeeRank for all rankable businesses, writes `RankSnapshot` (month)
- **Annual rating job** — yearly (Jan 1) computes yearly aggregate, writes `RankSnapshot` (year)
- **Rising Bees** — filter: business age < 6 months AND meets minimum orders in window
- **Storefront display** — badges on business cards, ranking page with pagination

---

## 🔲 Phase 8 — Honey Rewards (NOT STARTED)

- `PointsService` ready (ledger-based)
- Need: earn rules (purchase %, review bonus, referral), spend rules (discount at checkout), daily cap, admin audit page

---

## 🔲 Phase 9 — Bee Chat (NOT STARTED)

- MVP: Livewire polling (every few seconds)
- Later: Laravel Reverb (WebSockets) — requires long-running process
- Store `first_reply_at` on `Conversation` for BeeRank response-time metric

---

## 🔲 Phase 10 — Hive Guard, Filament Admin, Deploy (NOT STARTED)

- **Filament v3** (verify Laravel 13 compatibility) for admin/seller/support panels
- Admin: user management, business approval, order moderation, points audit, rank snapshot review
- Seller panel: product CRUD, order management, chat, payout requests
- Security hardening: rate limiting, CSP, security headers, backup strategy
- Deploy: choose host with PHP 8.3+, SSH, per-minute cron, PostgreSQL (~$7–15/mo)

---

## 📋 Open Questions for User (Blocking Phase 5)

1. **Launch country** — where will the marketplace first go live? (Decides Stripe vs. local gateway, courier API, tax rules)
2. **Payout model** — how does money flow from customer to seller?  
   - *Option A (recommended for MVP):* Platform collects via Stripe test mode → admin manually pays sellers weekly/monthly  
   - *Option B:* Cash on delivery (seller collects, platform invoices commission)  
   - *Option C:* Stripe Connect / split payments (more complex, defers to post-MVP)
3. **Operating system** — confirmed Windows (Laravel Herd), but good to note for any shell commands

---

## 🧪 Testing Status

| Area | Test Coverage |
|------|---------------|
| BeeRank scoring (weights, smoothing, minimum orders, rising logic) | ❌ Not yet — **write Pest tests first** |
| Points ledger (earn/spend/refund reversibility, daily cap) | ❌ Not yet |
| Order splitting (cart → one order per seller) | ❌ Not yet |
| Auth / roles / permissions | ❌ Not yet |

**Policy:** Write tests **before** or **with** the feature for ranking, points, and order-splitting logic.

---

## 📁 Key Files to Know

| File | Why It Matters |
|------|----------------|
| `config/beerank.php` | All ranking knobs — tweak without code changes |
| `app/Services/BeeRankService.php` | Core ranking algorithm (read this to understand scoring) |
| `app/Services/CheckoutService.php` | Cart → per-seller order splitting |
| `app/Models/Order.php` | `isOnTime()`, `isCompleted()`, status enums |
| `app/Models/Business.php` | `isRising()`, `isRankable()`, status enum |
| `database/migrations/*` | Full schema with indexes for ranking queries |
| `routes/web.php` | All route definitions with middleware groups |
| `.env` | PostgreSQL on port **5433** |

---

## 🚀 Next Immediate Steps

1. **Confirm open questions** (launch country + payout model)
2. **Build Phase 3:**
   - Fortify auth views (Flux components)
   - RoleSeeder assigns `customer` on registration
   - Seller onboarding flow: form → creates `Business` (pending) → assigns `seller` role
   - Admin approval action in Filament (or simple artisan command for now)
   - `seller` middleware / gate
3. **Write Pest tests** for `BeeRankService` (edge cases: no orders, no reviews, missing components, rising bee boundary)
4. **Seed demo data** (`DemoSeeder` exists) and verify BeeRank computes correctly
5. **Move to Phase 4** once seller can log in and see their dashboard

---

## 💡 Notes for Future Sessions

- **Never store card data** — Stripe Elements / Payment Element only
- **Secrets in `.env` only** — `.env` is gitignored
- **Money = integer minor units + currency code** — never floats
- **Event timestamps from day one** — `ordered_at`, `shipped_at`, `delivered_at`, `cancelled_at`, `first_reply_at` cannot be reconstructed later
- **Free tools only** — flag any cost before adding
- **Check Laravel 13 / PHP 8.3+ compatibility** before any new package
- **Mobile-first PWA** — Tailwind responsive, `manifest.json`, service worker (later)

---

*This file lives at `PROGRESS.md` in the project root. Update it at the end of each major session.*