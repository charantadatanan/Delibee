# deliBEE — Project Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Build period:** 21 October – 12 November 2026
**Report date:** 12 November 2026
**Prepared by:** ____________________
**Status:** MVP complete

---

## 1. Executive summary

deliBEE is a multi-vendor marketplace where sellers are ranked on how they are performing now, not on lifetime totals. The build started from an empty Laravel scaffold on 21 October 2026 and reached a complete, tested MVP on 12 November 2026.

By the finish line the project had:

- **41 commits**, **246 tracked files** and **24 Livewire pages**.
- **21 migrations** covering **28 tables**, **79 indexes** and **7 database CHECK constraints**.
- **115 automated tests** (114 passing, 1 skipped) with **273 assertions**.
- **PHPStan level 8** with zero errors and a clean Pint run.
- GitHub Actions running the full suite against a fresh PostgreSQL 18 service on every push.
- Zero axe-core accessibility violations across the storefront, customer, seller, admin, auth and settings pages in both light and dark mode.
- Verified backups: custom-format dumps, a Linux/Windows backup script, a successful restore drill into a scratch database, and a registered nightly Windows task.
- Two complete visual themes, with the previous one preserved on a GitHub branch and tag.

The build is complete for the MVP. The only remaining work is operational (off-machine backup storage/PITR, optional `listen_addresses` hardening) and the payment gateway integration.

---

## 2. At a glance

| Metric | Value |
|---|---:|
| Build period | 21 Oct – 12 Nov 2026 |
| Commits | 41 |
| Tracked files | 246 |
| Migrations | 21 |
| Business tables | 28 (including framework tables) |
| Indexes | 79 |
| CHECK constraints | 7 |
| Application tests | 115 (114 passed, 1 skipped, 273 assertions) |
| Static analysis | PHPStan level 8 — 0 errors |
| Code style | Pint — clean |
| Accessibility | 0 axe violations (light and dark) |
| CI | Green on every push |
| Backup restore drill | 28 tables, 21 migrations, matching row counts |
| Nightly backup task | Registered, next run 21:30 |

---

## 3. How the build progressed

The work moved through five phases. Build work began on 21 October; the repository history starts on 8 November when the project was first committed.

### Phase 0 — Foundation (21 October – 7 November)

Uncommitted work that established the product:

- Laravel 13 scaffold with PHP 8.4, Livewire 4, Flux UI, Tailwind CSS 4 and Vite.
- PostgreSQL schema, migrations, models and factories for identity, marketplace, commerce, loyalty, ranking and chat.
- Fortify authentication, roles and permissions, seller onboarding and the seller desk.
- Storefront, cart, checkout, orders, verified reviews, BeeRank, Honey Points and Bee Chat.
- PostgreSQL full-text search with a generated `tsvector` column plus trigram matching.
- Demo data with realistic order histories and rank snapshots.

### Phase 1 — Repository and first public build (8 November)

The project was committed and shipped as a working application:

- Initial commit with the complete MVP (`2ad0e39`).
- Warm Hive visual redesign across storefront, app shell, settings and auth.
- README, screenshots and a GitHub Actions workflow.
- Seller access hardening and expanded domain tests.
- Chat entry point from product pages and a fix for the polling 500.
- CLI business approval, per-currency cart/checkout totals, Hive Guard business moderation.
- Composer/npm security updates and the Vite+ tooling upgrade.

### Phase 2 — Database hardening (9 November)

- Seven CHECK constraints for money, stock, ratings and quantities (`c67daad`).
- Verified PostgreSQL backup script (`7f1139d`).
- Daily retention command for sessions and failed jobs (`bc699a2`).
- One-command Task Scheduler registration (`3649c0b`).
- Backup verification before pruning (`6809125`) and a cron-ready POSIX script (`b246c3d`).
- WCAG 2.2 AA fixes across storefront, auth, customer, seller and admin pages (`b8a25c1`, `512eacf`).

