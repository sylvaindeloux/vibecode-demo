# Foundations

Every visual value of the CRA app is a design token. This file lists them with their role.
Source of truth: `tokens/tokens.json`; CSS exposure: `tokens/tokens.css` (copied to `assets/styles/tokens.css`).

## How tokens work

- **Token name = CSS custom property name.** `color-primary` in `tokens.json` is `var(--color-primary)` in CSS.
- **Two tiers.**
  - *Primitives* (`primitive.*` in tokens.json): raw values named by value (`teal-600`, `space-4`, `font-size-lg`, `size-mm-20`). They exist only to be referenced by semantic tokens. **Never** write `var(--teal-600)` or `var(--space-4)` outside `tokens.css`.
  - *Semantic tokens* (`semantic.*`): named by usage (`color-surface`, `space-stack-md`, `radius-card`), each referencing one primitive (`{teal-600}`). **The only tokens allowed in components.**
- **Themes.** Only semantic colors and elevations change between themes. Light is the default (`:root`). Dark applies:
  - automatically with `prefers-color-scheme: dark`, unless `<html data-theme="light">`;
  - manually with `<html data-theme="dark">` (theme switch, `theme` cookie).
  Both dark blocks are wrapped in `@media screen`, so **print always uses the light theme**.
- **Reduced motion.** Under `prefers-reduced-motion: reduce`, `duration-fast` and `duration-base` become `0ms`. Always animate with these tokens.

### Allowed literals (the only exceptions)

| Literal | Where | Why |
|---|---|---|
| `0`, `100%`, `1fr`, `auto`, `none`, `transparent`, `currentColor`, `inherit` | anywhere | keywords / neutral values, not design decisions |
| `47.99rem`, `63.99rem`, `29.99rem` | `@media` preludes only, with a comment naming the breakpoint token | custom properties cannot be used in media queries |
| `15mm`, `A4 portrait` | `@page` in print.css only | `@page` cannot read custom properties (value documented as `size-print-margin`) |
| `1px` | `.visually-hidden` pattern only | accessibility pattern |
| multipliers in `calc()` (`2 *`, `-1 *`) and `clip-path` percentages | anywhere | geometry, not a value |

`php <skill>/scripts/check-styles.php` enforces all of this (see SKILL.md).

### Adding a token

1. Need a value no semantic token covers? Do **not** write the value. Pick (or add) a primitive in `primitive.<group>` of `tokens.json`, then add a semantic token in `semantic.<group>` with `$value: "{primitive}"` (colors and shadows: `{"light": "{…}", "dark": "{…}"}`) and a `$description` saying where it is used.
2. Mirror it in `tokens.css` (skill copy **and** `assets/styles/tokens.css`): primitive in the primitives section, semantic in the semantic section, dark value in **both** dark blocks when it differs.
3. Text colors: check contrast ≥ 4.5:1 (≥ 3:1 for borders, focus rings, icons, text ≥ 24px) against every background named in the description, in both themes.
4. Add a row to this file.

## Color

Palette: one accent (**teal**), teal-tinted **neutral grays**, and four semantic hues (**green** success, **amber** warning / public holiday, **red** error / destructive, **blue** info). No other hue.

Rules:

- Text: `color-text` by default, `color-text-muted` for secondary text. Never put muted text on `color-primary-subtle` or status backgrounds: use the matching `-text` token.
- Anything drawn on a fill uses the fill's `on-` token (`color-on-primary`, `color-on-error`). In dark theme the accent is lighter and its `on-` color is dark: never hard-code white.
- Status is never conveyed by color alone: always a word and/or an icon (toast icons, badge text, day labels, hatch pattern for leave, split for half day).
- Borders of interactive controls use `color-border-strong` (≥ 3:1). `color-border` is for decorative dividers only.
- Focus: `outline: var(--focus-ring-width) solid var(--color-focus-ring); outline-offset: var(--focus-ring-offset)` on `:focus-visible` (set globally in base.css). Never remove it.
- The CRA sheet uses only `color-paper-*` (identical in both themes, black text, prints well in black and white).

All text pairs listed in the descriptions were checked at ≥ 4.5:1 in both themes (WCAG AA); control borders, focus rings, today ring and day fills at ≥ 3:1.

### Surfaces and borders

