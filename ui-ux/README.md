# deliBEE UI/UX starter kit

A self-contained handoff pack for the UI/UX team: one static style-guide page plus
copies of the real Blade and CSS files that drive the marketplace interface.

Open `preview.html` in any browser. It needs no Laravel, no Node and no build step.

## What is in here

```
ui-ux/
├── preview.html          the sampler: tokens, type, components, page patterns
├── preview.css           hand-written CSS for the sampler (imports theme/tokens.css)
├── preview.js            light/dark toggle only
├── theme/
│   └── tokens.css        plain-CSS copy of every theme token (light + dark)
└── samples/
    ├── README.md         manifest: what each copied source file shows
    ├── app.css           the real Tailwind theme source
    └── *.blade.php       real layout, component and page markup
```

## Quick start

1. Double-click `preview.html`, or drag it into a browser.
2. Use the **Dark mode** button in the top bar to check both themes.
3. Read `samples/README.md` before editing anything — it maps each copied file to
   the file it came from in the app.

Fonts load from `fonts.bunny.net` (the same host the app uses). Without an internet
connection the page falls back to system fonts; everything else is local.

Optional, if you prefer serving it:

```bash
python -m http.server 8080 --directory ui-ux
# then open http://localhost:8080/preview.html
```

## Theme at a glance

| Token | Value | Tailwind | Used for |
|---|---|---|---|
| Paper | `#FFF8E7` | `bg-paper` | page background |
| Hero | `#FFF4C5` | `bg-[#FFF4C5]` | hero and product-media surfaces |
| Ink | `#1E1E1E` | `bg-ink` / `text-ink` | text, primary buttons |
| Honey | `#F2C96D` | `bg-honey` | accent, hex marks, badges |
| Honey deep | `#6B5745` | `text-honey-deep` | links, muted headings |
| Comb | `#E6DCC4` | `border-comb` | borders, dividers |
| Leaf | `#5B6B2E` | `text-leaf` | delivered / success |
| Zinc 500 | `#7A6852` | `text-zinc-500` | light-mode subtle text |
| Zinc 400 | `#B3A488` | `text-zinc-400` | dark-mode muted text |
| Zinc 950 | `#12100D` | `bg-zinc-950` | dark-mode page background |

- **Display font:** Fredoka, weights 400–700. Used automatically for `h1`/`h2` and
  explicitly via `font-display` for product names and big numbers.
- **Body font:** Inter, weights 400–600.
- **Radius scale:** `15px` buttons/inputs, `20px` small panels, `24px` cards,
  `32px` heroes.
- **Texture:** `bg-comb` is a honeycomb SVG at 18% fill; use it behind heroes and
  media areas at 50–70% (light) and 10–15% (dark), never as page decoration.

The same values are available as plain CSS custom properties in
`theme/tokens.css` — use those in HTML prototypes that do not run Tailwind.

## Rules of the system

1. **Tokens or nothing.** Use `bg-paper`, `text-zinc-600`, `border-comb`, etc.
   Avoid raw hex values except for photo surfaces and status tints already in use.
2. **Every colour has a dark pair.** If a component uses `bg-white/70`, it also
   needs `dark:bg-zinc-900/70`; text colours need `dark:text-zinc-400` or lighter.
3. **Honey is not text.** Honey sits under ink. For honey-coloured text on dark
   surfaces, use `dark:text-honey`; on light surfaces use `text-honey-deep`.
4. **One display voice.** Fredoka for headings, product names and stat numbers;
   Inter for everything else. Do not set body copy in Fredoka.
5. **Borders are comb.** Cards use `border-comb/90`; on hover they warm to
   `border-honey-deep/40`. Dark mode swaps to `zinc-800` / `honey/40`.
6. **Focus stays visible.** Controls use the honey ring, page links use the
   `:focus-visible` outline. Never remove a focus indicator.
7. **Contrast floor.** Zinc 500 is the lightest muted text allowed on paper;
   zinc 400 is for dark mode only (or non-text marks).
8. **Status colour has meaning.** Honey = pending, sky = in transit, leaf =
   delivered/active, zinc = cancelled/draft, ink = sold out. Do not reuse them
   for decoration.

## Where the real code lives

| Concern | Path |
|---|---|
| Theme tokens, honeycomb texture, focus/selection | `resources/css/app.css` |
| Fonts (Bunny) | `vite.config.js` (`bunny(...)` calls) |
| Storefront shell (header, footer, toast group) | `resources/views/components/storefront/layout.blade.php` |
| Product card | `resources/views/components/storefront/product-card.blade.php` |
| Dashboard shell and sidebar | `resources/views/layouts/app.blade.php`, `resources/views/layouts/app/sidebar.blade.php` |
| Marketplace page and filters | `resources/views/livewire/storefront/marketplace.blade.php` |
| Customer pages | `resources/views/pages/` |
| Auth pages | `resources/views/pages/auth/` |
| Seller pages | `resources/views/pages/seller/` |
| Hive Guard (admin) pages | `resources/views/pages/admin/` |
| Pagination (Livewire + category page) | `resources/views/vendor/livewire/`, `resources/views/vendor/pagination/` |

For a full file-by-file map (routes, domain services, tests, scripts), see
[`docs/guides/code-map.md`](../docs/guides/code-map.md).

## Keeping the sampler honest

`resources/css/app.css` is the **source of truth**. This folder is a snapshot
taken on 14 November 2026. When a token or component changes in the app:

1. update `theme/tokens.css` and the matching part of `preview.css` / `preview.html`;
2. re-copy the changed Blade file into `samples/`;
3. update the date in the sampler top bar and in this README.

The `samples/` copies are for reading and prototyping. If the team edits them,
the changes must be ported back into the real files above — editing a copy has
no effect on the app.

## Known gaps

- **Flux-only surfaces are not reproduced:** auth pages, settings, toasts and the
  mobile slide-over menu come from Flux. Their look should be taken from the
  running app.
- **Breakpoints are approximated** in the static preview; verify responsive
  behaviour in the app at 640 / 768 / 1024 / 1280px.
- The cart icon is the same emoji the app uses; it is intentionally not an SVG.

## Sending it to the team

From the repository root on Windows PowerShell:

```powershell
Compress-Archive -Path ui-ux -DestinationPath delibee-ui-ux.zip -Force
```

On macOS/Linux:

```bash
zip -r delibee-ui-ux.zip ui-ux
```

The folder contains no credentials, environment files or database dumps.
