# Calendar — detailed specification

The monthly calendar is the home page and the core of the product: the user opens it once a month, marks the days worked for one client/mission, and opens the printable CRA. Target: done in 2 minutes, with mouse, keyboard or thumb.

Files: `templates/components/Calendar.html.twig` + `templates/components/Calendar/*.html.twig`, `assets/styles/components/calendar.css`, `assets/controllers/calendar_controller.js`, reference view model in `src/Calendar/` (`CalendarMonth`, `CalendarDay`, `CalendarMonthFactory`, `FrenchHolidays`).

## Anatomy

```
<section.calendar data-controller="calendar">
├── Calendar:Toolbar      ‹ Novembre 2026 ›  [month select]  [Aujourd’hui]    [Cocher les jours ouvrés] [Vider le mois]
├── p#calendar-instructions (visually hidden, referenced by the grid)
├── table.calendar__grid[role=grid]
│   ├── thead: 7 weekday headers, Monday first (abbr: long name; Lun / L on small screens)
│   └── tbody: 5–6 rows × 7 Calendar:Day (days of adjacent months = kind "outside")
├── Calendar:Selection    focused day · its state · its note · [Modifier le jour]
├── live region (role=status, visually hidden)
├── Calendar:Legend
├── details.calendar__help  keyboard shortcuts
├── Calendar:Summary      18,5 jours travaillés · breakdown · [Voir le CRA imprimable]   (sticky at the bottom on desktop)
├── <template>s for day labels (cloned by the controller)
├── Calendar:DayDialog    (Modal: day type + note)
└── Modal#calendar-clear-confirm
```

## Component API

```twig
<twig:Calendar
    :month="month"                     {# CalendarMonth view model #}
    :saveUrl="path('app_calendar_save', {client: client.id})"
    :csrfToken="csrf_token('calendar')"
    :printUrl="path('app_cra', {client: client.id, month: month.id})"
    :clientId="client.id"
    :readonly="false"
/>
```

| Prop | Meaning |
|---|---|
| `month` | view model below |
| `saveUrl` | persistence endpoint; `null` = no persistence (demo) |
| `csrfToken` | sent as `X-CSRF-Token` |
| `printUrl` | CTA of the summary; `null` hides it |
| `clientId` | kept in the month selector query string |
| `readonly` | no quick actions, no selection bar, no dialogs, clicks ignored (`aria-readonly="true"`, `data-readonly`). Use for a locked month. |

When no client exists, do not render the calendar: render an `EmptyState` ("Ajoutez votre premier client") with a primary "Ajouter un client" button (see `examples/calendar.html.twig`).

## View model

Built server-side (`CalendarMonthFactory::create()`), rendered by Twig, then updated client-side by the controller. Arrays with the same keys work too.

`CalendarMonth`

| Field | Example | Notes |
|---|---|---|
| `id` | `'2026-11'` | value of the month select |
| `label` | `'novembre 2026'` | IntlDateFormatter `LLLL y`; capitalised by CSS |
| `weeks` | `CalendarDay[][]` | Monday-first rows of 7, including outside days |
| `focusDate` | `'2026-11-17'` | the only day with `tabindex="0"`: today if in the month, else the 1st |
| `isCurrent` | `true` | hides the "Aujourd’hui" button |
| `previousUrl`, `nextUrl`, `currentUrl` | | month navigation links (keep the client param) |
| `formAction` | `/` | month select GET form action, **without** query string |
| `monthOptions` | `[{value: '2026-11', label: 'Novembre 2026'}, …]` | last 12 months → next 2, most recent first |
| `counts()` | `{full: 11, half: 1, off: 2, total: 11.5}` | initial summary |
| `workdayCount()` | `20` | Mon–Fri minus public holidays |

`CalendarDay`

| Field | Example | Notes |
|---|---|---|
| `date` | `'2026-11-13'` | ISO date, `data-date` |
| `number` | `13` | |
| `kind` | `workday` · `weekend` · `holiday` · `outside` | calendar fact, set by the server only |
| `state` | `empty` · `full` · `half` · `off` | user input |
| `note` | `'Démo client (après-midi)'` | ≤ 140 chars, printed on the CRA |
| `isToday` | `false` | |
| `holidayName` | `'Armistice 1918'` | French name given by `FrenchHolidays`, holidays only |
| `label` | `'vendredi 13 novembre 2026'` | IntlDateFormatter `EEEE d MMMM y`, start of the accessible name |

Quantity of a day: `full` = 1, `half` = 0.5, others = 0. **Total = full + half / 2.** `off` (leave/absence) is informative: counted in the summary, not printed in the CRA table.

### French public holidays

