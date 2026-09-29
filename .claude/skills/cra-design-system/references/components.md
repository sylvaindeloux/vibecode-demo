# Components

Every component is an **anonymous Twig Component** in `templates/components/` (sub-components in a folder: `Calendar/Day.html.twig` → `<twig:Calendar:Day>`), with:

- props declared by `{% props %}` (anything else passed lands in `attributes`);
- `attributes.defaults({...})` on the root element, so callers can add `class`, `id`, `data-*`, `aria-*` (classes, `data-controller` and `data-action` are **merged**, other attributes are overridden);
- one stylesheet `assets/styles/components/<name>.css`, BEM classes (`block__element--modifier`), states in attributes (`aria-*`, `data-*`, `:disabled`);
- behaviour, if any, in a Stimulus controller `assets/controllers/<name>_controller.js`.

Before writing markup, look for the component below. If none fits, create a new component (template + CSS + entry in this file) rather than writing the markup inline twice.

Conventions used below: **Props** (name = default), **Blocks** (content slots), **Tokens** (main semantic tokens), **States**, **Do / Don't**.

---

## Layout

### Base layout — `templates/base.html.twig`

Anatomy: skip link → `<twig:AppHeader>` → `<main id="main" class="page">` → `<twig:ToastStack>` → `{% block dialogs %}`.

- Blocks: `title` (also the default PDF file name on the CRA page), `body`, `page_modifier` (e.g. ` page--narrow` for forms), `dialogs` (page-level `<dialog>`s, outside `<main>`), `importmap`.
- `<html lang>` = request locale; `data-theme` = `theme` cookie (`light`|`dark`; absent = system). No inline script.
- `.page`: container, max `size-container`, gutters `space-page-gutter` (`-compact` below tablet), blocks separated by `space-stack-lg`. `.page--narrow`: forms (max `size-field-max-width`).

### AppHeader — `<twig:AppHeader :theme="theme" />`

App name + logo (link to calendar), main navigation, theme switch. Sticky, `color-surface`, bottom `color-border`.

- Props: `theme = 'system'`.
- Active item: from `app.current_route` (prefixes `app_calendar`/`app_cra`, `app_client`, `app_profile`).
- Mobile (< 768px): the `<nav>` becomes a fixed **bottom tab bar** (icon above label, 64px tall, safe-area aware); the header keeps logo + theme switch; `<body>` gets bottom padding.
- Do: add a destination by adding a `<li><twig:NavLink>` (max 4 items, they must fit the bottom bar).
- Don't: put actions in the header; they belong to the PageHeader.

### NavLink — `<twig:NavLink href icon label active />`

- Props: `href`, `label`, `icon` (Lucide name), `active = false` → `aria-current="page"`.
- States: default (`color-text-muted`), hover (`color-surface-hover`), active (`color-primary-subtle` + `color-text-primary`; mobile: top bar `color-primary`), focus-visible.

### ThemeSwitch — `<twig:ThemeSwitch current="system" />`

Radio group (fieldset + visually-hidden legend) of 3 icon options: Clair (sun), Sombre (moon), Automatique (monitor). Stimulus `theme`: sets `<html data-theme>` and the `theme` cookie (1 year, `SameSite=Lax`).

- Tokens: `color-surface-muted` track, `color-surface` + `elevation-raised` for the checked option, `radius-pill`.
- Keyboard: arrows move between options (native radios). Labels have a `title` for mouse users and a visually-hidden text for screen readers.

## PageHeader — `<twig:PageHeader title subtitle>`

Page `<h1>`, optional subtitle, optional actions.

- Props: `title` (required), `subtitle = null`.
- Blocks: `actions` (buttons, ClientPicker). Twig blocks cannot be conditional: when actions depend on a condition, branch around the whole component (see `examples/client_index.html.twig`).
- Tokens: `font-size-heading-1`, `color-text-muted`, `space-inline-lg`.
- Mobile: stacked, actions full width.
- Do: one PageHeader per page, first thing in `body`. Don't: a second `<h1>` elsewhere.

