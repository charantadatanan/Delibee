# deliBEE — Products & Inventory Admin Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Report date:** 27 November 2026
**Production:** https://delibee-production-6l26tu.laravel.cloud
**Status:** Live with a seeded demo catalogue; seller photo upload remains open

---

## Progress

**Seller onboarding and approval.** A seller opens `/seller/onboarding`, submits a business
profile and waits for Hive Guard approval. `app/Http/Middleware/EnsureSeller.php` gates the seller
area; pending and suspended businesses are redirected to `/seller/pending`. Hive Guard can approve
pending shops, suspend active shops and reactivate suspended ones in
`resources/views/pages/admin/businesses.blade.php`.

**Seller desk.** The seller area is implemented as Livewire single-file pages:

- `resources/views/pages/seller/dashboard.blade.php` — overview, recent orders and restock alerts.
- `resources/views/pages/seller/analytics.blade.php` — revenue, orders and product performance.
- `resources/views/pages/seller/products.blade.php` — create, edit, price, stock and status
  management for the seller's own catalogue.
- `resources/views/pages/seller/orders.blade.php` — fulfilment timeline per order.

Ownership is enforced in the component queries; a seller only sees products and orders belonging
to their own business.

**Inventory and stock.** Products have a price in the business currency, stock, category, slug and
active/inactive status (`app/Models/Product.php`, `database/migrations/2026_10_21_000400_create_products_table.php`).
Stock is reduced under `lockForUpdate` during checkout and restored when an order is cancelled
(`app/Services/CheckoutService.php`). Restock alerts surface low-stock products on the seller
dashboard.

**Product imagery.** Product cards and detail pages display the first related
`product_images` row (`resources/views/components/storefront/product-card.blade.php`,
`resources/views/livewire/storefront/product-details.blade.php`). The demo catalogue uses six
licensed Wikimedia Commons stand-in photographs, attached by
`database/seeders/DemoPhotoSeeder.php`; attribution is on `/credits`. Cloud Object Storage
(`delibee-public`, default `public` disk) serves the images in production.

**Hive Guard moderation.** Admins can approve, suspend and reactivate businesses, review every
order, and hide or republish reviews from `resources/views/pages/admin/*.blade.php`. Suspended
businesses and their products are excluded from storefront and ranking queries.

**Catalogue and empty states.** Production currently holds 5 businesses (4 active, 1 pending),
20 products and 20 images. Storefront and seller pages include designed empty states
(`resources/views/components/ui/empty-state.blade.php`) rather than blank panels.

## Final output

The seller desk, inventory pages and Hive Guard are deployed on Laravel Cloud and share the same
Postgres database as the storefront. The demo sellers can sign in, see their inventory, inspect
orders and view analytics; the demo admin can approve the pending shop and moderate reviews.

## Verification

| Check | Result |
|---|---|
| Seller routes | `/seller/dashboard`, `/seller/products`, `/seller/orders`, `/seller/analytics` — HTTP 200 as a demo seller on production |
| Hive Guard routes | `/admin/businesses`, `/admin/orders`, `/admin/reviews` — HTTP 200 as a demo admin on production |
| Storage | Bucket-hosted product images and a write/read/delete round-trip verified |
| Automated suite | 123 tests, 122 passed, 1 skipped, 315 assertions; Pint clean; PHPStan 0 errors |
| Build | Vite production build succeeds |

## Open items

1. **Seller product-photo upload is not implemented.** Sellers can create and edit products but
   cannot upload a new image in this build; all current photos come from the demo seeder. Add a
   `WithFileUploads` field on the seller product form, validate image type/size, write to the
   default `public` disk and create/update a `ProductImage` row.
2. **Demo catalogue is test data.** Five businesses, 20 products and the demo accounts should be
   removed or replaced before a real launch.
3. **No custom domain.** The live app uses its `laravel.cloud` hostname; DNS/TLS for a branded
   domain is pending.
