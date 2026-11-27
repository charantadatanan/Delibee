# deliBEE — Database Integration Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Report date:** 27 November 2026
**Production:** https://delibee-production-6l26tu.laravel.cloud
**Database:** Laravel Serverless Postgres `delibee-db` (`neon_serverless_postgres`), database/schema `production`, Singapore (`ap-southeast-1`)
**Server version:** PostgreSQL 18.7
**Status:** Integrated; application live on Laravel Cloud
**Prepared by:** Database integration workstream

---

## Key numbers at a glance

| Metric | Value |
|---|---:|
| Migrations applied | 23 / 23 |
| Tables | 29, including `migrations` |
| Indexes | 83 |
| Database CHECK constraints | 7 |
| Production PostgreSQL | 18.7 |
| Businesses / products / product images | 5 / 20 / 20 |
| Orders / reviews / users | 30 / 28 / 25 |

---

## 1. Progress

- **Schema and migrations.** The schema now has **29 tables from 23 migration files**. The original 21 migrations produced 28 tables; the 27 November auth work adds `users.username`, email-verification code fields, `profiles.phone_country` and `social_identities` (`database/migrations/2026_11_27_085804_add_rafs_auth_fields_to_users_and_profiles.php`, `database/migrations/2026_11_27_090932_create_social_identities_table.php`). On Cloud, `php artisan migrate:status` reports all 23 as **Ran** in batch 1.
- **PostgreSQL 18 is the only target.** The `pgsql` connection in `config/database.php` reads `DB_*`; `.env.example` defaults to `DB_CONNECTION=pgsql`, port 5432 and database `delibee`. Local development also runs PostgreSQL 18 (18.6 per the 9 November report), while production reports **18.7**. Laravel Cloud injects the Postgres connection details as `DB_*` variables at runtime; the repository sets only `DB_CONNECTION=pgsql`, and no credentials are committed (`.env` is gitignored).
- **Search lives in PostgreSQL.** `database/migrations/2026_10_21_000400_create_products_table.php` enables `pg_trgm` and `unaccent`, adds a generated `products.search_vector tsvector` column (name weight A, description weight B) with a GIN index, and adds a trigram GIN index on `products.name`. `Product::scopeSearch()` uses `websearch_to_tsquery('simple', ...)` plus an `ILIKE` fallback, and `scopeOrderByRelevance()` ranks with `ts_rank()` (`app/Models/Product.php`). `app/Livewire/Storefront/Marketplace.php` calls both. `unaccent` is installed by the migration but is not yet called by the query scope.
- **Constraints and indexes back the business rules.** `database/migrations/2026_11_09_000000_add_check_constraints.php` adds seven CHECK constraints: non-negative product price and stock, ratings 1–5, positive cart/order-item quantities, non-negative order-item amounts and non-negative order amounts. Unique guards cover `users.email`, `users.username`, `businesses.slug`, product `(business_id, slug)`, order numbers, review `(user_id, order_item_id)`, cart `(user_id, product_id)`, conversation `(business_id, user_id)`, rank snapshots and both `social_identities` pairs. `database/migrations/2026_11_08_000100_add_foreign_key_indexes.php` adds eight covering indexes for relation, timeline, chat and category lookups; `database/migrations/2026_11_08_000000_add_unique_index_to_reviews_table.php` adds the review uniqueness guard.
- **Seeders are production-safe.** `database/seeders/DatabaseSeeder.php` always runs `RoleSeeder` (3 roles, 5 permissions) and `CategorySeeder` (7 categories), both idempotent, then returns before demo data when `app()->isProduction()`. `DemoSeeder` defines the 5-business, 30-order, 28-review demonstration set; `DemoPhotoSeeder` writes product photos to `Storage::disk('public')` (`database/seeders/DemoPhotoSeeder.php`). The standard deploy seed therefore does not recreate demo rows in production; the live snapshot already contains the demonstration dataset.
- **Sessions, cache and queues use Postgres.** `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches` and `failed_jobs` come from `database/migrations/0001_01_01_000000_create_users_table.php`, `database/migrations/0001_01_01_000001_create_cache_table.php` and `database/migrations/0001_01_01_000002_create_jobs_table.php`. Production sets `SESSION_DRIVER=database`, `CACHE_STORE=database` and `QUEUE_CONNECTION=database`. `delibee:prune` deletes sessions idle beyond 7 days and failed jobs older than 30 days, scheduled daily at 03:05 (`routes/console.php`, `app/Console/Commands/PruneStaleRecords.php`).
- **Object storage is attached.** Public bucket `delibee-public` is the default `public` disk; `league/flysystem-aws-s3-v3` is installed and `config/filesystems.php` defines the disk. Seeded photos are stored under `products/*.jpg` and resolve from the bucket in production.
- **Deployment.** `main` deploys to the `production` environment with `php artisan migrate --force && php artisan db:seed --force`; the current deployment is commit `4988c76`. Cloud injects the Postgres and bucket connection values; the repository contains none of them (`docs/guides/laravel-cloud.md`).

