# Mobile storefront motion and game-feel guide

A practical map of the motion, game-feel and UX principles used by the mobile
storefront (the marketplace below the `lg` breakpoint), with the exact code that
implements each one.

Verified against the working tree on 2026-11-26.

## 1. Purpose and how to use this guide

Use this guide when you are:

- adding, tuning or removing motion in the mobile storefront;
- reviewing whether a new screen feels consistent with the marketplace;
- auditing touch targets, focus behaviour or reduced-motion support;
- handing the mobile UI to a designer or QA tester.

How to work with it:

1. Find the principle in the table in section 3 and read the file reference.
2. Change the code at that reference. Prefer the shared tokens and keyframes in
   `resources/css/app.css` over one-off animation values.
3. Run the manual checklist in section 5 at 390px and 320px, in light and dark
   mode, and with reduced motion enabled.
4. If a token, keyframe or class name changes, update this guide in the same
   change. The code is the source of truth; this document is the map.

Convention: rows marked **(UX principle)**, **(UX pattern)** or **(visual
principle)** are not literal animation. They are layout, hierarchy or
interaction rules that the motion and screen design support. Every other row is
literal CSS motion.

### File map

| File | Role in the mobile experience |
|---|---|
| `resources/css/app.css` | Motion tokens, keyframes, mobile motion classes, reduced-motion block, `<=360px` rules, scroll padding |
| `resources/views/livewire/storefront/marketplace.blade.php` | Splash, search and filter panel, Top Products carousel, Featured Shops, Trending Now, pagination, branded closing block |
| `resources/views/components/storefront/mobile-nav.blade.php` | Fixed bottom nav, item order, centre hive, cart badge |
| `resources/views/components/storefront/mobile-nav-item.blade.php` | Individual nav bubble, `--nav-index`, arc offsets, active state |
| `resources/views/components/storefront/mobile-header.blade.php` | Sticky mobile header, animated logo, tier/join pill, chat button |
| `resources/views/components/storefront/layout.blade.php` | Mounts the mobile header and nav; reserves bottom space in `main` |
| `vite.config.js` | Loads Poppins and Fredoka from Bunny |
| `tests/Feature/Http/MobileMarketplaceTest.php` | Render coverage for the mobile sections and nav |

## 2. Motion tokens and rhythm

| Token or rule | Value | Intended feel |
|---|---|---|
| `--mobile-ease-soft` | `cubic-bezier(.22, .8, .24, 1)` | Quick departure, long settle. Used for arrivals and reveals. |
| `--mobile-ease-overshoot` | `cubic-bezier(.34, 1.56, .64, 1)` | A small rebound past the target. Used for nav rises and active bubbles. |
| Tailwind transition default | 150ms | Colour and transform changes on press/hover controls. |
| Card and tile lift | `duration-200 ease-out` | A quick lifted response before the press or hover settles. |
| Image zoom | `duration-500` | Slower follow-through on product and shop imagery. |
| Entrance durations | 450-550ms | Cards and shops 450ms; nav bar 500ms; logo and section reveals 550ms. |
| Splash | 850ms bee pop; 1350ms overlay; 400ms fade | One exaggerated arrival per tab session. |
| Ambient float | 4.5s `ease-in-out` loop | Continuous life in the centre hive. |
| Stagger | 30ms cards, 40ms shops, 55ms nav items | Reads as a cascade, not a single block. |
| Debounce | 300ms search, 500ms price inputs | Keeps typing responsive without a blocking spinner. |

Rules of the road:

- **Only animate `transform` and `opacity`.** Every custom keyframe in
  `app.css` follows this. Do not animate width, height, top or left for mobile
  motion; it causes layout work on low-end phones.
- **Use the two easing tokens.** Soft for arrivals; overshoot for nav and active
  states. Do not introduce a third curve without a reason.