| Token | Light → Dark | Use |
|---|---|---|
| `color-bg` | `gray-50` → `gray-950` | Page background behind every surface. |
| `color-surface` | `gray-0` → `gray-900` | Default surface: cards, header, table, inputs, calendar day cells. |
| `color-surface-muted` | `gray-100` → `gray-850` | Subtle surface: table header, summary bar, code, sunken areas. |
| `color-surface-hover` | `gray-100` → `gray-800` | Hover background for rows, ghost buttons, nav links, empty day cells. |
| `color-surface-active` | `gray-150` → `gray-700` | Pressed background for ghost/secondary buttons. |
| `color-surface-overlay` | `gray-0` → `gray-850` | Floating surfaces: modal, toast, menus. |
| `color-backdrop` | `gray-950-a45` → `black-a60` | Backdrop behind a modal <dialog>. |
| `color-border` | `gray-200` → `gray-800` | Decorative dividers and card borders (no contrast requirement). |
| `color-border-strong` | `gray-500` (both) | Borders of interactive controls (inputs, checkboxes, secondary buttons). >= 3:1 on surface and bg. |
| `color-border-disabled` | `gray-200` → `gray-800` | Border of disabled controls. |
| `color-border-primary` | `teal-600` → `teal-400` | Accent border: selected control, today ring. |

### Text

| Token | Light → Dark | Use |
|---|---|---|
| `color-text` | `gray-900` → `gray-50` | Body text and headings on bg, surface, surface-muted, surface-hover, surface-overlay. |
| `color-text-muted` | `gray-600` → `gray-400` | Secondary text (help, captions, table meta) on bg, surface, surface-muted. >= 4.5:1. |
| `color-text-disabled` | `gray-400` → `gray-600` | Disabled labels and out-of-month day numbers (exempt from contrast). |
| `color-text-inverse` | `gray-0` → `gray-950` | Text on color-tooltip-bg. |
| `color-text-link` | `teal-700` → `teal-300` | Inline links on bg and surface. |
| `color-text-link-hover` | `teal-800` → `teal-200` | Hovered inline links. |
| `color-text-primary` | `teal-700` → `teal-300` | Accent text on bg, surface and color-primary-subtle. |

### Primary (accent: teal)

| Token | Light → Dark | Use |
|---|---|---|
| `color-primary` | `teal-600` → `teal-400` | Accent fill: primary button, active nav marker, selected state, worked day. |
| `color-primary-hover` | `teal-700` → `teal-300` | Hovered primary fill. |
| `color-primary-active` | `teal-800` → `teal-200` | Pressed primary fill. |
| `color-on-primary` | `gray-0` → `gray-950` | Text and icons on color-primary / -hover / -active. |
| `color-primary-subtle` | `teal-50` → `teal-950` | Tinted background: active nav item, selected row, info about the current client. |
| `color-primary-subtle-hover` | `teal-100` → `teal-900` | Hovered tinted background. |
| `color-border-primary` | `teal-600` → `teal-400` | Accent border: selected control, today ring. |
| `color-focus-ring` | `teal-600` → `teal-300` | Keyboard focus ring (solid, >= 3:1 on every surface). |

### Status: neutral, success, warning, error, info

