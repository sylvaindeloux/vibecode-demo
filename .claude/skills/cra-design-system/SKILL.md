---
name: cra-design-system
description: Design system of EasyCRA, the CRA app (French freelancers' monthly activity report, Symfony + Twig Components + Stimulus + Turbo + AssetMapper). Use it for ANY creation or change of UI in this app - Twig templates or pages, Twig Components, CSS or design tokens, Stimulus controllers, Symfony form rendering, icons, UI texts/translations, the calendar, or the printable CRA page - even for a small tweak.
---

# EasyCRA design system

Calm, clean, professional but friendly: a tool opened once a month and closed after 2 minutes. One teal accent, neutral grays, four status colors, generous spacing, slightly rounded corners, flat surfaces (shadows only on floating elements), light and dark themes, French UI, WCAG AA.

## Non-negotiable rules

1. **Semantic tokens only.** In CSS, use `var(--<semantic-token>)` (listed in `references/foundations.md`). Never a primitive token (`--teal-600`, `--space-4`), never a hard-coded color, length, duration, shadow or z-index. Allowed literals are listed in foundations.md ("Allowed literals").
2. **Missing token → create it, don't hard-code.** Add a semantic token (and a primitive if needed) to `tokens/tokens.json` **and** `tokens.css` (skill copy and project `assets/styles/tokens.css`), with a `$description`, dark value in both dark blocks, contrast checked, and a row in foundations.md.
3. **Components first.** Before writing markup, reuse a Twig Component from `references/components.md`. If none fits, create a new anonymous component (`templates/components/`, own CSS file, entry in components.md) instead of writing or duplicating markup in a page.
4. **Interactions only through Stimulus.** No `<script>` in templates, no `on*=` attributes, no `style=` set from JS. Controllers toggle attributes (`data-*`, `aria-*`); CSS does the visuals.
5. **CRA page = check the print preview.** Any change touching the CRA page, `Cra:*` components, `cra-document.css`, `print.css`, tokens used by them or `base.html.twig` must be checked on screen **and** in print preview (checklist in `references/print.md`).
6. **All states and accessibility, every time.** Implement every state of a component (hover, focus-visible, active, disabled, loading, invalid, empty…), keyboard operation, labels and ARIA as specified; contrast ≥ 4.5:1 for text (3:1 for UI marks) in both themes; color is never the only cue.
7. **Texts in French through translations; code in English.** UI strings via `|trans` with keys in `translations/messages+intl-icu.fr.yaml` (validation: `validators+intl-icu.fr.yaml`). Class names, props, tokens, keys, comments, commits: English.

## How to work

1. Identify the page/component in `references/screens.md` and `references/components.md`; open the matching example in `templates/templates/examples/`.
2. Compose with existing components; follow their props, blocks and Do/Don't.
3. Style with semantic tokens in the component's own CSS file (BEM: `.block__element--modifier`, states in attributes), registered in `assets/styles/app.css` (`components` layer).
4. Add translation keys for every new text.
5. Verify, then report what you checked:
   - `php <skill-dir>/scripts/check-styles.php` from the project root (tokens in sync, only semantic tokens, no literals) must print `OK`;
   - `php bin/console lint:twig templates` and `php bin/console debug:translation fr --only-missing`;
   - look at the page (or `/_design-system/<screen>` in dev) in light, dark, 390px wide, keyboard only; CRA: print preview.

## Where to look

| File | Read / use when |
|---|---|
| `references/foundations.md` | choosing any token (color, type, spacing, size, radius, elevation, motion, z-index, breakpoint); adding a token; icons; accessibility baseline |
| `references/components.md` | using, changing or creating a component: anatomy, props, variants, states, tokens, Do/Don't |
| `references/calendar.md` | anything about the calendar: day states, view model, keyboard, ARIA, summary, persistence endpoint, Live Component variant, French holidays |
| `references/print.md` | anything about the printable CRA: structure, `<title>`/file name, A4, print CSS, page breaks, browser support, verification checklist |
| `references/screens.md` | building or changing a page: routes, composition, fields, controller variables, flows |
| `references/symfony-integration.md` | installing the system, Twig Component / Stimulus / Turbo / form theme / translations / UX Icons conventions, AssetMapper CSS organisation, file layout |
| `tokens/tokens.json` | source of truth of tokens (primitive + semantic, light/dark); edit first when a value changes |
| `tokens/tokens.css` | same tokens as CSS custom properties; copy to `assets/styles/tokens.css` |
| `scripts/check-styles.php` | after every CSS/token change (`php <skill-dir>/scripts/check-styles.php [project-root]`) |
| `templates/` | files to copy into the Symfony project, same paths (mapping in symfony-integration.md) |
| `templates/assets/styles/` | `app.css` (entry, layers), `tokens.css`, `base.css`, `utilities.css`, `print.css`, `components/*.css` (one per component) |
| `templates/assets/controllers/` | Stimulus controllers: theme, modal, modal_trigger, toast, toast_stack, tooltip, print, autosubmit, calendar |
| `templates/assets/fonts/`, `templates/assets/icons/lucide/` | self-hosted fonts (OFL), locked Lucide icons |
| `templates/templates/base.html.twig` | shared layout (header, nav, theme, toasts, dialogs block) |
| `templates/templates/components/` | all Twig Components (Button, Field, DataTable, Calendar/*, Cra/*, Modal…) |
| `templates/templates/form/theme.html.twig` | Symfony form theme (labels, help, errors, widgets, toggle, segmented) |
| `templates/templates/examples/` | reference screens: `calendar`, `cra_print`, `client_index` (list + empty + delete dialog), `client_form`, `profile` |
| `templates/translations/` | French strings (ICU) and validation messages |
| `templates/config/packages/` | `twig.yaml` (form theme), `ux_icons.yaml`, `translation.yaml` |
| `templates/src/` | calendar view model + `FrenchHolidays`, `ToggleType`, dev gallery `DesignSystemController` (`/_design-system`) |

## Quick reference

- Page skeleton: `{% extends 'base.html.twig' %}`, `{% block title %}`, `{% block body %}<twig:PageHeader …/>…{% endblock %}`, dialogs in `{% block dialogs %}`.
- Buttons: `<twig:Button variant="primary|secondary|ghost|danger" size="sm|md|lg" icon="lucide:…" href="…">Texte</twig:Button>`; icon-only needs `iconOnly` + `label`.
- Icons: `<twig:ux:icon name="lucide:calendar-days" class="icon" />` (Lucide only).
- Forms: Symfony `form_row()` with the global theme; standalone controls: `<twig:Field>` + `<twig:Input|Select|Textarea>`.
- Feedback: flash `addFlash('success', 'translation.key')` → toast; confirmation of destructive actions → `<twig:ConfirmDialog>`.
- Breakpoints (literal in `@media`, comment the token): mobile `max-width: 47.99rem`, tablet `63.99rem`, small `29.99rem`.
- Screen-only elements on the CRA page: `data-print-hide`.
