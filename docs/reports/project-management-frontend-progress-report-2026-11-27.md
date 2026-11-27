# deliBEE — Project Management + Custom Front-end Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Report date:** 27 November 2026
**Scope:** Project management and custom front-end — design system, Figma theme, responsive storefront, mobile navigation, accessibility/motion, documentation
**Production:** https://delibee-production-6l26tu.laravel.cloud
**Status:** Live on Laravel Cloud — light/dark theme toggle included in this release; external integrations open

---

## 1. Progress

**Milestones.** The repository stood at **72 commits** at audit time, HEAD `4988c76` (27 November 2026, bee-mark favicons). This release adds the light/dark theme toggle, its auth-page mount, the grouped progress reports and the theme screenshots. The front-end work since the 12 November MVP report moved through three phases:

- **13–14 November** — pagination themed on the honey tokens (`3d638b4`), dark focus contrast raised, Bee Chat search fixed at narrow widths (`abe5a1e`), and the UI/UX sampler synced with the app (`095aedd`).
- **26 November** — the Figma mobile storefront and bright-honey theme landed (`5fcc4b0`), followed by mobile motion/accessibility polish (`ba3521f`), nav/header refinements (`a251d7f`, `2da2ee3`), and the motion guide plus design-kit sync (`5eb339f`).
- **27 November** — RAFS authentication ported with a full UI refresh (`eddffed`), store hero brought into the hive palette with new screenshots (`59975fa`), mobile navigation evolved from a labelled menu to a hive-revealed radial menu (`c1b8a6a`, `8894475`, `f8894ad`, `a4ec704`), Laravel Cloud deployment prepared and documented (`da56436`, `34ea9ec`), demo seeding made production-safe (`cf7f43d`), Resend added (`d6c2f54`), social sign-in marked under development (`13a293d`), and the bee mark adopted for favicons (`4988c76`).

**Project management and documentation.** `README.md` is the entry point: live URL, CI badge, desktop/mobile screenshot tables, stack, setup, demo accounts, admin operations, backups, quality gates, business rules, hosting and current status. `docs/README.md` indexes the documentation set. New or refreshed guides are `docs/guides/figma-122-screens.md` (Figma coverage and route map), `docs/guides/mobile-motion.md` (motion principles, accessibility rules, manual test checklist), `docs/guides/laravel-cloud.md` (production infrastructure, build/deploy, environment variables, first-deploy checklist), `docs/guides/rafs-auth.md`, and `docs/guides/code-map.md`. The design team has a standalone handoff pack in `ui-ux/README.md`, `ui-ux/preview.html`, `ui-ux/theme/tokens.css` and `ui-ux/samples/`.

**CI.** `.github/workflows/ci.yml` runs on every push to `main` and every pull request. It starts a PostgreSQL 18 service, installs PHP 8.4 and Node 24, audits Composer dependencies, runs `npm ci` and `npm run build`, audits Node dependencies, then runs Pint, PHPStan and the full test suite. The README carries the workflow badge.

**Design system.** `resources/css/app.css` is the source of truth: paper `#FFF8E7`, hero `#FFF4C5`, ink `#1E1E1E`, honey `#FFC233`, honey-deep `#6B5745`, comb `#E6DCC4`, leaf `#5B6B2E`, a warm-brown zinc scale, and radii of 15/20/24/32 px. Poppins is the body face and Fredoka the display face, loaded from Bunny through `vite.config.js`. Shared pieces live in `resources/views/components/ui/` — `bee-logo.blade.php`, `hills.blade.php`, `mobile-header.blade.php`, `order-status.blade.php`, `seller-mobile-nav.blade.php`, `admin-mobile-nav.blade.php` and `empty-state.blade.php` — with the four Honey tiers in `app/Support/BeeTier.php`. The UI/UX kit records the token rules: every colour has a dark pair, honey is not used for text on light surfaces, focus stays visible, and muted text does not fall below zinc 500 on paper.

**Figma theme.** The deliBEE UI project theme is implemented across storefront, auth, customer, seller and admin pages. `docs/guides/figma-122-screens.md` maps **122 Figma node links to 36 distinct 390×844 screens** and records the route/view for each screen group (entry, storefront, seller desk), plus the shared parts (tier badges, product wireframes, lines, buttons). Payments, KYC/payout capture, rider tracking and saved cards are explicitly deferred rather than faked. The previous Warm Hive theme remains recoverable from the `ui-backup/warm-hive` branch and `ui-warm-hive-2026-11-12` tag, with token notes in `docs/archive/theme-warm-hive.md`; older screenshots are kept in `docs/screenshots/` and `docs/screenshots/figma-theme/`.