| Token | Light → Dark | Use |
|---|---|---|
| `color-neutral-bg` | `gray-100` → `gray-800` | Neutral badge/tag background. |
| `color-neutral-text` | `gray-700` → `gray-200` | Neutral badge/tag text on color-neutral-bg. |
| `color-neutral-border` | `gray-200` → `gray-700` | Neutral badge/tag border. |
| `color-success-bg` | `green-50` → `green-950` | Success toast/badge background. |
| `color-success-text` | `green-700` → `green-300` | Success text and icon on color-success-bg, surface, bg. |
| `color-success-border` | `green-200` → `green-800` | Success toast/badge border. |
| `color-success-solid` | `green-600` → `green-400` | Success solid mark (icons, progress). |
| `color-warning-bg` | `amber-50` → `amber-950` | Warning toast/badge background. |
| `color-warning-text` | `amber-700` → `amber-300` | Warning text and icon on color-warning-bg, surface, bg. |
| `color-warning-border` | `amber-200` → `amber-800` | Warning toast/badge border. |
| `color-warning-solid` | `amber-400` (both) | Warning solid mark. |
| `color-error-bg` | `red-50` → `red-950` | Error toast/badge/field-message background. |
| `color-error-text` | `red-600` → `red-300` | Error text and icon on color-error-bg, surface, bg (validation messages). |
| `color-error-border` | `red-200` → `red-800` | Error toast/badge border. |
| `color-error-solid` | `red-600` → `red-400` | Destructive button fill and invalid field border. |
| `color-error-solid-hover` | `red-700` → `red-300` | Hovered destructive button fill. |
| `color-on-error` | `gray-0` → `gray-950` | Text on color-error-solid / -hover. |
| `color-info-bg` | `blue-50` → `blue-950` | Info toast/badge/help panel background. |
| `color-info-text` | `blue-700` → `blue-300` | Info text and icon on color-info-bg, surface, bg. |
| `color-info-border` | `blue-200` → `blue-800` | Info toast/badge border. |
| `color-info-solid` | `blue-600` → `blue-400` | Info solid mark. |
| `color-tooltip-bg` | `gray-900` → `gray-100` | Tooltip background (text: color-text-inverse). |

### Calendar day states

| Token | Light → Dark | Use |
|---|---|---|
| `color-day-bg` | `gray-0` → `gray-900` | Empty working day cell background. |
| `color-day-border` | `gray-200` → `gray-800` | Day cell border (decorative). |
| `color-day-text` | `gray-900` → `gray-50` | Day number on color-day-bg, -weekend-bg, -half-bg, -off-bg. |
| `color-day-hover-bg` | `gray-100` → `gray-800` | Hovered empty day cell. |
| `color-day-worked-bg` | `teal-600` → `teal-400` | Full worked day fill; also the filled triangle of a half day. |
| `color-day-worked-hover-bg` | `teal-700` → `teal-300` | Hovered worked day. |
| `color-day-worked-text` | `gray-0` → `gray-950` | Day number and label on color-day-worked-bg. |
| `color-day-half-bg` | `teal-100` → `teal-900` | Half day: light half behind the day number. |
| `color-day-half-border` | `teal-600` → `teal-400` | Half day border. |
| `color-day-weekend-bg` | `gray-100` → `gray-850` | Saturday/Sunday cell background. |
| `color-day-weekend-text` | `gray-600` → `gray-400` | Day number on a weekend cell. |
| `color-day-holiday-bg` | `amber-50` → `amber-950` | French public holiday cell background. |
| `color-day-holiday-text` | `amber-700` → `amber-300` | Holiday label and day number on color-day-holiday-bg. |
| `color-day-holiday-border` | `amber-300` → `amber-700` | Holiday cell border. |
| `color-day-off-bg` | `gray-100` → `gray-850` | Leave/absence cell background (with hatch). |
| `color-day-off-hatch` | `gray-200` → `gray-700` | Diagonal hatch stripes on a leave/absence cell. |
| `color-day-off-text` | `gray-700` → `gray-300` | Label on a leave/absence cell. |
| `color-day-today-ring` | `teal-600` → `teal-300` | Inset ring on today's cell. |
| `color-day-today-bg` | `teal-600` → `teal-400` | Disc behind today's day number. |
| `color-day-today-text` | `gray-0` → `gray-950` | Today's day number on color-day-today-bg. |
| `color-day-today-inverse-bg` | `gray-0` → `gray-950` | Disc behind today's number when today is worked (on color-day-worked-bg). |
| `color-day-today-inverse-text` | `teal-700` → `teal-300` | Today's number on color-day-today-inverse-bg. |
| `color-day-outside-text` | `gray-400` → `gray-600` | Day number of days outside the displayed month. |

### Paper (CRA sheet, never themed)

| Token | Light → Dark | Use |
|---|---|---|
| `color-paper-bg` | `gray-0` (both) | CRA sheet background (screen preview and print). Stays white in dark theme. |
| `color-paper-text` | `gray-1000` (both) | CRA sheet text: always black, prints crisp in black and white. |
| `color-paper-text-muted` | `gray-600` (both) | CRA sheet secondary text (labels, hints) on color-paper-bg. |
| `color-paper-rule` | `gray-500` (both) | CRA sheet table rules and signature boxes (>= 3:1 on paper). |
| `color-paper-rule-light` | `gray-300` (both) | CRA sheet light row separators (decorative). |
| `color-paper-fill` | `gray-100` (both) | CRA header/total row fill. Browsers may drop it: never the only cue. |
| `color-paper-accent` | `teal-700` (both) | CRA eyebrow text; prints as dark gray in B&W (>= 4.5:1 on paper). |