## Button — `<twig:Button>`

| Prop | Default | Values |
|---|---|---|
| `variant` | `'secondary'` | `primary` (one per view: the main action), `secondary`, `ghost` (low emphasis, toolbars, tables), `danger` (destructive, confirmation dialogs only) |
| `size` | `'md'` | `sm` (32px, tables/toolbars), `md` (40px), `lg` (48px) |
| `type` | `'button'` | `submit` for forms |
| `href` | `null` | renders `<a>` (navigation) instead of `<button>` (action) |
| `icon` / `iconEnd` | `null` | Lucide name before / after the label |
| `iconOnly` | `false` | square button, icon only; **requires `label`** (accessible name) |
| `label` | `null` | text when no content block; aria-label when `iconOnly` |
| `loading` | `false` | spinner, `aria-busy="true"`, `aria-disabled="true"`, visually-hidden "Chargement…" |
| `disabled` | `false` | native `disabled` (a disabled link renders as a disabled `<button>`) |
| `block` | `false` | full width |

- Content block = label: `<twig:Button variant="primary" icon="lucide:plus">{{ 'client.index.add'|trans }}</twig:Button>`.
- Automatic loading: while Turbo submits a form (`form[aria-busy="true"]`), its submit buttons show the spinner and ignore clicks. No JS to write.
- Tokens: `color-primary(-hover/-active)`/`color-on-primary`, `color-error-solid(-hover)`/`color-on-error`, `color-surface(-hover/-active)`, `color-border-strong`, `radius-control`, `size-control-*`, `space-control-x-*`, `font-weight-semibold`.
- States: default, hover, active, focus-visible (global ring), disabled (`color-surface-muted`, `color-text-disabled`), loading.
- Do: verbs in the infinitive ("Enregistrer", "Ajouter un client"). Wrap icon-only buttons in `<twig:Tooltip decorative>` to show their label on hover.
- Don't: two primary buttons side by side; a `danger` button outside a confirmation; an `<a>` styled as a button without `href`; icon-only without `label`.

### PrintButton — `<twig:PrintButton />`

Primary button with printer icon, label "Imprimer / Enregistrer en PDF", Stimulus `print` → `window.print()`. Has `data-print-hide`. Props: `label`, `variant = 'primary'`, `size = 'md'`.

## Form fields

Two ways to render the same markup:

1. **Symfony forms** (always, for data entry): `form_start(form)`, `form_row(form.x)`, `form_end(form)`. The global form theme `templates/form/theme.html.twig` outputs the design-system markup, including labels, "(facultatif)" on optional fields, help, errors, `aria-describedby`, `aria-invalid`.
2. **Standalone controls** (filters, GET forms, dialogs not backed by a FormType): `<twig:Field>` + `<twig:Input|Textarea|Select>`, `<twig:Checkbox>`, `<twig:Toggle>`, `<twig:SegmentedControl>`.

Markup contract (both ways):

```html
<div class="field field--invalid">
  <label class="field__label" for="client_name">Nom du client</label>
  <input class="input" id="client_name" required aria-invalid="true" aria-describedby="client_name_help client_name_errors">
  <p class="field__help" id="client_name_help">…</p>
  <ul class="field__errors" id="client_name_errors"><li class="field__error"><svg class="icon">…</svg>Indiquez le nom du client.</li></ul>
</div>
```

Layout helpers: `.form` (column, `space-stack-md`, max `size-field-max-width`, added by `form_start`), `.form__row` (responsive columns, min `size-field-min-width`), `.form__section` + `.form__legend` (fieldset), `.form-actions` (buttons, primary first), `.form-errors` (form-level error alert, rendered by `form_errors(form)`).

### Field — `<twig:Field id label>`

- Props: `label`, `id` (of the control), `help = null`, `errors = []` (strings), `optional = false` (adds "(facultatif)"), `hideLabel = false` (visually hidden, still announced).
- Block: the control. Give it `describedBy="<id>_help <id>_errors"` (only the ids that exist) and `invalid` when errors exist.