- **Stagger through a custom property, and cap it.** Cards use
  `--card-index: {{ $loop->index % 9 }}` so the last rows do not wait
  indefinitely. Shops use `--shop-index`; nav items use `--nav-index`.
- **Prefer `backwards` fill for staggered entrances.** `.mobile-card-reveal` and
  `.mobile-shop-reveal` are pre-hidden during their delay but return to natural
  styles after the animation. `.mobile-reveal`, `.mobile-nav-item` and the
  splash use `both` because they should hold their end state.
- **Keep interactive motion under 550ms.** Only the 850ms splash bee, the
  1350ms splash overlay and the ambient float are longer.
- **Every new animation class must be added to the reduced-motion block.** If
  the motion is triggered by a tap, hover or focus, also add
  `motion-reduce:transition-none` at the call site.

## 3. The 19 principles, mapped

| Principle | Where it shows up | File reference | Notes |
|---|---|---|---|
| **Exaggeration** | The splash bee overshoots to 108% wide / 94% tall and rotates before settling; the active nav bubble lifts 3px and scales to 1.06; the hive bobs 5px and tilts 1 degree. | `resources/css/app.css`: `@keyframes mobile-splash-pop`, `.mobile-nav-item.is-active .nav-bubble`, `@keyframes mobile-hive-float` | Exaggeration is concentrated in one hero moment (the splash). Everything else is a small state change so the page does not feel cartoonish. |
| **Origin awareness** | The filter panel reveals (opacity and scale) from its top-right corner under the funnel button; nav items rise 16px from the bottom bar; the splash pops from screen centre; cards and sections rise 10-12px from below. | `resources/views/livewire/storefront/marketplace.blade.php` (`origin-top-right`, `x-transition`); `resources/css/app.css` (`mobile-nav-rise`, `mobile-reveal-up`, `mobile-card-in`) | Keep the transform origin and travel direction aligned with the control that triggered the change. The panel is anchored top-right because the funnel sits at the right end of the search field. |
| **Staggering** | Five nav items start at 160ms with 55ms steps; eight shop tiles step 40ms; trending cards step 30ms with the index capped at 9; section reveals offer 80/160/240ms delays (the shops and trending sections use 160/240ms). | `resources/css/app.css` (`--nav-index`, `--shop-index`, `--card-index`, `.mobile-reveal-delay-*`); `resources/views/components/storefront/mobile-nav-item.blade.php`; `marketplace.blade.php` | DOM order is the stagger order. The card cap keeps the maximum card delay at 240ms; shops peak at 280ms; nav items finish under 900ms including their 500ms rise. |
| **Squash and stretch** | The splash scales the two axes in opposite directions: `0.82/0.88`, then `0.96/1.04`, then `1.08/0.94`, then `0.98/1.02`, then `1`. Card reveals start at `scale(0.98)`; nav items rise from below. | `resources/css/app.css`: `@keyframes mobile-splash-pop`, `@keyframes mobile-card-in`, `@keyframes mobile-nav-rise` | Volume is preserved around 1.0. The splash is the only place with true squash and stretch; the rest is simple scale. |
| **Rubber banding** | The splash alternates overshoot and settle; nav items and bubble state changes use the overshoot bezier, so they pass the target and come back. | `resources/css/app.css`: `--mobile-ease-overshoot`, `@keyframes mobile-splash-pop`, `.mobile-nav-item`, `.mobile-nav-item .nav-bubble` | This is a CSS approximation, not spring physics. Keep the overshoot small (the current keyframes peak at 8%) so it reads as weight, not bounce sickness. |
| **Doherty threshold** (UX principle) | Press feedback is immediate (`active:scale-95`, `active:scale-[0.99]`, `group-active:scale-95`); entrances and reveals run in 150-550ms; search debounces at 300ms, price at 500ms; the mobile marketplace never swaps to a blocking spinner; a screen-reader-only live region announces the result count. | `marketplace.blade.php` (search, filter, carousel, trending); `resources/views/components/storefront/mobile-nav-item.blade.php`; `resources/css/app.css` | Feedback should feel instant (well under 100ms). Actual result time is network/Livewire-bound, so the design keeps the old content visible and gives immediate pressed feedback instead of a spinner. |
| **Anticipation** | The splash starts small and rotated -4 degrees before it overshoots; nav items start 16px low before they rise; cards and shop tiles lift on hover before a tap. | `resources/css/app.css`; `marketplace.blade.php`; `mobile-nav.blade.php` | The wind-up is visual only. Never delay the actual tap response to create anticipation. |
| **Fitt's Law** (UX principle) | The home hive is 64px; nav bubbles are 44px (40px at `<=360px`); the filter and carousel arrows are 40px; search is full width; the fixed action bar sits in the thumb zone. | `mobile-nav.blade.php`; `mobile-nav-item.blade.php`; `marketplace.blade.php`; `resources/css/app.css` (`@media (max-width: 360px)`) | These exceed the 24px WCAG 2.5.8 minimum. The header tier and join pills are smaller (roughly 36px and 27px high), so treat them as secondary controls. Keep the compact nav at 40px or larger. |
| **Slow in, slow out** | No entrance or press motion is linear: arrivals decelerate on the soft curve, nav uses overshoot, and the ambient float uses `ease-in-out`. | `resources/css/app.css`: `--mobile-ease-soft`, `--mobile-ease-overshoot`, `.mobile-hive` | The stagger delays are linear by design; only the easing is eased. This keeps the cascade predictable while the movement still settles. |
| **Hick's Law** (UX principle) | One funnel button hides categories, price, sort and clear; the default screen shows search, the carousel and five bottom actions; the funnel carries a count badge when filters are active. | `marketplace.blade.php` (search block, filter panel); `mobile-nav.blade.php` | The active-filter badge is important: it makes a collapsed, non-default state visible without reopening the panel. Do not add more top-level filter controls beside the funnel. |
| **Follow-through** | The hive keeps floating after its entrance; the active nav bubble stays lifted; a hovered card keeps its lift while its image zooms for 500ms; staggered children keep arriving after their section heading. | `resources/css/app.css`; `marketplace.blade.php` (lift classes, `duration-500` images); `mobile-nav.blade.php` | Follow-through is expressed as persisted state plus slower secondary motion, not as a separate exit animation. |
| **Arcs** | Per-item `margin-top` offsets of 8/0/0/8/12px fan the five nav bubbles around the raised centre hive; the splash rotation path runs -4, 1.5, -0.5, 0 degrees. | `resources/views/components/storefront/mobile-nav-item.blade.php` (`margin-top`); `mobile-nav.blade.php` (hive at `bottom-14`); `resources/css/app.css` (`mobile-splash-pop`) | The offsets create a shallow upward arc toward the centre. The `<=360px` rule removes them so the compact bar stays clean. |
| **Von Restorff effect** (UX principle) | The centre hive is 64px, elevated, ringed with a 4px paper border, shadowed and continuously floating; the other five actions are 44px bubbles. The active bubble inverts to ink. | `mobile-nav.blade.php`; `mobile-nav-item.blade.php`; `resources/css/app.css` | One dominant object is enough. Do not add a second floating or oversized action, or the hierarchy stops reading. |
| **Timing** | Interactive and entrance motion 150-550ms; splash bee 850ms; splash overlay 1350ms with a 400ms fade; ambient float 4.5s; stagger 30/40/55ms; debounce 300/500ms. | `resources/css/app.css`; `marketplace.blade.php` (splash `x-transition.opacity.duration.400ms`, `setTimeout(..., 1350)`); `mobile-header.blade.php` | Timing is the budget that holds the other principles together. Anything non-splash that needs more than 550ms should probably be split into stages instead. |
| **Interruptible motion** | Press and hover are CSS transitions, so they can reverse mid-flight; the carousel is swipeable and arrow-driven with snap points; the splash is tap-to-dismiss and auto-dismisses; reduced motion turns entrances, float, smooth scrolling and nav transforms off. | `marketplace.blade.php` (carousel `x-data`, splash `@click`); `resources/css/app.css` (reduced-motion block) | Entrance keyframes using `both` or `backwards` are not interruptible by input, which is acceptable because they are short and never block a control. Keep it that way. |
| **Secondary action** (UX pattern) | Clear filters in the panel and in the empty state, See all in Featured Shops, the View pill on Top Products, pagination, and the carousel arrows as an alternative to swiping. | `marketplace.blade.php` | Secondary actions are visually subordinate: outline button, small text link, small pill. The product card or link remains the primary target. |
| **Solid drawing** (visual principle) | Shared honey/paper/ink palette, radius tokens of 15/20/24/32px (search 10px, carousel 20px, nav bar top 34px), circular bubbles, the hex logo clip, the honeycomb ground, Fredoka headings and Poppins body. | `resources/css/app.css` (`@theme`, `.clip-hex`, `.bg-comb`); `vite.config.js` (fonts); the mobile Blade files | Consistency of weight, geometry and material stands in for drawn character animation. There is no bespoke illustration rig; the "solid drawing" is the system's shape language. |
| **Frequency of use** (UX principle) | Nav order is Shop, Rank, Cart, Profile, Settings, with Home in the centre; the most-used destinations sit in the thumb zone and Settings is last. | `mobile-nav.blade.php` (`$items` order); `mobile-nav-item.blade.php` | Visual labels are screen-reader-only, so icon choice carries the meaning. Reorder only with evidence from use, not preference. |
| **Peak-end rule** (UX principle) | The first visit opens with the splash and bee; the mobile page ends with a branded closing block after pagination: hex `d`, "Good sellers rise. The hive decides.", "You have reached the end of the hive." The hive float keeps the brand present between those points. | `marketplace.blade.php` (splash; closing block after `$products->links()`) | The closing block is inside the mobile branch and renders after the trending section, including when the empty state is shown. It is the last thing a scrolling user sees, so keep it calm and brand-consistent. |