### Primitive scales (reference only)

| Scale | Steps |
|---|---|
| `gray` | 0 `#ffffff` · 50 `#f6f7f7` · 100 `#eceeee` · 150 `#e3e6e6` · 200 `#d8dcdc` · 300 `#bfc5c5` · 400 `#949d9d` · 500 `#6b7575` · 600 `#515a5a` · 700 `#3c4444` · 800 `#2a3030` · 850 `#212626` · 900 `#191d1d` · 950 `#101313` · 1000 `#000000` |
| `teal` | 50 `#ecf7f5` · 100 `#d2eee9` · 200 `#a5dcd3` · 300 `#6fc3b6` · 400 `#3aa797` · 500 `#1d8b7c` · 600 `#137166` · 700 `#115d54` · 800 `#0f4b45` · 900 `#0d3d38` · 950 `#072421` |
| `green` | 50 `#edf7ef` · 100 `#d3eed9` · 200 `#a6dcb2` · 300 `#6fc184` · 400 `#41a35a` · 500 `#2a8643` · 600 `#1f6d36` · 700 `#1a572d` · 800 `#164526` · 900 `#12381f` · 950 `#081f10` |
| `amber` | 50 `#fdf6e6` · 100 `#fae8c0` · 200 `#f5d189` · 300 `#eeb44b` · 400 `#e2981f` · 500 `#c37b0c` · 600 `#9c5f08` · 700 `#7b4a0a` · 800 `#5f3a0c` · 900 `#482d0c` · 950 `#291805` |
| `red` | 50 `#fdf0ee` · 100 `#fadad5` · 200 `#f3b3a9` · 300 `#ea8574` · 400 `#dc5946` · 500 `#c63b28` · 600 `#a52e1e` · 700 `#84251a` · 800 `#681f17` · 900 `#4e1914` · 950 `#2c0b08` |
| `blue` | 50 `#eef3fc` · 100 `#d6e3f9` · 200 `#adc8f3` · 300 `#7ca6ea` · 400 `#4f84dc` · 500 `#3067c4` · 600 `#2552a4` · 700 `#1f4383` · 800 `#1b3667` · 900 `#172c51` · 950 `#0b172c` |

Translucent: `gray-950-a45` (rgba(16,19,19,.45)), `black-a40`, `black-a60`.

## Typography

- **Families.** *Atkinson Hyperlegible Next* (UI, variable 200–800) and *Atkinson Hyperlegible Mono* (numbers), both self-hosted in `assets/fonts/` (SIL OFL 1.1), declared in base.css with `font-display: swap`, falling back to `system-ui` / `ui-monospace`. Chosen for legibility (distinct 0/O, 1/l/I).
- **Numbers** (dates, quantities, totals, SIRET): `font-family-numeric` + `font-variant-numeric: tabular-nums` (utility `.text-numeric`) so columns align.
- **Scale** (desktop and mobile share it; only the calendar title steps down on mobile):

| Style | Size | Line height | Weight | Use |
|---|---|---|---|---|
| display | `font-size-display` 40px | `line-height-display` 1.2 | bold | calendar month title |
| heading-1 | `font-size-heading-1` 30px | `line-height-display` 1.2 | bold | page title (`<h1>`, one per page) |
| heading-2 | `font-size-heading-2` 24px | `line-height-heading` 1.35 | semibold | section, modal title, empty-state title |
| heading-3 | `font-size-heading-3` 19px | `line-height-heading` 1.35 | semibold | card title, brand |
| body | `font-size-body` 16px | `line-height-body` 1.5 | regular | text, inputs, buttons |
| label | `font-size-body` 16px | 1.5 | medium | form labels, nav links |
| small | `font-size-small` 14px | 1.5 | regular | help, meta, small buttons, badges |
| caption | `font-size-caption` 12px | 1.5 | semibold | day labels, weekday initials |
| print-heading | `font-size-print-heading` 16pt | 1.2 | bold | CRA month title |
| print-subheading | `font-size-print-subheading` 12pt | 1.35 | bold | party names, total row |
| print-body | `font-size-print-body` 10pt | `line-height-print` 1.35 | regular | CRA table |
| print-meta / print-small | 9pt / 8pt | 1.35 | regular | party blocks / hints |