`FrenchHolidays::forYear($year, $alsaceMoselle = false)`: 1er janvier, lundi de Pâques, 1er mai, 8 mai, Ascension, lundi de Pentecôte, 14 juillet, 15 août, 1er novembre, 11 novembre, 25 décembre; Alsace-Moselle adds Vendredi saint and 26 décembre (expose it as a profile setting if needed). It returns the French name of each holiday, keyed by date. A holiday on a weekend keeps `kind: holiday`.

## Day cell states

Two orthogonal attributes on the gridcell (`td.calendar-day`), styled from CSS only:

- `data-kind` (fact): `workday` | `weekend` | `holiday` | `outside`
- `data-state` (input): `empty` | `full` | `half` | `off`
- flags: `data-today`, `data-has-note`, `data-disabled`, `data-pending` (save in flight)

State rules are declared after kind rules: **a worked weekend or holiday looks worked** (its kind stays in the accessible name).

| Appearance | Selector | Visual (never color alone) | Tokens |
|---|---|---|---|
| Empty workday | `[data-kind=workday][data-state=empty]` | white cell, thin border | `color-day-bg`, `color-day-border`, `color-day-text` |
| Worked, full day | `[data-state=full]` | solid teal fill + ✓ "Journée" | `color-day-worked-bg`, `-worked-text`, hover `-worked-hover-bg` |
| Half day | `[data-state=half]` | diagonal split: light half with the number, solid half with "½ j", 2px border | `color-day-half-bg`, `color-day-worked-bg`, `color-day-half-border` |
| Leave / absence | `[data-state=off]` | diagonal hatch + palm icon "Congé" | `color-day-off-bg`, `-off-hatch`, `-off-text` |
| Weekend | `[data-kind=weekend]` | grey cell, muted number, no border | `color-day-weekend-bg`, `-weekend-text` |
| Public holiday | `[data-kind=holiday]` | amber tint + border, flag icon + holiday name | `color-day-holiday-bg`, `-holiday-border`, `-holiday-text` |
| Today | `[data-today]` | number in a filled disc + inset ring (inverse disc when worked); `aria-current="date"` | `color-day-today-bg/-text`, `-today-inverse-bg/-text`, `-today-ring` |
| Outside the month | `[data-kind=outside]` | dashed border, faded number, not interactive, `aria-disabled="true"` | `color-day-outside-text`, `color-day-border` |
| Has note | `[data-has-note]` | note icon top-right (dot on mobile) | inherits text color |
| Hover | `:hover` | `--day-bg-hover` of the state | `color-day-hover-bg`, `-worked-hover-bg` |
| Focus | `.calendar-day__button:focus-visible` | 2px focus ring, offset 2px | `color-focus-ring` |
| Disabled / read-only | `[data-disabled]`, `.calendar[data-readonly]` | no hover, `cursor: not-allowed` | — |
| Saving | `[data-pending]` | `cursor: progress` | — |

Visible labels are decorative (`aria-hidden`); the button's `aria-label` carries everything: `"{label}[, jour férié : {name}][, week-end][, aujourd’hui], {state}[, Note : {note}]"`.

The names of the states ("Non travaillé", "Journée complète", "Demi-journée", "Congé ou absence") are written once, in `labels.states` of `Calendar.html.twig`. `Calendar` gives them to the controller (`labels` value) and to each `Calendar:Day` (prop `stateLabel`), so the accessible name is the same whether the server or the controller wrote it.

Layout: desktop cells ≥ `size-day-min-height` (80px), gap `space-calendar-gap`, number top-left, label bottom-left. Mobile (< 768px): cells ≥ 48px, gap 4px, labels hidden (fills, split, hatch and borders remain), note as a dot, weekday initials under 480px.

## Interactions

### Pointer / touch

- **Click / tap a day**: cycles `empty → full → half → empty` (`off → empty`). The day becomes the selected day.
- **"Modifier le jour"** (selection bar): opens the day dialog for the selected day — the way to set *Congé ou absence* and a note with a mouse or on touch.
- **Cocher les jours ouvrés**: every `workday` in state `empty` becomes `full` (weekends, holidays, leave and existing half days untouched). One request.
- **Vider le mois**: opens `#calendar-clear-confirm` (danger modal, Cancel focused); confirming sets every day to `empty` and clears notes. One request.
- **Month navigation**: previous/next links, month select (auto-submitted GET form), "Aujourd’hui" when not on the current month. All are Turbo visits (full page render: no client-side month building).

### Keyboard (grid pattern, roving tabindex)

The grid is **one tab stop**: exactly one day button has `tabindex="0"` (initially `month.focusDate`); arrow keys move focus.

| Key | Action |
|---|---|
| `←` `→` | previous / next day (skips outside days) |
| `↑` `↓` | same weekday, previous / next week (clamped to first / last day) |
| `Home` / `End` | first / last day of the week row |
| `Ctrl`/`⌘` + `Home` / `End` | first / last day of the month |
| `Page Up` / `Page Down` | previous / next month (follows the nav links) |
| `Enter` / `Space` | cycle state (native button click) |
| `J` | journée complète (`full`) |
| `D` | demi-journée (`half`) |
| `C` | congé / absence (`off`) |
| `Suppr` / `Retour arrière` | non travaillé (`empty`) |
| `N` | open the day dialog (type + note) |
| `Esc` | closes the dialog (native), focus returns to the day |