### Phase 3 — Quality gates (10 November)

- GitHub Actions bumped to `checkout@v7` and `setup-node@v7` (`42217b7`).
- Remaining contrast fixes: seller onboarding/pending, cart quantity labels, order detail (`a5344f1`, `53b7205`, `a3ad78d`).
- Chat inbox now eager-loads only the latest message; unused eager loads removed from seller orders and the cart (`8d0177e`, `57bc95f`, `b7c9b9b`).
- Conservative security headers on every response (`db22bfe`).
- PHPStan raised to level 8 with real null-safety guards (`f17e1b0`).

### Phase 4 — Feature completion (12 November)

- Repository reorganization: grouped tests, moved scripts under `scripts/backup/`, archived old briefs, removed unused starter views (`3fe79d0`).
- Review moderation in Hive Guard (`8b57171`), with the full `ReviewPolicy` restored (`0f1192d`) and a storefront-effect test (`be86084`).
- Order oversight listing in Hive Guard (`c564485`).
- Per-user rate limits for chat (`040b1f2`) and reviews (`2394cfa`).
- Product decisions implemented: per-currency settlement and single-business sellers (`23b7fcf`).
- Figma “deliBEE UI project” theme applied, with the previous theme backed up (`b3a50d6`).

---

## 4. What was built

### Marketplace and search

- Product listing with keyword search, category and price filters, and four sort modes.
- PostgreSQL full-text search (`tsvector` weighted name/description, GIN) plus trigram fuzzy matching.
- Business storefronts, category pages and a public BeeRank page.

### Checkout and orders

- One cart across many sellers; checkout splits it into one order per seller.
- Product rows locked during checkout so concurrent checkouts cannot oversell.
- Flat shipping priced per currency; each order is settled in the seller’s currency with no FX conversion.
- Order timeline with status events, seller fulfilment actions and cancellation that restores stock and reverses purchase points exactly once.

### BeeRank and Honey Points

- Rolling 30-day score combining ratings, completion, on-time delivery and chat response time.
- Monthly and yearly snapshots, plus Rising Bees for young sellers performing well.
- Transactional ranking publication that replaces a whole period, so stale ranks cannot survive a re-publish.
- Append-only Honey Points ledger with welcome, purchase, review and refund-reversal entries.

### Reviews and moderation

- Only delivered purchases can be reviewed; one review per purchased item.
- Hive Guard lists reviews with a status filter; admins can hide or republish them. Hidden reviews stay on record but stop influencing storefront ratings and BeeRank.

### Chat

- Customer/seller conversations with per-conversation response tracking.
- Per-user rate limiting (20 messages/minute by default).
- Inbox loads only the latest message per conversation.

### Seller desk and Hive Guard

- Seller onboarding with admin approval, product management and an order-fulfilment timeline.
- One business per seller; onboarding redirects existing sellers to their desk.
- Hive Guard for businesses (approve/suspend/reactivate), orders (list, filter, open, cancel) and reviews (hide/publish).

### Identity and settings

- Fortify authentication, email verification, password reset and password confirmation.
- Role-based access for customers, sellers and admins.
- Profile, security and appearance settings.

---

## 5. Architecture

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| UI | Livewire 4 single-file pages, Flux UI, Tailwind CSS 4 |
| Database | PostgreSQL 18.6 (`tsvector`, `pg_trgm`) |
| Assets | Vite 8, Bunny-hosted Inter + Fredoka |
| Quality | Pest, Larastan level 8, Pint |
| CI | GitHub Actions with a PostgreSQL 18 service |

**Domain code** lives in `app/Models`, `app/Services`, `app/Policies` and `app/Support`. **Pages** live in `resources/views/pages` (Livewire 4 single-file components), while the public storefront components live in `app/Livewire/Storefront` with views under `resources/views/livewire/storefront`.

**Money policy:** amounts are integer minor units with a currency code. Each seller uses one currency; orders, shipping and payouts stay in that currency. The platform does not convert between currencies.