### Input — `<twig:Input id type="text" />`

- Props: `id`, `name = id`, `type = 'text'` (text, email, tel, date, number, search, url), `value = null`, `invalid = false`, `describedBy = null`. Other attributes pass through (`required`, `autocomplete`, `inputmode`, `maxlength`, `placeholder`).
- `date` and `number` use `font-family-numeric` (`.input--numeric`). Dates: native `type="date"` (Symfony `DateType` with `widget: 'single_text'`), never a JS date picker.
- Tokens: `color-surface`, `color-border-strong` (hover `color-text-muted`, focus `color-border-primary` + ring), invalid `color-error-solid` 2px, disabled `color-surface-muted`/`color-text-disabled`, `size-control-md`, `radius-control`.

### Textarea — `<twig:Textarea id rows="3" />`

Same props as Input (+ `rows`, `value` as content). Vertical resize only.

### Select — `<twig:Select id :options="[{value, label}]" value />`

Native `<select>` + decorative `chevron-down` icon (`.select`). Props: `id`, `options`, `name`, `value`, `placeholder` (empty first option), `invalid`, `describedBy`. Never replace with a custom JS dropdown.

### Checkbox — `<twig:Checkbox id label />`

Box 20px, checked = `color-primary` + check mark `color-on-primary`. Props: `id`, `label`, `name`, `value = '1'`, `checked`, `invalid`. For a value submitted with a form.

### Toggle — `<twig:Toggle id label />`

Checkbox with `role="switch"`; for settings applied immediately (on/off). Track `size-toggle-width × size-toggle-height`, checked `color-primary`. Symfony: use `App\Form\Type\ToggleType` (block prefix `toggle`, themed).

### SegmentedControl — `<twig:SegmentedControl legend name :options value />`

Radio group as joined buttons, for 2–5 short exclusive choices (day type in the day dialog). Props: `legend`, `name`, `options: [{value, label, icon?}]`, `value`, `hideLegend`. Symfony: `ChoiceType` with `expanded: true, multiple: false` renders the same markup inside a fieldset.

Form Do / Don't:

- Do: label above the control; help under it; errors under help, in `color-error-text` with an icon, phrased as a fix ("Indiquez le nom du client."). Mark optional fields, not required ones.
- Do: validate server-side (Symfony constraints, messages in `translations/validators+intl-icu.fr.yaml`); the theme adds `novalidate` so browser bubbles never replace these messages. Return HTTP 422 on invalid submit (Turbo requirement).
- Don't: placeholders as labels; disabled submit buttons to signal invalid forms; red borders without a message.

## ClientPicker — `<twig:ClientPicker :clients :selected action :params />`

Labelled native select of `client · mission` in a GET form, auto-submitted on change (Stimulus `autosubmit`, Turbo navigation). Keeps other query params (`params`, e.g. `{month: month.id}`). A submit button (`.autosubmit-fallback`) is visible only without JS.

- Props: `clients: [{id, name, mission}]`, `selected`, `name = 'client'`, `action`, `params = {}`.
- Placement: calendar PageHeader actions. Mobile: full width.

## DataTable — `<twig:DataTable>`, `<twig:DataTable:Row>`, `<twig:DataTable:Cell>`

```twig
<twig:DataTable :caption="'client.index.caption'|trans" :columns="[
    {label: 'client.field.name'|trans},
    {label: 'client.field.contact'|trans},
    {label: 'client.index.actions'|trans, type: 'actions', hidden: true},
]">
    {% for client in clients %}
        <twig:DataTable:Row>
            <twig:DataTable:Cell type="primary"><a class="data-table__link" href="…">{{ client.name }}</a></twig:DataTable:Cell>
            <twig:DataTable:Cell :label="'client.field.contact'|trans">{{ client.contactName }}</twig:DataTable:Cell>
            <twig:DataTable:Cell type="actions">…icon-only ghost sm buttons…</twig:DataTable:Cell>
        </twig:DataTable:Row>
    {% endfor %}
</twig:DataTable>
```

