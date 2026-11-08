# DEEPSEEK.md: deliBEE continuation brief

> For: a DeepSeek coding agent continuing an EXISTING Laravel project. Written to spend few tokens.
> Do not re-derive what is decided here. Do not re-read what is summarized here.

## 0. Read order and precedence
1. This file (rules + the next task).
2. `CLAUDE.md` in the repo root: full product spec, rationale, phase checklist (§8). Authoritative for product intent.
3. The repo itself. If a doc disagrees with the repo, **trust the repo and fix the doc**.

Precedence: user's latest message > this file > `CLAUDE.md`. Where this file is silent, `CLAUDE.md` applies.
Work **with the existing scaffold** (starter-kit files, `composer.json`, `vite.config.js`, `phpunit.xml`, `phpstan.neon`, `pint.json`). Never re-run `laravel new`, never regenerate or replace the project.

## 1. Snapshot
- **Product:** deliBEE, a multi-vendor marketplace that ranks sellers on last-30-day performance (BeeRank), highlights new sellers (Rising Bees), tracks a yearly rating, and rewards buyers (Honey Points ledger). Bee-themed names are branding; UI always pairs them with a plain label (see CLAUDE.md §1).
- **Locked stack:** PHP 8.3+, Laravel 13.x, Livewire 4 + Flux 2 (**free** `livewire/flux` only; no Pro components), Fortify auth, Tailwind 4, Vite via `vp`, **PostgreSQL**, `spatie/laravel-permission` 8, Pest 5, Pint, Larastan.
- **Done:** scaffold; Postgres database `delibee` (role `delibee`); spatie installed.
- **Not done:** CLAUDE.md §8 phases 1-10. **Current task: Phase 1 + 2** (schema, migrations, models, factories, roles).
- The repo may be newer than this file. First action, one call: `git log --oneline -n 10; git status -s; ls app/Models database/migrations`. Continue from what you find.

## 2. Token discipline (follow strictly)
- Never open: `vendor/`, `node_modules/`, `composer.lock`, `package-lock.json`, `storage/`, `.env` (never print its contents).
- Search with `rg` / `Select-String` and read line ranges, not whole files.
- Inspect, don't read: `php artisan route:list --except-vendor`, `php artisan model:show <Model>`, `php artisan db:table <table>`, `php artisan migrate:status`.
- Generate, don't hand-write boilerplate: `php artisan make:model X -mf`, `make:enum`, `make:policy`, `make:livewire`, `make:test --pest`. Batch several generators per command.
- Plan in <= 10 lines, then implement. Write each file once. Don't print files back.
- Final report <= 8 lines: what changed, what was verified, what's next, any decision needing the user.
- **Your training data may predate Laravel 13, Livewire 4, Flux 2, Pest 5, Vite+ (`vp`).** Don't guess their APIs. Check `php artisan list`, `composer show -s <pkg>`, package source in `vendor/` (targeted `rg`), existing `app/Livewire` and `resources/views` conventions, or official docs if you have web access.

## 3. Improvement mandate
You may change this plan when your change is clearly better for **correctness, security, simplicity, or cost**, and it stays **free**, on Laravel 13 / PHP 8.3+, and on Postgres. When you do:
1. Make the change.
2. Append one line to `docs/DECISIONS.md`: `YYYY-MM-DD | change | why | files`.
3. Mention it in your report.

Not allowed without asking the user: reintroducing dropped tech (Next.js, Supabase, Vercel/Netlify/Cloudflare hosting, pg_cron), paid packages/services, storing card data, or anything touching **OPEN** items below.

## 4. Fix first (small, high value)
- [ ] `DB_PORT`: Postgres listens on **5432**. `.env`, `.env.example`, `phpunit.xml` may still say `5433` (connection refused). Set all to 5432, then `php artisan config:clear`.
- [ ] Test DB: `delibee_test` may not exist. Create it (owner `delibee`) before running tests.
- [ ] `phpunit.xml` may contain a hard-coded DB password and is **not** git-ignored. Remove `DB_PASSWORD` from it, load it from `.env.testing`, add `.env.testing` to `.gitignore`. Tell the user to rotate the password. Never print or commit it.
- [ ] `composer.json` still has starter-kit name/description. Rename (e.g. `delibee/delibee`) when convenient.
- [ ] After fixes: `php artisan migrate:status` must connect.