- Never set body text below `font-size-small` (14px) on screen. Captions (12px) only for short labels that repeat information available elsewhere (day labels mirror the accessible name).
- French typography: typographic apostrophe `’`, non-breaking space before `: ; ! ?` and inside `« »` (typed in the texts themselves).

| Token | Value | Use |
|---|---|---|
| `font-size-display` | `font-size-3xl` (2.5rem) | Display: calendar month title on desktop, empty-state hero. |
| `font-size-heading-1` | `font-size-2xl` (1.875rem) | Page title (h1). |
| `font-size-heading-2` | `font-size-xl` (1.5rem) | Section title (h2), modal title, summary total. |
| `font-size-heading-3` | `font-size-lg` (1.1875rem) | Card title (h3). |
| `font-size-body` | `font-size-md` (1rem) | Body text, inputs, buttons (md). |
| `font-size-small` | `font-size-sm` (0.875rem) | Help text, table meta, small buttons, badges. |
| `font-size-caption` | `font-size-xs` (0.75rem) | Captions, day labels (Férié, ½), weekday headers on mobile. |
| `font-size-print-body` | `font-size-pt-10` (10pt) | Printed body and table text. |
| `font-size-print-small` | `font-size-pt-8` (8pt) | Printed footnotes, signature hints. |
| `font-size-print-meta` | `font-size-pt-9` (9pt) | Printed party blocks (freelance / client). |
| `font-size-print-heading` | `font-size-pt-16` (16pt) | Printed document title. |
| `font-size-print-subheading` | `font-size-pt-12` (12pt) | Printed section titles and total. |

| Token | Value | Use |
|---|---|---|
| `line-height-display` | `line-height-120` (1.2) | Display and heading-1. |
| `line-height-heading` | `line-height-135` (1.35) | Headings 2-3. |
| `line-height-body` | `line-height-150` (1.5) | Body, small, caption. |
| `line-height-control` | `line-height-100` (1) | Single-line controls (height is set by size-control-*). |
| `line-height-print` | `line-height-135` (1.35) | CRA sheet text (compact, fits a month on one page). |

| Token | Value | Use |
|---|---|---|
| `font-weight-regular` | `font-weight-400` (400) | Body text. |
| `font-weight-medium` | `font-weight-500` (500) | Labels, nav links, table headers. |
| `font-weight-semibold` | `font-weight-600` (600) | Buttons, headings 2-3, badges. |
| `font-weight-bold` | `font-weight-700` (700) | Heading 1, display, totals. |

| Token | Value | Use |
|---|---|---|
| `letter-spacing-caps` | `letter-spacing-6` (0.06em) | Uppercase eyebrows and role labels (CRA sheet). |
| `letter-spacing-display` | `letter-spacing-neg-1` (-0.01em) | Display text. |
| `letter-spacing-normal` | `letter-spacing-0` (0) | Everything else. |

| Token | Value | Use |
|---|---|---|
| `font-family-body` | `font-family-sans` ("Atkinson Hyperlegible Next", system-ui, -apple-system, "Segoe UI", sans-serif) | All UI text. |
| `font-family-numeric` | `font-family-mono` ("Atkinson Hyperlegible Mono", ui-monospace, "SF Mono", Menlo, Consolas, monospace) | Totals, day counts, SIRET, dates in tables. |

## Spacing

4px base scale (`space-0-5` … `space-16`). Semantic spacing is named by relationship:

- `space-inline-*`: horizontal gap between siblings (icon + label, buttons).
- `space-stack-*`: vertical gap between stacked blocks.
- `space-inset-*`: padding inside a box.
- `space-control-x-*`: horizontal padding of controls (height comes from `size-control-*`).
- Dedicated: page gutters, table cells, calendar gaps, print.

Generous by default: page blocks `space-stack-lg` (24px), form fields `space-stack-md` (16px), card padding `space-inset-lg` (24px).