## 4. Accessibility rules

### WCAG 2.2.2 Pause, Stop, Hide (Level A)

- The splash auto-dismisses after **1350ms** (well under the five-second
  threshold) and is also **tap-to-dismissible** (`@click="splash = false"`). It
  is shown at most once per tab session via `sessionStorage`.
- The carousel does not auto-advance, so 2.2.2 does not apply to it.
- The hive float is an **infinite decorative loop**. It stops under
  `prefers-reduced-motion: reduce`, but 2.2.2 does not formally depend on an OS
  preference. If an audit applies 2.2.2 literally, add a pause/stop control or
  let the float finish after a few cycles.
- The splash is `aria-hidden="true"` because it is decorative and short-lived.
  Keyboard users rely on the auto-dismiss; there is no Escape handler.

### WCAG 2.3.3 Animation from Interactions (Level AAA)

- Interaction-triggered motion is intentionally small: a 5% press scale, a 2px
  lift, a 3px active-bubble lift.
- Reduced motion disables nav transforms and the nav bubble transition, and
  card/shop tiles use `motion-reduce:transition-none`.
- Residual gaps at the time of writing: the arrow buttons and home hive keep
  their `active:scale-95` transition, card media keeps its hover zoom
  transition, and Alpine's filter-panel `x-transition` still runs. These are
  tiny, but if the project needs strict 2.3.3 coverage, gate them with
  `motion-reduce:transition-none` or a `matchMedia` check in the Alpine
  component.

