# deliBEE Figma screen coverage

Source: [deliBEE UI project](https://www.figma.com/design/F3Lc10rrvlDUVjkbHA7UBp/deliBEE-UI-project), page **UI DESIGN**.
The 122 node links resolve to 36 distinct 390×844 screens (plus shared parts: tier badges, product
wireframes, lines and buttons). The implementation below keeps every existing feature and maps the
Figma screens onto real routes.

## Design system

| Token / part | Where it lives |
| --- | --- |
| Cream `#FFF8E7`, hero `#FFF4C5`, honey `#FFC233`, ink `#1E1E1E`, comb `#E6DCC4`, leaf `#5B6B2E` | `resources/css/app.css` `@theme` |
| Poppins body / Fredoka display | `resources/css/app.css` |
| Cream card + 1px ink outline (`.ui-card`) | `resources/css/app.css` |
| Honey pill button with press shadow (`.ui-btn`) | `resources/css/app.css` |
| Outlined fields (`.ui-field`, Flux control overrides) | `resources/css/app.css` |
| Back / honey circle (`.ui-back`), honey menu row (`.ui-row`) | `resources/css/app.css` |
| Hills illustration | `components/ui/hills.blade.php` |
| Bee logo | `components/ui/bee-logo.blade.php` — full lockup from Figma node `169:299` (Frame 4) with the `Frame 3` bee/cart mark as `images/brand/deliBEE-mark.png`; `variant="mark"` renders the icon alone |
| Mobile page header | `components/ui/mobile-header.blade.php` |
| Status badge | `components/ui/order-status.blade.php` |
| Seller bottom navigation | `components/ui/seller-mobile-nav.blade.php` |
| Four Honey tiers | `app/Support/BeeTier.php` |

The cream theme is the default now (the old forced `class="dark"` was removed); the appearance setting
still switches light / dark / system.

## Screen map

### Entry and onboarding

| Figma frame | Route / view |
| --- | --- |
| `spalash` (1:3), `WELCOME PAGE ANIMATION` (213:76) | existing mobile splash in `livewire/storefront/marketplace.blade.php` |
| `iPhone 13 & 14 - 2` welcome (113:14) | splash / onboarding art |
| `login page` (113:26) | `/login` |
| `sing up page` (113:52) | `/register` |
| `Forget Password` (427:35) | `/forgot-password`, `/reset-password/{token}` |
| `Seller sing up page` (584:26), `STEP2/STEP3/OTP` (587:124, 586:83, 589:167) | `/seller/onboarding` + `/seller/pending` — step 1 and review states; KYC/payout capture is deferred because payments are on hold |

### Storefront

| Figma frame | Route / view |
| --- | --- |
| `home page` / `home page button` (113:89, 472:11, 474:440) | `/marketplace` — Top Products carousel, Featured Shops, Trending Now, floating honey nav |
| `store profile` (228:31), `about` (351:9), `reviews` (363:7) | `/businesses/{business}` — blue store hero, Products/About/Reviews tabs, rating breakdown |
| product page (525:12) and product wireframes | `/businesses/{business}/products/{product}` — quantity stepper and Add to Cart |
| `cart` (470:207) | `/cart` — one card per seller, quantity stepper, per-currency totals |
| `checkout` (671:207) | `/checkout` — delivery address, per-seller items, payment-on-hold card, totals |
| `Order Confirmed` (742:307) | `/orders?placed=1&order=...` confirmation card after checkout |
| `My Order` / `Track Order` (470:150, 742:456) | `/orders`, `/orders/{order}` — status tabs, timeline, items, review form, cancel |
| `Profile`, `profile 2`, `profile 3`, `Edit Profile` (295:50, 311:57, 470:7, 315:115) | `/dashboard`, `/settings/profile` — identity, tier, menu, recent orders, bio/phone edit |
| `settings` (299:132) | `/settings` plus `/settings/profile`, `/settings/security`, `/settings/appearance` |
| `LeaderBoards` (474:556) | `/ranking` — podium and ranked list |
| `Need Help` (546:62) | `/help` — Bee Chat, order help, delivery-issue guidance |
| tier badges (311:115/129/132/135) | Larva / Forager / Courtier / Queen, `app/Support/BeeTier.php` and `/rewards` |
| `reviews` (363:7) | `/reviews` — the customer's own ratings |

### Seller desk

| Figma frame | Route / view |
| --- | --- |
| `Dashboard` (592:347) | `/seller/dashboard` — stat cards, seven-day sales, stock alerts, recent orders |
| `Analytics` / `Line chart` (616:93, 610:8) | `/seller/analytics` — 7D/30D/3M/1Y, revenue, top products |
| `Inventory` (610:174, 610:341) | `/seller/products` — product cards, low-stock banner, restock panel, add product |
| `Add New Product` / `Confirm Restock` (742:75, 742:199) | restock panel in `/seller/products` (opens with `?restock={id}`) |
| seller orders | `/seller/orders` — status filters and one-step fulfilment |
| `ADMIN PAGES` label (583:21) | existing Hive Guard admin routes kept on the shared shell |

## Intentionally deferred

- **Payments** stay on hold, as agreed: checkout records the order, shows the payment-on-hold state,
  and collects no card details or e-wallet data.
- **KYC / payout step 3**, rider map/rider contact, saved addresses, saved items and card management
  need new tables/fields and were not faked. The settings/profile entries are marked “coming soon”,
  “set at checkout” or “payments on hold”.
- Product photos in the Figma mockups are brand/product art. The seeded demo listings now use six
  licensed Wikimedia Commons photographs as stand-ins, attached through `product_images`; the footer
  links to `/credits` with photographer and licence details. A real seller image-upload workflow is
  still to build, and any listing without an image keeps the honeycomb initials fallback.

## Checks

```bash
php artisan test          # 120 tests, 119 passed / 1 skipped
vendor/bin/phpstan analyse --no-progress
vendor/bin/pint --parallel --test
npm run build
```

Desktop and mobile screenshots: `screens/final-*.png`.