---

## 2. Final output

The production database/schema is the 23-migration schema, organised as follows:

| Domain | Tables | Count |
|---|---|---:|
| Identity and access | `users`, `password_reset_tokens`, `sessions`, `profiles`, `social_identities`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | 10 |
| Marketplace | `businesses`, `categories`, `products`, `product_images` | 4 |
| Commerce | `cart_items`, `orders`, `order_items`, `order_status_events`, `reviews` | 5 |
| Loyalty and ranking | `points_ledger`, `rank_snapshots` | 2 |
| Chat | `conversations`, `messages` | 2 |
| Framework infrastructure | `migrations`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | 6 |
| **Total** | | **29** |

Production data snapshot, 27 November 2026:

| Table | Rows | Notes |
|---|---:|---|
| `users` | 25 | Accounts in the production database |
| `businesses` | 5 | 4 active; 1 pending and hidden from the storefront |
| `categories` | 7 | Seeded marketplace categories |
| `products` | 20 | Catalogue rows, PHP pricing |
| `product_images` | 20 | Photos served from `delibee-public` |
| `orders` | 30 | One order per seller per checkout |
| `reviews` | 28 | Verified-purchase reviews only |

The storefront reads this data live at `https://delibee-production-6l26tu.laravel.cloud`. The marketplace returns the four active businesses and their products; the pending business is not exposed. Image URLs resolve from the Cloud bucket.

---

## 3. Verification

| Check | Result |
|---|---|
| Deployment | Commit `4988c76` deployed successfully to `delibee` / `production` on 27 November 2026 |
| Migrations | `php artisan migrate:status` — **23 / 23 Ran**, batch 1 |
| Database status | `php artisan delibee:status` — database `production`; PostgreSQL **18.7**; 10,208 kB; **29 tables**; **83 indexes**; **23 migrations**; 0 pending jobs; 0 failed jobs; latest app backup `none` |
| Row counts | `users` 25, `businesses` 5, `products` 20, `product_images` 20, `orders` 30, `reviews` 28 |
| Search read path | Live `/marketplace?search=honey` returned HTTP 200 with matching `Wild Honey` products and `products/wild-honey.jpg` images |
| Marketplace read path | `/marketplace`, `/ranking`, `/login` and `/up` returned HTTP 200; active shops and seeded products rendered |
| Object storage | `products/fresh-apples.jpg` from `delibee-public` returned HTTP 200 `image/jpeg` |
| Extensions/search objects | The products migration that creates `pg_trgm`, `unaccent`, the generated `tsvector` and both GIN indexes is applied; `Product::scopeSearch()` and `scopeOrderByRelevance()` are the live query path |
| Integrity rules | Seven CHECK constraints and the unique/FK indexes are present via the applied migrations; the production schema reports 83 indexes |
| Backup / PITR | Cloud metadata: `supportsPitr: true`, `earliestRestoreTime` 2026-11-27 12:59:40 UTC, `retention_days: 1`; no manual snapshots listed |
| CI guardrail | `.github/workflows/ci.yml` provisions PostgreSQL 18 for the migration suite and tests |

---

## 4. Open items

1. **No application-level backup runbook on Cloud.** The verified dump scripts (`scripts/backup/backup-database.ps1`, `scripts/backup/backup-database.sh`) and restore verifier (`scripts/backup/verify-backup.ps1`, `scripts/backup/verify-backup.sh`) read `DB_*` from a local `.env` and write to `storage/backups`; they are not scheduled against `delibee-db`. `delibee:status` reports `latest backup: none`. Define a Cloud-safe dump path (for example through a private tunnel), schedule, destination, owner and retention, then re-run the restore drill against the 23-migration schema.
2. **PITR and retention settings need an explicit decision.** PITR is available on the cluster, but the current metadata reports a one-day retention setting and there are no manual snapshots. Confirm that this recovery window meets the production RPO/RTO, extend it if required, and document the restore procedure next to `docs/operations/database-operations.md`, whose WAL/PITR section targets a self-managed server.
3. **No live custom domain.** Production is served from `delibee-production-6l26tu.laravel.cloud`. A `Delibee.com.ph` domain has been added in Laravel Cloud, but hostname, SSL and origin checks are still pending, so the branded domain is not live yet.

---

**Primary references:** `docs/guides/laravel-cloud.md`, `docs/operations/database-operations.md`, `database/migrations/`, `database/seeders/`, `config/database.php`, `routes/console.php`, `app/Models/Product.php`, `app/Livewire/Storefront/Marketplace.php`, `app/Console/Commands/DatabaseStatus.php`, `.github/workflows/ci.yml`.