### WCAG 2.4.11 Focus Not Obscured (Minimum, AA, new in 2.2)

- `html { scroll-padding-bottom: 6rem; }` reserves 96px when the browser scrolls
  a focused element into view.
- `main` has `pb-32` (128px) on mobile so content can clear the fixed nav even
  at the end of the page.
- The nav itself is approximately 56px tall plus `env(safe-area-inset-bottom)`.
- Verify by tabbing from the search field through every nav link and the home
  hive; the focus ring must stay fully visible above the bar.

### WCAG 2.5.8 Target Size (Minimum, AA)

- WCAG 2.2 AA requires 24x24 CSS px. Current targets are comfortably above it:
  home hive 64px, nav bubbles 44px (40px at `<=360px`), filter and carousel
  arrows 40px.
- 44px is the Apple HIG / WCAG 2.5.5 (AAA) number. The 40px compact nav passes
  AA but not the 44px AAA guidance, so do not shrink it further.
- Icon-only controls have accessible names: the funnel has
  `aria-label="Filters and sorting"`, the arrows have Previous/Next labels, the
  hive has `aria-label="Home"`, and each nav link carries an `sr-only` label.
- The filter button exposes `aria-expanded` and `aria-controls`; category chips
  use `aria-pressed`.

### Reduced-motion coverage