**Seller policy:** one seller account owns one business in this build. The schema keeps `businesses.owner_id` non-unique so multi-business can be added later without a destructive migration.

---

## 6. Quality and verification

| Verification | Result |
|---|---|
| Application tests | 115 — 114 passed, 1 skipped, 273 assertions |
| Static analysis | PHPStan level 8 — 0 errors |
| Coding style | Pint — clean |
| PHP dependency audit | No security advisories |
| Node dependency audit | 0 vulnerabilities |
| CI | GitHub Actions green on every push |
| Accessibility | 0 axe violations across 12+ page groups, light and dark |
| Backup restore drill | Latest dump restored into a scratch database — 28 tables, 21 migrations, all compared row counts matched |
| Restore verifier | `verify-backup.ps1` / `verify-backup.sh` automate the restore, comparison and cleanup |
| Smoke tests | `/up`, `/marketplace`, `/login`, `/ranking` — HTTP 200 |

The one skipped test is two-factor authentication, which is disabled in this build.

---

## 7. Security posture

- The application connects as the non-superuser `delibee` role with no database-creation privilege.
- All connections use `scram-sha-256`; host-based rules allow loopback only.
- No database credential is committed; `.env` is ignored and the old test password was removed from `phpunit.xml`.
- Every HTTP response sets `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin` and a restrictive `Permissions-Policy`.
- Chat and review submissions are rate-limited per user.
- Laravel policies gate seller, admin, conversation, review and order actions.

Remaining operational hardening: restrict `listen_addresses` to `localhost`, enable SSL if the database moves off the machine, add off-machine backups and point-in-time recovery.

---

## 8. Design and theme

The app shipped with two complete themes:

**Current — Figma “deliBEE UI project” theme**

- Cream paper `#FFF8E7`, hero cream `#FFF4C5`, honey `#F2C96D`, brown `#6B5745`, olive `#5B6B2E`.
- Fredoka for headings/navigation, Inter for body text.
- Soft 15 px inputs, 35 px pills, 32 px cards and a golden honeycomb motif.
- Screenshots: `docs/screenshots/figma-theme/`.

**Previous — Warm Hive theme**

- Paper `#fbf6ea`, ink `#201a10`, honey `#f2a900`, leaf `#2f5d46`.
- Fraunces display and Instrument Sans body.
- Screenshots: `docs/screenshots/`.
- Restore with `git checkout ui-backup/warm-hive` or tag `ui-warm-hive-2026-11-12`; token reference in `docs/archive/theme-warm-hive.md`.

Both themes were audited with axe-core; the current theme reports zero violations in light and dark mode.

---

## 9. Problems found and fixed

| Problem | Resolution |
|---|---|
| Hardcoded test database password in `phpunit.xml` | Removed; credentials come from `.env`/environment; history scanned |
| Checkout oversell race | Product rows locked in a stable order inside the checkout transaction |
| Points awarded through an invalid static call | Replaced with the container-resolved service; reward only on first review creation |
| Duplicate reviews and points farming | Unique index on `(user_id, order_item_id)` plus a transactional upsert |
| Stale ranking rows after re-publish | Transactional delete-and-reinsert of the whole period |
| Suspended/pending businesses visible publicly | Active-only scopes for products, rankings and storefronts |
| Chat send/mark-read 500s | Replaced `unset($this->conversation)` with a `fresh()` reload |
| Chat polling 500 every 5 s | Removed the call to a non-existent `echo` method; switched to `$refresh` |
| Pagination `aria-label` on spans | Published accessible Livewire/Laravel pagination views |
| Contrast failures on cream backgrounds | Raised muted text to accessible brown tones; re-audited light and dark |
| Vite/Composer security advisories | Updated Pest, PHPUnit, Symfony, Vite+ and rebuild verified |
| `ReviewPolicy` accidentally overwritten | Restored all original methods in `0f1192d` |
| Per-currency config cast to `1` in the checkout summary | Centralized shipping in `CheckoutService::shippingForCurrency()` |