| Token | Value | Use |
|---|---|---|
| `space-inline-xs` | `space-1` (0.25rem) | Gap between an icon and its label inside badges/tags. |
| `space-inline-sm` | `space-2` (0.5rem) | Gap between an icon and its label in buttons, between inline controls. |
| `space-inline-md` | `space-3` (0.75rem) | Gap between buttons in a group, between toolbar items. |
| `space-inline-lg` | `space-6` (1.5rem) | Gap between toolbar groups. |
| `space-stack-xs` | `space-1` (0.25rem) | Label to input, title to subtitle. |
| `space-stack-sm` | `space-2` (0.5rem) | Input to help/error text, items in a tight list. |
| `space-stack-md` | `space-4` (1rem) | Between form fields, paragraphs, card sections. |
| `space-stack-lg` | `space-6` (1.5rem) | Between page blocks (page header to content). |
| `space-stack-xl` | `space-10` (2.5rem) | Between major page sections. |
| `space-inset-xs` | `space-1` (0.25rem) | Padding of tiny elements (tooltip vertical, badge vertical). |
| `space-inset-sm` | `space-2` (0.5rem) | Padding of compact elements (day cell, tag). |
| `space-inset-md` | `space-4` (1rem) | Padding of cards, toasts, table cells (horizontal). |
| `space-inset-lg` | `space-6` (1.5rem) | Padding of modals, page sections, empty states. |
| `space-inset-xl` | `space-8` (2rem) | Padding of the CRA sheet preview on screen. |
| `space-control-x-sm` | `space-3` (0.75rem) | Horizontal padding, small controls. |
| `space-control-x-md` | `space-4` (1rem) | Horizontal padding, medium controls and inputs. |
| `space-control-x-lg` | `space-6` (1.5rem) | Horizontal padding, large controls. |
| `space-table-cell-y` | `space-3` (0.75rem) | Vertical padding of table cells. |
| `space-calendar-gap` | `space-2` (0.5rem) | Gap between calendar day cells (desktop). |
| `space-calendar-gap-compact` | `space-1` (0.25rem) | Gap between calendar day cells (mobile). |
| `space-page-gutter` | `space-8` (2rem) | Horizontal page padding (desktop). |
| `space-page-gutter-compact` | `space-4` (1rem) | Horizontal page padding (mobile), 16px gutter. |
| `space-print-cell-y` | `space-0-5` (0.125rem) | Vertical padding of CRA table cells (fits a full month on one A4 page). |
| `space-print-section` | `space-3` (0.75rem) | Gap between blocks of the CRA sheet. |

## Sizes

Heights of controls, icons, layout widths, A4 dimensions. Controls are 40px (`size-control-md`); on touch screens interactive targets are at least `size-touch-target` (44px) or have 8px spacing around them.

| Token | Value | Use |
|---|---|---|
| `size-icon-sm` | `size-16` (1rem) | Icons in small buttons, badges, inline text. |
| `size-icon-md` | `size-20` (1.25rem) | Icons in buttons, nav, inputs. |
| `size-icon-lg` | `size-24` (1.5rem) | Icons in toasts, modal header. |
| `size-icon-xl` | `size-48` (3rem) | Empty-state icon. |
| `size-control-sm` | `size-32` (2rem) | Height of small buttons/controls. |
| `size-control-md` | `size-40` (2.5rem) | Height of medium buttons, inputs, selects. |
| `size-control-lg` | `size-48` (3rem) | Height of large buttons. |
| `size-touch-target` | `size-44` (2.75rem) | Minimum hit area on touch devices (mobile nav, day cells). |
| `size-checkbox` | `size-20` (1.25rem) | Checkbox box size. |
| `size-toggle-width` | `size-40` (2.5rem) | Toggle track width. |
| `size-toggle-height` | `size-24` (1.5rem) | Toggle track height. |
| `size-header-height` | `size-64` (4rem) | App header height. |
| `size-bottom-nav-height` | `size-64` (4rem) | Mobile bottom navigation height. |
| `size-day-min-height` | `size-80` (5rem) | Calendar day cell minimum height (desktop). |
| `size-day-min-height-compact` | `size-48` (3rem) | Calendar day cell minimum height (mobile). |
| `size-field-max-width` | `size-560` (35rem) | Max width of forms. |
| `size-field-min-width` | `size-240` (15rem) | Min width of a column in a multi-column form row. |
| `size-input-date-width` | `size-240` (15rem) | Max width of date inputs. |
| `size-hatch-stripe` | `size-4` (0.25rem) | Stripe width of the leave/absence hatch pattern. |
| `size-hatch-gap` | `size-8` (0.5rem) | Repeat period of the leave/absence hatch pattern. |
| `size-note-dot` | `size-8` (0.5rem) | Diameter of the 'has note' dot on a day cell (mobile). |
| `size-modal-width` | `size-480` (30rem) | Modal width. |
| `size-toast-width` | `size-320` (20rem) | Toast width. |
| `size-tooltip-max-width` | `size-240` (15rem) | Tooltip max width. |
| `size-empty-state-max-width` | `size-480` (30rem) | Empty state text column. |
| `size-container` | `size-1120` (70rem) | Max width of page content. |
| `size-link-underline-offset` | `size-em-0-2` (0.2em) | Distance between inline link text and its underline. |
| `size-sheet-width` | `size-a4-width` (210mm) | A4 sheet width (screen preview and print). |
| `size-sheet-height` | `size-a4-height` (297mm) | A4 sheet min height (screen preview). |
| `size-sheet-padding` | `size-mm-18` (18mm) | Inner margin of the A4 sheet preview on screen (mirrors @page margin). |
| `size-signature-height` | `size-mm-20` (20mm) | Height of each signature box. |
| `size-print-margin` | `size-mm-15` (15mm) | Documented value of @page margin (literal in @page, see print.md). |