## 5. Environment facts
- Windows + **PowerShell**. psql: `& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -p 5432 -c "..."` (PowerShell needs `&` and a `C:\` path; Git-Bash-style `/c/...` paths failed).
- DB: host `127.0.0.1`, port **5432**, database `delibee`, role `delibee`. Passwords are never stored in docs.
- Dev: `composer dev` (runs `php artisan dev`) or `npm run dev` (`vp dev`). Build: `npm run build` (`vp build`).
- **Quality gate:** `composer test` = config:clear + Pint check + PHPStan + `php artisan test`. Keep it green. Auto-fix style with `composer lint`.
- Git: commit locally per milestone with a clear message. Never commit `.env`. Ask before any `git push`.

## 6. Decided design rules (rationale in CLAUDE.md §4)
- Money = **unsigned integers in minor units** + `char(3)` currency. No floats/decimals for money.
- Statuses are **string columns cast to PHP backed enums** (`app/Enums`), not Postgres enum types.
- Roles via spatie: `customer` (default on register), `seller`, `admin`. Authorization via Policies/Gates.
- **One order per seller.** Cart may span sellers; checkout splits.
- Record timestamps: `ordered_at`, `shipped_at`, `delivered_at`, `cancelled_at`, `first_reply_at`. Rankings are computed from them.
- Honey Points = **append-only ledger**; balance = `SUM(delta)`; never a mutable balance column.
- Reviews only from **verified, delivered** purchases.
- Sellers update order status themselves (Bee Flight timeline). No courier/delivery-person role in MVP.
- Bee Chat = Livewire polling first (Reverb later).

## 7. OPEN. Ask the user, don't assume
- **Launch country** (payment gateway, couriers, tax).
- **Seller payout model** (platform collects + manual payout vs COD vs split payments).
- Until decided: **no payments/payouts tables, no gateway packages, no checkout payment step.** Build checkout up to order creation only.

## 8. TASK: Phase 1 + 2 (schema, migrations, models)
Create migrations in this order (Postgres; use `foreignId()->constrained()`; names/types below are the contract):

```
categories     id; parent_id null->categories nullOnDelete; name; slug unique; sort smallint=0; timestamps
businesses     id; owner_id->users restrict; name; slug unique; description text null;
               status string='pending'; ship_within_days tinyint=3; timestamps; softDeletes; idx(status)
products       id; business_id->businesses restrict; category_id->categories restrict; name;
               slug; description text null; price_minor uint; currency char(3); stock uint=0;
               status string='draft'; timestamps; softDeletes;
               unique(business_id,slug); idx(business_id,status); idx(category_id,status)
product_images id; product_id cascade; path; sort usmallint=0; timestamps
profiles       id; user_id unique cascade; phone string(32) null; address text null; timestamps
cart_items     id; user_id cascade; product_id cascade; quantity usmallint; timestamps;
               unique(user_id,product_id)
orders         id; user_id->users restrict; business_id->businesses restrict; status string='pending';
               total_minor uint; currency char(3); ordered_at ts; shipped_at/delivered_at/cancelled_at ts null;
               timestamps; idx(business_id,ordered_at); idx(user_id,ordered_at); idx(business_id,status)
order_items    id; order_id cascade; product_id->products restrict; name_snapshot string;
               quantity usmallint; unit_price_minor uint; timestamps
order_status_events  id; order_id cascade; status string; note string null; created_at only;
               idx(order_id,created_at)
reviews        id; order_item_id unique->order_items restrict; user_id->users restrict;
               business_id->businesses restrict; rating utinyint CHECK 1..5; body text null;
               timestamps; idx(business_id,created_at)
points_ledger  id; user_id->users restrict; delta int (+/-); reason string; ref_type string null;
               ref_id ubigint null; created_at only; idx(user_id,created_at); idx(ref_type,ref_id);
               unique(user_id,reason,ref_type,ref_id)   -- idempotent awards
rank_snapshots id; business_id cascade; period_type string ('month'|'year'); period_start date;
               score decimal(6,3); rank uint; metrics jsonb; created_at only;
               unique(business_id,period_type,period_start); idx(period_type,period_start,rank)
conversations  id; customer_id->users cascade; business_id->businesses cascade;
               first_reply_at ts null; timestamps; unique(customer_id,business_id)
messages       id; conversation_id cascade; sender_id->users restrict; body text; timestamps;
               idx(conversation_id,created_at)
```

**Deviations from the ERD already shown to the user (intentional improvements; log them in `docs/DECISIONS.md`):**
1. `order_items.name_snapshot`: product names change; order history must not.
2. `orders.currency`: the ERD only had it on products.
3. `businesses.ship_within_days`: needed to measure on-time shipping for BeeRank.

**Also deliver:**
- Enums: `BusinessStatus`(pending, active, suspended), `ProductStatus`(draft, active, archived), `OrderStatus`(pending, confirmed, shipped, delivered, cancelled), `PeriodType`(month, year). Cast them in models.
- Models with relationships, casts, `$fillable`/guarded, SoftDeletes where noted, factories for every model.
- `PointsLedger` model: **throws on update/delete** (append-only). Add `User::honeyBalance()` = `SUM(delta)`.
- `RolesSeeder` (customer, seller, admin); new registrations get `customer`. Extend the starter kit's registration action, don't replace it.
- Pest tests: relationships; unique constraints (cart, review-per-item, snapshot); ledger append-only; rating CHECK; money columns are integers; review allowed only when the order is `delivered` and belongs to the reviewer (enforce in a service/policy, test it).
- Do **not** build UI yet beyond what the starter kit has.

**Done when:** `php artisan migrate:fresh --seed` succeeds on Postgres (5432) and `composer test` is green. Then tick CLAUDE.md §8 (phases 1-2), update §9 status, and report.

## 9. Later phases: pre-decided so you don't burn tokens deliberating
- **Phase 4 search:** Postgres full-text. Add generated `tsvector` column + GIN index on products via raw SQL migration.
- **Phase 7 BeeRank** (all weights/thresholds in `config/beerank.php`, tunable): window 30 days; min 5 orders in window to be ranked; `score = 0.40*smoothed_rating + 0.25*completion_rate + 0.20*on_time_ship_rate + 0.15*response_score`. `smoothed_rating = (v*R + m*C)/(v+m)` with m=10, C=platform mean, v=review count in window. On-time = `shipped_at <= ordered_at + ship_within_days`. Response score from `conversations.first_reply_at - created_at` (<=1h => 1, >=48h => 0, linear; no chats => neutral). **Rising Bees** = business age <= 180 days and min orders met. Run via Laravel Scheduler monthly; persist to `rank_snapshots`; yearly rating reads the 12 monthly rows. Seed demo data with realistic timestamps to test (one 2-month-old strong seller vs one established seller).
- **Phase 8 Honey Rewards:** award via one service (`Honey::award(user, reason, ref)`), daily caps, reversal row on refund/cancel. Points have no cash value.
- **Phase 9 Bee Chat:** Livewire `wire:poll`; set `first_reply_at` when a business-side message is the first reply.
- **Phase 10:** Filament for admin/seller/support dashboards **only after** confirming a release compatible with Laravel 13 / Livewire 4 (check Packagist). Hive Guard = Policies, validation, rate limiting, moderation queue.

## 10. Style
- Laravel conventions, Pint `laravel` preset, strict types where the codebase already uses them, small classes, thin controllers/components, logic in `app/Actions` or `app/Services`.
- Explain **why** briefly when you recommend or change something; the user wants reasons, not essays.
- Keep this file and `CLAUDE.md` current: update status/checklists at the end of each phase; don't let them drift.