The shortcut list is visible in `details.calendar__help` and summarised in `#calendar-instructions` (grid `aria-describedby`). The selection button has `aria-keyshortcuts="N"`.

### Day dialog (`Calendar:DayDialog`)

Modal "Modifier le jour": date line, SegmentedControl "Type de journée" (Journée complète / Demi-journée / Congé ou absence / Non travaillé; initial focus on the current state), Note textarea (optional, 140 chars max, help "Affichée dans le CRA imprimé"), Cancel + primary "Enregistrer" (submit). Saving applies state + note, closes, and returns focus to the day.

## Accessibility

- `<table role="grid" aria-labelledby="calendar-title" aria-describedby="calendar-instructions">`, `role="row"`, `role="columnheader"` (with `<abbr title="lundi">`), `role="gridcell"`. The day `<button>` inside each cell is the interactive element.
- Outside days: `aria-disabled="true"` cell, content `aria-hidden`, no button.
- Today: `aria-current="date"` on the cell.
- Changes are announced in a polite live region: `"mercredi 18 novembre 2026 : Journée complète. Total : 12,5 jours travaillés."`; bulk actions: `"Jours mis à jour (20). Total : …"`.
- Read-only: `aria-readonly="true"` on the grid.
- Contrast: every day text/background pair ≥ 4.5:1, fills and rings ≥ 3:1 against the empty cell, in both themes (see foundations.md).
- States differ by shape, not only color: solid, split, hatch, tint + border, dashed; labels on desktop; full accessible names everywhere.
- Touch targets: cells ≥ 48 × 44px on mobile.

## Summary bar (`Calendar:Summary`)

`{total} {jour(s) travaillé(s)}` in `font-family-numeric` `font-size-heading-1` `color-text-primary`, breakdown (`Journées complètes`, `Demi-journées`, `Congés`, `Jours ouvrés du mois`), primary CTA "Voir le CRA imprimable". Server-rendered, then recomputed by the controller after each change (`data-calendar-target="summaryValue"` + `data-key`). Numbers formatted with `Intl.NumberFormat(lang)` (`12,5`), unit with `Intl.PluralRules` (French: "0 jour", "1,5 jour", "2 jours"; both forms are in `labels.unit`, and the server uses the singular under 2). Sticky at the bottom of the viewport on desktop (`elevation-raised`); static on mobile.

## Persistence

Stimulus mode (default): optimistic update, then one request per user action.

```
POST {saveUrl}
Content-Type: application/json
Accept: application/json
X-CSRF-Token: {csrfToken}

{"days": [{"date": "2026-11-18", "state": "full", "note": ""}, …]}
```

- Server: validate the token (`isCsrfTokenValid('calendar', $request->headers->get('X-CSRF-Token'))`), that every date belongs to the month and client, `state ∈ CalendarDay::STATES`, note ≤ 140 chars; upsert (`empty` with no note = delete the row); answer `204 No Content` (or `200` JSON). 4xx/5xx = failure.
- Failure: the controller restores the previous states, recomputes the summary and shows an error toast ("Enregistrement impossible" / "Vos dernières modifications ont été annulées…").
- While a request is in flight the affected days carry `data-pending`.

### Live Component variant (optional)

If the calendar becomes a Live Component (`#[AsLiveComponent('Calendar')]` PHP class + the same template), keep **exactly the same markup, classes and data attributes**, and:

- replace `click->calendar#cycle` by `data-action="live#action" data-live-action-param="cycle" data-live-date-param="{{ day.date }}"` on the day button, and quick actions by `live#action` with `fillWorkdays` / `clearMonth`;
- keep `calendar_controller.js` for keyboard navigation, the roving tabindex and the day dialog only (drop `persist()`; the Live Component re-renders);
- add `data-live-ignore` nowhere on the grid (it must re-render), but keep the focused date in a `LiveProp` so the re-render restores `tabindex="0"` and focus.

Choose one mode per project; do not mix persistence mechanisms.

## Do / Don't

- Do: build the month server-side (factory), including holidays and outside days; the client never computes calendars.
- Do: keep the grid a real `<table>`; one gridcell per day; one button per in-month day.
- Do: route every new day appearance through `data-*` + CSS tokens (`color-day-*`); add a legend entry and a `DayLabel` branch.
- Don't: add a second focusable element inside a day cell (breaks the grid pattern): use the selection bar / dialog.
- Don't: show a day's state with color only, or hide the kind (holiday/weekend) from the accessible name.
- Don't: hard-code French strings in the controller: pass them through the `labels` value (written in `Calendar.html.twig`).