## Radius

Slightly rounded: 6px controls, 10px cards, 14px modals, pills for badges/toggles, square on paper.

| Token | Value | Use |
|---|---|---|
| `radius-control` | `radius-6` (6px) | Buttons, inputs, selects, checkboxes, day cells. |
| `radius-card` | `radius-10` (10px) | Cards, tables, calendar, toasts, summary bar. |
| `radius-modal` | `radius-14` (14px) | Modal dialog. |
| `radius-tag` | `radius-4` (4px) | Tags, tooltips, kbd. |
| `radius-pill` | `radius-full` (9999px) | Badges, toggles, avatars. |
| `radius-none` | `radius-0` (0) | Printed document elements. |

## Borders

| Token | Value | Use |
|---|---|---|
| `border-width-default` | `border-width-1` (1px) | Every border and divider. |
| `border-width-strong` | `border-width-2` (2px) | Selected/today ring, active nav underline, half-day border. |
| `focus-ring-width` | `border-width-2` (2px) | Focus outline width. |
| `focus-ring-offset` | `border-width-2` (2px) | Focus outline offset. |

## Elevation

Flat first: surfaces are separated by `color-border`, not by shadows. Shadows only for things that float (sticky summary, toasts, tooltips, modals). Dark theme uses darker, denser shadows.

| Token | Light → Dark | Use |
|---|---|---|
| `elevation-flat` | `shadow-0` (both) | Cards, tables, calendar: flat, separated by color-border. |
| `elevation-raised` | `shadow-1` → `shadow-dark-1` | Sticky header, hovered card. |
| `elevation-overlay` | `shadow-2` → `shadow-dark-2` | Toasts, tooltips, menus. |
| `elevation-modal` | `shadow-3` → `shadow-dark-3` | Modal dialog. |

Primitives: `shadow-0` (none), `shadow-1` … `shadow-3` (soft, light theme), `shadow-dark-1` … `shadow-dark-3`.

## Motion

Short and functional: color transitions on hover/focus (`duration-fast`), enter/leave of modals and toasts (`duration-base`). No decorative animation. All durations collapse to 0 with reduced motion.

| Token | Value | Use |
|---|---|---|
| `duration-fast` | `duration-120` (120ms) | Hover/focus color transitions. |
| `duration-base` | `duration-200` (200ms) | Modal/toast enter and leave. |
| `duration-toast` | `duration-5000` (5000ms) | Auto-dismiss delay of non-error toasts. |
| `duration-spinner` | `duration-800` (800ms) | One rotation of the loading spinner. |
| `duration-none` | `duration-0` (0ms) | Used when prefers-reduced-motion: reduce. |

| Token | Value | Use |
|---|---|---|
| `easing-default` | `easing-standard` (cubic-bezier(0.2, 0, 0, 1)) | Every transition. |