Covered by the `@media (prefers-reduced-motion: reduce)` block in
`resources/css/app.css`:

- all eight animation classes: bee logo, nav bar, nav items, hive float,
  section reveals, splash bee, card reveals and shop reveals;
- the nav bubble transition and the active-bubble transform;
- the carousel's smooth scroll, plus the JS `matchMedia` check in `marketplace.blade.php`
  and the `motion-reduce:scroll-auto` utility;
- the splash is not shown at all, because the init script checks
  `prefers-reduced-motion` before opening it.

Not covered, and worth checking if an accessibility audit is strict:

- `active:scale-95` transitions on the carousel arrows and the home hive
  (the nav bubbles become instant because their transition is disabled);
- image zoom transitions on card media;
- Alpine's filter-panel transition.

## 5. Manual test checklist

Run the render test first:

```bash
php artisan test tests/Feature/Http/MobileMarketplaceTest.php
```

Then open `/marketplace` in responsive mode. Use 390px and 320px widths. Clear
`sessionStorage.delibee-splash` between splash runs. Toggle light mode by
removing the `dark` class from `<html>` (Flux appearance may reapply it; DevTools
is fine for this check).

### 390px (typical phone)

- [ ] First load in a fresh tab shows the splash; it auto-dismisses after about
      1.35s and disappears immediately on tap.
- [ ] The splash is never shown again in the same tab session, and is skipped
      entirely under reduced motion.
- [ ] The header logo arrives in 550ms; the nav bar and five bubbles rise and
      stagger; the centre hive floats continuously.
- [ ] The search field is full width and focused easily; the funnel button is
      40px and toggles the panel.
- [ ] The filter panel grows from its top-right origin, has visible
      `aria-expanded` state, and the funnel badge updates when a category,
      price or sort is active.
- [ ] Clear filters resets the fields and the count; the empty state also offers
      Clear filters.
- [ ] Search updates after the 300ms debounce without a blocking spinner, and
      the live result count is announced.
- [ ] Top Products: swipe snaps cleanly; the arrow buttons are 40px, labelled,
      and show pressed feedback; the View pill is visible.
- [ ] Featured Shops: tiles arrive with a 40ms stagger, lift on hover/active,
      and logos scale gently.
- [ ] Trending Now: cards arrive with a 30ms stagger, lift and scale on
      interaction, and the three-column grid stays readable.
- [ ] Pagination works and the branded closing block follows it: hex `d`,
      tagline, "You have reached the end of the hive."
- [ ] The fixed nav never covers the closing block or focused controls.
- [ ] The cart badge, when present, matches the cart quantity on first render.
- [ ] Tab through the page: the focus ring is visible, the nav is not obscured,
      and the active destination exposes `aria-current="page"`.

### 320px and the 360px boundary

- [ ] At 320px the five bubbles are 40px, the gap tightens, the spacer is
      3.5rem, and the per-item arc offsets are removed.