- DataTable props: `columns: [{label, type?: text|numeric|actions, hidden?: bool}]` (hidden = visually-hidden header, e.g. "Actions"), `caption` (required, visually hidden unless `captionVisible`). The wrapper is a focusable `role="region"` (keyboard scroll when it overflows).
- Cell props: `type = 'text'` (`primary` → `<th scope="row">` semibold; `muted`; `numeric` → mono, right-aligned; `actions` → right-aligned button group), `label` (column label, **required on every non-primary, non-actions cell**: it is shown before the value on mobile).
- States: row hover `color-surface-hover`; sticky header `color-surface-muted`.
- Mobile (< 768px): each row becomes a card (primary cell as title, then `label | value` pairs, actions at the bottom right).
- Do: row actions = icon-only ghost `sm` buttons with a label naming the item ("Supprimer Pharmacie Lumière"); the primary cell links to the edit page. Missing values: a neutral Badge ("Aucune mission") rather than an empty cell.
- Don't: more than 5 columns; actions as text links; a table for the CRA sheet (use `Cra:DaysTable`).

## Calendar — `<twig:Calendar>`

Key component, full spec in **references/calendar.md** (states, view model, keyboard, ARIA, persistence, Live Component variant). Sub-components: `Calendar:Toolbar`, `Calendar:Day`, `Calendar:DayLabel`, `Calendar:Selection`, `Calendar:Legend`, `Calendar:Summary`, `Calendar:DayDialog`.

## Card — `<twig:Card title subtitle>`

Flat surface (`color-surface`, `color-border`, `radius-card`, `elevation-flat`). Props: `title = null`, `subtitle = null`, `headingLevel = 2`. Blocks: content (body, padding `space-inset-lg`), `actions` (header right), `footer` (`color-surface-muted`, right-aligned buttons). Use to frame a form or a group of settings. Don't nest cards.

## Badge — `<twig:Badge tone icon>`

Read-only status pill: `tone = 'neutral'` | `primary` | `success` | `warning` | `error` | `info`, optional `icon`. Tokens `color-<tone>-bg/-text/-border`, `radius-pill`, `font-size-small`. Always text (color is never the only cue). Not clickable: use a Button or Tag.

## Tag — `<twig:Tag icon removable removeLabel>`

Label attached to an item (square-ish `radius-tag`, `color-surface-muted`). Optional remove button (`removable`, accessible `removeLabel`, default "Retirer"); wire it with `data-action` on the Tag. Use for categories/filters; statuses are Badges.

## Tooltip — `<twig:Tooltip text>`

Short text above (default) or below (`position="bottom"`) its content, on hover and keyboard focus; Escape hides it (Stimulus `tooltip`, WCAG 1.4.13). Tokens `color-tooltip-bg`, `color-text-inverse`, `radius-tag`, `elevation-overlay`, max `size-tooltip-max-width`.

- `decorative` (bubble `aria-hidden`): the text repeats the trigger's accessible name — the icon-only button case.
- Otherwise pass `id` and put `aria-describedby="<id>"` on the trigger.
- Don't: essential information, links or buttons inside a tooltip; tooltips on touch-only interactions.

## Toast — `<twig:ToastStack />` and `<twig:Toast>`

Transient feedback after an action. `ToastStack` is in base.html.twig once: it renders **Symfony flash messages** and hosts client toasts.

- Server: `$this->addFlash('success', 'client.flash.created')` (message = translation key). Types: `success`, `info` (alias `notice`), `warning`, `error` (alias `danger`).
- Client: `window.dispatchEvent(new CustomEvent('toast:show', {detail: {tone: 'error', title: '…', message: '…'}}))` — texts must come from translations (e.g. `labels` values passed by Twig).
- Toast props: `tone = 'info'`, `title`, `message = null`, `persistent` (default: true for warning/error).
- Behaviour: success/info auto-dismiss after `duration-toast` (5 s), paused on hover/focus; warning/error stay until closed; close button labelled "Fermer la notification". `role="status"` (success/info) or `role="alert"` (warning/error). Top-right on desktop, above the bottom bar on mobile. `data-turbo-temporary` (not cached by Turbo).
- Tokens: `color-surface-overlay`, `color-<tone>-border`, icon `color-<tone>-text`, `elevation-overlay`, `radius-card`, `size-toast-width`.
- Do: one short sentence in the past tense ("Client ajouté."). Don't: toasts for validation errors (inline field errors) or for information the user must act on (use the page).