## Layers (z-index)

Dialogs use the native top layer (`showModal()`), never a z-index.

| Token | Value | Use |
|---|---|---|
| `z-base` | `z-0` (0) | Default stacking. |
| `z-sticky` | `z-10` (10) | Sticky table headers, calendar weekday row. |
| `z-header` | `z-100` (100) | App header and mobile bottom nav. |
| `z-toast` | `z-1000` (1000) | Toast stack (dialogs use the top layer, no z-index). |

## Breakpoints

Desktop-first (`max-width` queries). Custom properties cannot be used in `@media`: write the literal with a comment, e.g. `@media (max-width: 47.99rem) { /* breakpoint-mobile */ }`.

| Token | Value | Use |
|---|---|---|
| `breakpoint-mobile` | `breakpoint-768` (48rem) | max-width: 47.99rem  -> phones: bottom nav, stacked tables, compact calendar. |
| `breakpoint-tablet` | `breakpoint-1024` (64rem) | max-width: 63.99rem  -> tablets: narrower gutters. |
| `breakpoint-small` | `breakpoint-480` (30rem) | max-width: 29.99rem  -> small phones: weekday initials only. |

What changes per breakpoint:

| Below | Change |
|---|---|
| `breakpoint-tablet` (1024px) | page gutters shrink to `space-page-gutter-compact` |
| `breakpoint-mobile` (768px) | navigation becomes a bottom tab bar; page header actions go full width; tables become stacked cards; calendar cells compact (no labels, note dot); summary not sticky; modal buttons full width; toasts above the bottom bar; the A4 sheet reflows |
| `breakpoint-small` (480px) | weekday headers show initials |

## Iconography

- **One set: Lucide** (https://lucide.dev, ISC license), through Symfony UX Icons: `<twig:ux:icon name="lucide:calendar-days" class="icon" />` (or `ux_icon('lucide:…', {class: 'icon'})` in PHP-heavy templates such as the form theme).
- Every icon used by the design system is committed in `assets/icons/lucide/*.svg`, so rendering never calls the Iconify API. For a new icon: pick it on lucide.dev, then `php bin/console ux:icons:lock` (or copy the SVG from the `lucide-static` package, without its `class`, `width`, `height`).
- Stroke icons in `currentColor`: color comes from the parent's `color`. Size only with `size-icon-sm|md|lg|xl` (the `.icon` class defaults to md).
- Icons are decorative (`aria-hidden="true"` by default via `config/packages/ux_icons.yaml`). An icon alone never carries meaning: pair it with visible text or give its button an accessible name (`<twig:Button iconOnly label="…">`).
- Icon vocabulary (reuse these meanings):

| Meaning | Icon |
|---|---|
| Calendar / nav | `calendar-days` · brand: `calendar-check` |
| Clients / profile | `users` · `user-round` · add client: `user-plus` · mission: `briefcase` |
| Add / edit / delete | `plus` · `pencil` · `trash-2` |
| Save / confirm | `check` |
| Print / CRA | `printer` · `file-text` |
| Back / months | `arrow-left` · `chevron-left` · `chevron-right` · select: `chevron-down` |
| Theme | `sun` · `moon` · `monitor` |
| Status | success `circle-check` · info `info` · warning `triangle-alert` · error `circle-x` · field error `circle-alert` |
| Day states | worked `check` · leave `tree-palm` · holiday `flag` · note `sticky-note` · edit day `notebook-pen` · fill `calendar-check` · clear `eraser` |
| Close | `x` |

## Accessibility baseline

- WCAG 2.2 AA: contrast (above), focus visible (global `:focus-visible`), keyboard operable (calendar grid: see calendar.md), target size ≥ 24px (44px on touch for navigation and days).
- `<html lang>` from the request locale; one `<h1>` per page (PageHeader, or the CRA title); a skip link to `#main`.
- Forms: every control has a `<label>`; help and errors are linked with `aria-describedby`; invalid controls get `aria-invalid="true"`; optional fields are marked "(facultatif)".
- Live feedback: toasts (`role="status"`, `role="alert"` for warning/error), calendar changes announced in a polite live region.
- Motion respects `prefers-reduced-motion`; dark theme respects `prefers-color-scheme`.
