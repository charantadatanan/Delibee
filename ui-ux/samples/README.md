# Source samples

Copies of the real files that produce the interface in `../preview.html`. They were
copied from the app on 12 November 2026 and are here so the UI/UX team can read
and prototype against the actual markup.

## Manifest

| Copy | Original path | What it shows |
|---|---|---|
| `app.css` | `resources/css/app.css` | Tailwind `@theme` tokens, radius scale, honeycomb texture, focus and selection styles, Flux sidebar overrides |
| `storefront-layout.blade.php` | `resources/views/components/storefront/layout.blade.php` | Public shell: sticky header, primary nav, cart badge, footer, toast group |
| `product-card.blade.php` | `resources/views/components/storefront/product-card.blade.php` | Product card with image/hex fallback, sold-out pill, seller line, hover states |
| `app-sidebar.blade.php` | `resources/views/layouts/app/sidebar.blade.php` | Signed-in shell: Flux sidebar, role-based groups (Platform / Seller / Hive Guard), mobile header |
| `marketplace.blade.php` | `resources/views/livewire/storefront/marketplace.blade.php` | Marketplace hero, filter bar, product grid, empty state, pagination call |
| `dashboard.blade.php` | `resources/views/pages/dashboard.blade.php` | Welcome hero, stat cards, recent-orders panel with status pills |
| `rewards.blade.php` | `resources/views/pages/rewards.blade.php` | Honey balance hero and ledger list |
| `chat-index.blade.php` | `resources/views/pages/chat/index.blade.php` | Conversation rows, avatars, unread dot, empty state |
| `auth-login.blade.php` | `resources/views/pages/auth/login.blade.php` | Auth card layout built on the Flux auth components |
| `pagination-tailwind.blade.php` | `resources/views/vendor/pagination/tailwind.blade.php` | Laravel's default pagination markup — currently unthemed, a first task |
| `pagination-simple.blade.php` | `resources/views/vendor/pagination/simple-tailwind.blade.php` | The simplified previous/next variant of the same |

## How to read a Blade file

Blade is HTML with a thin PHP layer. For design work:

- `class="..."` is everything that styles the element. Classes prefixed `dark:`
  are the dark-mode variants.
- `{{ $thing }}` echoes data — read it as placeholder copy.
- `@if`, `@foreach`, `@auth` are server-side conditions/loops. They show which
  states exist (empty, sold out, admin-only, and so on).
- `wire:*` attributes are Livewire behaviour (clicks, search, loading states).
  They do not change the visual rules.
- `$slot` is where a layout injects the page content.
- `<flux:...>` are vendor UI components. Override them with wrapper classes and
  the token rules — never edit files inside `vendor/`.

## What is editable

The copies in this folder are **reference only**. Real changes belong in the
original paths above, then the changed file is copied back here so the pack stays
honest. If the team wants to prototype directly, keeping the same class names
means the result can be ported one-to-one into the Blade file.
