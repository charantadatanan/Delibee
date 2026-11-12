# Warm Hive theme (previous UI)

Preserved on 12 November 2026 before switching the app to the Figma **deliBEE UI project** theme.

## Restore the previous UI

- Branch: `ui-backup/warm-hive` (pushed to GitHub)
- Tag: `ui-warm-hive-2026-11-12`
- Command: `git checkout ui-backup/warm-hive`

The old screenshots remain in `docs/screenshots/` (`marketplace.png`, `dashboard.png`, `ranking.png`, `login.png`).

## Theme tokens (old `resources/css/app.css`)

```css
--font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
--font-display: 'Fraunces', ui-serif, Georgia, 'Times New Roman', serif;

--color-paper: #fbf6ea;
--color-ink: #201a10;
--color-honey: #f2a900;
--color-honey-deep: #8a5a00;
--color-comb: #ecdfc2;
--color-leaf: #2f5d46;

--color-zinc-50: #fafafa;
--color-zinc-100: #f5f5f5;
--color-zinc-200: #e5e5e5;
--color-zinc-300: #d4d4d4;
--color-zinc-400: #a3a3a3;
--color-zinc-500: #737373;
--color-zinc-600: #525252;
--color-zinc-700: #404040;
--color-zinc-800: #262626;
--color-zinc-900: #171717;
--color-zinc-950: #0a0a0a;
```

## Fonts (old `vite.config.js`)

- Instrument Sans — 400, 500, 600
- Fraunces — 600, 700

## Hardcoded fills used by the old UI

- `#f7edd5` — dashboard, marketplace and business-profile heroes
- `#f6ecd4` — product placeholders and the sidebar tint