## EmptyState — `<twig:EmptyState icon title message>`

Icon in a `color-primary-subtle` disc, title (h2, `font-size-heading-2`), message (`color-text-muted`, max `size-empty-state-max-width`), `actions` block with **one** primary button. Dashed `color-border-strong` frame. Props: `icon`, `title`, `message`, `headingLevel = 2`. Use when a list is empty or a prerequisite is missing (no client yet on the calendar). Hide the PageHeader primary action when the empty state already offers it.

## Modal — `<twig:Modal id title>`

Native `<dialog>` + Stimulus `modal`: `showModal()` (top layer, focus trapped, inert background), Escape and backdrop click close, focus returns to the trigger, closed before Turbo caches the page.

- Props: `id` (required), `title`, `icon = null`, `variant = 'default'` | `'danger'`, `openOnConnect = false` (open at render, e.g. after a failed submit).
- Blocks: content (body), `footer` (buttons, secondary first then primary, right-aligned; full width on mobile).
- Open it from any button: `data-controller="modal-trigger" data-action="modal-trigger#open" data-modal-trigger-id-param="<id>"`. Extra `data-modal-trigger-<key>-param` are forwarded: `action` → form action, `token` → hidden `_token`, other keys → text of `[data-modal-slot="<key>"]`.
- Initial focus: the element with `data-modal-target="initialFocus"` (Cancel in confirmations), else the first focusable.
- Tokens: `color-surface-overlay`, `color-backdrop`, `elevation-modal`, `radius-modal`, `size-modal-width`, `space-inset-lg`.
- Don't: nest dialogs; use a modal for a full form (a page is better); forget the footer's Cancel.

### ConfirmDialog — `<twig:ConfirmDialog id title message confirmLabel tokenId />`

Destructive confirmation (`variant="danger"`, `triangle-alert` icon): subject in bold (`data-modal-slot="subject"`), message, Cancel (initial focus) + `danger` submit inside a POST form with CSRF token (`csrf_token(tokenId)`). **One instance per page**, reused by every row: triggers pass `action` (URL) and `subject` (item name). See `examples/client_index.html.twig`.

- Server: check `isCsrfTokenValid(tokenId, $request->request->get('_token'))`, delete, flash, redirect (303).

## CRA document — `Cra:*`

Screen chrome and document parts of the printable CRA; spec in **references/print.md**.

| Component | Role |
|---|---|
| `<twig:Cra:Toolbar backUrl title />` | back link, document name, PrintButton (`data-print-hide`) |
| `<twig:Cra:Help />` | info panel: how to save as PDF and remove browser headers/footers (`data-print-hide`) |
| `<twig:Cra:Sheet :cra />` | the A4 document: header (month, mission, period, total, date), parties, days table, signatures |
| `<twig:Cra:Party role :party />` | freelance or client block |
| `<twig:Cra:DaysTable :days :total />` | worked days (date, weekday, quantity, note) + total |
| `<twig:Cra:Signatures freelancerName clientName />` | two signature boxes, never split across pages |

## Utilities (`assets/styles/utilities.css`)

`.visually-hidden` (screen-reader only), `.stack` / `.stack--lg` (vertical rhythm), `.cluster` (inline group), `.text-small`, `.text-muted`, `.text-numeric`, `.autosubmit-fallback`. Keep this list short: prefer a component.

## Print attribute

`data-print-hide` on any element hides it when printing (layout, buttons, help, dialogs…). The header, navigation, toasts and PageHeader are hidden by print.css already.