**Official bee logo.** The official deliBEE bee mark is reproduced from Figma node `169:299` (Frame 4). `resources/views/components/ui/bee-logo.blade.php` renders the full lockup (mark plus Fredoka wordmark) and supports `variant="mark"` for the icon alone, using `public/images/brand/deliBEE-mark.png` (with `deliBEE-mark-small.png`). Commit `4988c76` replaced the generic favicons with the bee mark: `public/favicon.ico`, `public/apple-touch-icon.png`, `public/images/brand/favicon-32.png` and `public/images/brand/favicon-192.png`, wired in `resources/views/partials/head.blade.php` with cache-busting `?v=2`.

**Responsive desktop/mobile.** `resources/views/components/storefront/layout.blade.php` renders a desktop header above the `lg` breakpoint and delegates below it to `resources/views/components/storefront/mobile-header.blade.php`; signed-in customer pages use the shared `resources/views/components/ui/mobile-header.blade.php`, while the app shell uses the Flux sidebar and its mobile header in `resources/views/layouts/app/sidebar.blade.php`. Screenshots cover desktop home, login, seller dashboard/analytics/store and mobile home, orders, product, profile, settings, store, chat, admin, credits and seller pages. `docs/guides/mobile-motion.md` documents manual checks for the mobile branch at **390 px and 320 px**, in light and dark mode, and with reduced motion; it also records known limitations such as the duplicated mobile/desktop DOM and raster asset weight.

**Mobile radial navigation.** `resources/views/components/storefront/mobile-nav.blade.php` replaces the flat bottom bar with a hive button that fans out five labelled actions — Shop, Rank, Cart, Profile, Settings — on a radial arc. `resources/views/components/storefront/mobile-nav-item.blade.php` supplies each bubble, active state and cart badge. The CSS in `resources/css/app.css` uses a 6.5 rem radius below 380 px and 7.5 rem above it, 35 ms per-item stagger and a 420 ms overshoot transition. The menu opens with a scrim, closes on scrim click or Escape, exposes `aria-expanded`, and keeps the closed list `inert` and `aria-hidden`. Screenshot: `screens/mobile-radial-open.png`; commit `a4ec704`.

**Light/dark theme toggle.** `resources/views/components/ui/theme-toggle.blade.php` is a 40 px circular button that uses Flux's reactive `$flux.dark` getter to set `$flux.appearance` to light or dark, swaps moon/sun icons, and exposes an `aria-pressed` state plus the accessible name “Toggle light and dark theme”. It is mounted in the storefront desktop header, the storefront mobile header, the shared customer mobile header, the app sidebar/admin mobile bar and the auth layout — five component files, six placements. Screenshots: `screens/login-dark-toggle.png`, `screens/mobile-theme-toggle-320.png` and `screens/mobile-subpage-theme-toggle.png`. The component and wiring are part of this release.

**Screenshots.** `screens/` holds desktop and mobile shots, including the three theme-toggle screenshots. The README embeds the desktop marketplace, login and seller dashboard plus the mobile marketplace, orders, product, radial navigation, profile and seller inventory; `screens/mobile-radial-open.png` documents the radial menu and the theme-toggle shots document the new control.

**Accessibility and motion.** The README records **zero axe-core violations** across storefront, customer, seller, admin and settings pages when audited against WCAG 2.2 AA, in light and dark mode. `docs/guides/mobile-motion.md` documents 19 motion/UX principles mapped to code, with specific rules for WCAG 2.2.2 (pause/stop/hide), 2.3.3 (animation from interactions), 2.4.11 (focus not obscured) and 2.5.8 (target size), plus reduced-motion coverage and a manual checklist. `resources/css/app.css` contains the `prefers-reduced-motion` block; the guide also notes residual micro-motion (press scales, card image zoom, Alpine filter transition) that is not yet gated. `tests/Feature/Http/MobileMarketplaceTest.php` provides render coverage.

---

## 2. Final output

### Live at https://delibee-production-6l26tu.laravel.cloud

The site is deployed from `main` with push-to-deploy enabled; this release adds the light/dark theme toggle and the grouped reports. The production environment is documented in `docs/guides/laravel-cloud.md`:

| Item | Value |
|---|---|
| Application / environment | `delibee` / `production` |
| Region | Singapore (`ap-southeast-1`) |
| Runtime | PHP 8.5, Node 24 |
| Compute | `flex-512mb`, 1 replica, hibernation enabled |
| Database | Laravel Serverless Postgres `delibee-db`, schema `production` |
| Object storage | Public bucket `delibee-public` as the default `public` disk |
| Scheduler / queue | Scheduler enabled; database queue driver |

Smoke checks returned `/up` 200, `/marketplace` 200, `/login` 200 and `/ranking` 200; `/` returns 302 to `/marketplace`. The live build includes the Figma storefront theme, the radial mobile navigation, the RAFS auth screens with disabled “under development” social buttons on `/login` and `/register`, the bee-mark favicons, the light/dark theme toggle and the production-safe database seeding.

### In code

- The complete app: storefront (`app/Livewire/Storefront/`, `resources/views/livewire/storefront/`), Livewire single-file pages (`resources/views/pages/`), app shell (`resources/views/layouts/app/`), auth (`resources/views/pages/auth/`) and Hive Guard (`resources/views/pages/admin/`).
- The design system and all Figma-derived components listed above, committed and deployed.
- The theme toggle and its five component files / six placements, committed and deployed with this release.
- CI, deployment documentation, the Figma route map, the motion guide, the UI/UX sampler and the screenshot set.

> Release note: the theme toggle, its screenshots and the five grouped reports ship together.

---

## 3. Verification

| Check | Command | Result |
|---|---|---|
| Tests | `php artisan test` | **123 tests — 122 passed, 1 skipped, 316 assertions** |
| Coding style | `vendor/bin/pint --test` | Passed |
| Static analysis | `vendor/bin/phpstan analyse --no-progress` | Passed — 0 errors (level 8) |
| Front-end build | `npm run build` | Passed — `app.css` 463.05 kB (48.72 kB gzip); Poppins and Fredoka assets emitted |
| CI | `.github/workflows/ci.yml` | PostgreSQL 18, PHP 8.4, Node 24; Composer/npm audits, Pint, PHPStan and tests on push/PR |
| Live smoke | `curl` | `/up`, `/marketplace`, `/login`, `/ranking` = 200; `/` = 302 → `/marketplace` |

The suite, Pint, PHPStan and Vite build were run against the release candidate on 27 November 2026. An initial full-suite run overlapped another run on the shared `delibee_test` database and hit PostgreSQL relation/deadlock errors; an isolated rerun returned the results above. The single skipped test is two-factor authentication, which is disabled in this build.

---

## 4. Open items

1. **Social sign-in is under development.** `resources/views/components/auth/social-buttons.blade.php` renders disabled Google, Facebook and Apple buttons with an explicit “under development” note. `app/Http/Controllers/Auth/SocialAuthController.php` implements Google and Facebook OAuth through Socialite, but no provider credentials are configured (`.env.example` leaves `GOOGLE_*` and `FACEBOOK_*` empty) and Apple has no provider in the controller. Closing it needs OAuth apps, enabled buttons, callback tests and a policy for linking existing accounts.
2. **Email delivery is live.** Production now uses Gmail SMTP (`smtp.gmail.com:465`) with the app password stored as the Cloud secret `MAIL_PASSWORD`; a live test email and registration OTP were confirmed. Resend remains an optional future transport and needs an owned, verified domain (`docs/guides/laravel-cloud.md`).
3. **Custom domain is optional.** The app is fully usable on the generated `delibee-production-6l26tu.laravel.cloud` hostname. `docs/guides/laravel-cloud.md` leaves “Custom domain and DNS configured” unchecked; buying a domain and configuring DNS/TLS is branding work, not a blocker.
4. **Payments remain on hold.** Checkout records orders and shows the payment-on-hold state; there is no gateway or payout flow, and KYC/payout capture is explicitly deferred in `docs/guides/figma-122-screens.md`. It needs a product decision, gateway integration and payout mechanics before launch.

---

## Summary

The Project Management + Custom Front-end category is complete through the design system, the Figma 122-link/36-screen mapping, the official bee identity, responsive desktop/mobile layouts, the radial mobile navigation, documentation, CI and a live Laravel Cloud deployment. Verification is green: 123 tests (122 passed, 1 skipped, 316 assertions), Pint, PHPStan and the Vite build all pass. The theme toggle and email delivery are deployed. The remaining work is social sign-in, the optional custom domain and the on-hold payment gateway.