- [ ] All five nav links and the home hive are tappable without overlap.
- [ ] The 3-column product grid does not cause horizontal page scroll; only the
      carousels scroll horizontally.
- [ ] Filter fields and the sort select fit without clipping.
- [ ] At exactly 360px the compact media query applies; at 361px the normal
      44px bubbles and arcs return.

### Light and dark

- [ ] Repeat the key checks after removing the `dark` class: splash, logo and
      icon inversion, search placeholder, active nav bubble, filter panel,
      empty state and closing block.
- [ ] Focus rings remain visible against honey, paper and ink surfaces.
- [ ] Images that use `dark:brightness-0 dark:invert` still read correctly.

### Reduced motion

- [ ] Emulate `prefers-reduced-motion: reduce` in DevTools and reload: no
      splash, no logo/nav/reveal/card/shop entrances, no hive float.
- [ ] The active nav bubble is not lifted or scaled.
- [ ] Carousel arrows jump instead of smooth-scrolling; manual swipes still work.
- [ ] All content is visible immediately (no stuck opacity or transform).
- [ ] Residual micro-motion (press scales, Alpine panel transition) is noted; if
      the audit requires it, gate those too.

### Network and Doherty

- [ ] Throttle to Fast 3G and type in search: pressed feedback is immediate,
      there is no blocking spinner, and previous results stay visible until the
      new ones arrive.
- [ ] Toggle a filter with a slow connection: the funnel badge and result count
      reflect the state, and the layout does not jump to a spinner screen.

## 6. Known limitations

- **Raster asset weight.** The mobile art is PNG and heavy for its rendered
  size: `bee-welcome.png` 172KB at 256px, `bee-logo.png` 104KB at 147x70,
  `chat.png` 72KB at 22px, `search.png` 48KB at 20px, `hive.png` 36KB at 36px.
  There is no `srcset`. The flat icons and logo would benefit from SVG; the
  splash art from WebP/AVIF with a `srcset`. The header logo should get
  `fetchpriority="high"`. All carousel images use `loading="lazy"`, including
  the first above-the-fold card, which can delay LCP.
- **Duplicate mobile and desktop DOM.** The Livewire view renders both branches
  (`lg:hidden` and `hidden lg:block`) in one response, so product loops, links
  and queries are duplicated. The hidden branch is `display: none` and is not
  exposed to assistive technology, and `wire:key` prefixes are distinct
  (`top-`, `shop-`, `trend-`, `desktop-product-`), but the HTML weight and
  initial render work are doubled. Breakpoint-specific rendering would remove
  that cost if it becomes a problem.
- **Cart badge refresh.** `mobile-nav.blade.php` computes the count
  server-side when the layout renders. The fixed nav sits outside the Livewire
  component and has no event listener, so quantity changes on `/cart` do not
  update the badge until the next `wire:navigate` or full page render. The
  desktop header uses `($cartCount ?? 0)`, but the layout never defines
  `$cartCount`, so that badge stays hidden unless a shared variable is added.
- **Reduced-motion coverage is not total.** Entrances, the float, nav
  transforms and smooth scrolling are covered. Small press scales, card media
  zooms and Alpine's filter transition are not. The hive float also runs
  indefinitely unless the OS preference is set; strict WCAG 2.2.2 compliance
  would need an explicit pause/stop mechanism.
- **Splash dismissal is pointer-only.** The overlay is `aria-hidden` and
  dismissible by tap; keyboard users depend on the 1.35s auto-dismiss. There is
  no Escape handler. If `sessionStorage` access throws, the whole init is caught
  and the splash is skipped.
- **Selectors drift.** This guide references class and keyframe names rather
  than line numbers so it survives small edits. If a name changes, update the
  corresponding row in section 3 and the reduced-motion list in section 2.

See also: `docs/guides/code-map.md` for the wider file and theme map, and
`tests/Feature/Http/MobileMarketplaceTest.php` for the current render coverage.