---

## 10. Decisions made

| Decision | Choice |
|---|---|
| Currency | Per-currency settlement; shipping priced per currency; no FX conversion; payouts in the order currency |
| Seller scope | One business per seller in this build; onboarding enforces it; schema remains flexible |
| Moderation | Businesses approve/suspend; reviews hide/publish; admins can open and cancel orders |
| Retention | Sessions idle >7 days and failed jobs >30 days pruned daily; rank snapshots retained as history |
| Payment | Offline; the gateway remains an open decision |
| Ranking | Monthly publication on the 1st; annual on 1 January; transactional replacement |

---

## 11. Final state and how to run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Quality gates:

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

Backups:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\backup\backup-database.ps1
powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1
./scripts/backup/backup-database.sh --output-dir /var/backups/delibee --keep-days 30
```

Demo accounts (password `password`): `admin@delibee.test`, `customer@delibee.test`, `test@example.com`.

Repository layout highlights:

```
app/Models, Services, Policies, Support        domain logic
app/Livewire/Storefront                        public Livewire components
resources/views/pages                          Livewire 4 pages
resources/views/livewire/storefront            public component views
resources/css/app.css                          deliBEE UI theme tokens
database/migrations                            schema, search and constraints
tests/Feature/{Domain,Http,Admin,Seller,...}   test suites
scripts/backup                                 backup and scheduling scripts
docs/reports                                   progress reports
docs/screenshots/figma-theme                   current theme screenshots
docs/screenshots                               previous Warm Hive screenshots
docs/archive                                   historical notes and old theme reference
```

---

## 12. Open items

1. **Off-machine backup storage and point-in-time recovery** — both backup scripts now support a verified off-site copy (`-OffsiteDir` / `--offsite-dir` / `BACKUP_OFFSITE_DIR`) and the nightly task is registered locally. Choose the destination (OneDrive, NAS, external drive) and enable WAL archiving for production PITR; the runbook is in `docs/operations/database-operations.md`.
2. **`listen_addresses = 'localhost'`** — defense in depth; requires an elevated PostgreSQL service restart.
3. **Payment gateway** — checkout records orders without taking payment; the gateway and payout mechanics remain open.
4. **Multi-business sellers** — possible later; the schema does not prevent it, but the seller UI assumes one business today.
5. **Filament or a dedicated support desk** — only if the custom Hive Guard UI stops scaling; no work started.

---

## Appendix — key commits

| Commit | Date | Milestone |
|---|---|---|
| `2ad0e39` | 2026-11-08 | Initial commit of the complete MVP |
| `21c714a` | 2026-11-08 | README, screenshots and CI workflow |
| `0d425d3` | 2026-11-08 | Hive Guard admin businesses page |
| `c67daad` | 2026-11-09 | Database CHECK constraints |
| `7f1139d` | 2026-11-09 | Verified PostgreSQL backup script |
| `bc699a2` | 2026-11-09 | Retention for sessions and failed jobs |
| `b8a25c1` | 2026-11-09 | WCAG 2.2 AA accessibility fixes |
| `db22bfe` | 2026-11-10 | Security headers on every response |
| `f17e1b0` | 2026-11-10 | PHPStan level 8 |
| `3fe79d0` | 2026-11-12 | Repository reorganization |
| `8b57171` | 2026-11-12 | Review moderation in Hive Guard |
| `c564485` | 2026-11-12 | Order oversight in Hive Guard |
| `040b1f2` | 2026-11-12 | Chat rate limiting |
| `2394cfa` | 2026-11-12 | Review rate limiting and admin cancellation test |
| `23b7fcf` | 2026-11-12 | Per-currency settlement and single-business sellers |
| `b3a50d6` | 2026-11-12 | Figma deliBEE UI theme |
