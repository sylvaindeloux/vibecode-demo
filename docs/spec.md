# EasyCRA: product specification

| | |
|---|---|
| Status | Draft, to be reviewed before any code is written |
| Date | 2026-09-29, second version |
| Target versions | Symfony 8.1 (8.1.8 today), Symfony UX 3.5, DoctrineBundle 3.3, Doctrine ORM 3.7, PHP 8.4 or later |
| Sources | `cra-design-system` skill (`references/screens.md`, `calendar.md`, `print.md`, `components.md`, `symfony-integration.md`), `symfony-bp-*` skills, decisions of appendix A |

## How to read this document

- The design system is the source of truth for everything visual and for the behaviour of its components. This document does not restate it: it points to it and adds what the design system does not define (data, rules, server behaviour, tests).
- Identifiers: `US-nn` user story, `AC` acceptance criterion, `BR-nn` business rule, `D-nn` decision (appendix A).
- French texts between quotation marks are user interface strings. They are written directly in the files that show them: there are no translation keys and no translation files (D-19).
- If this document and the design system disagree, the design system wins and this document is corrected.

## 1. Vision and scope

### 1.1 Vision

EasyCRA helps a French freelancer produce the monthly CRA ("compte rendu d'activité") attached to an invoice: the list of days worked for one client during one month, signed by both parties.

The tool is opened once a month and closed after two minutes: mark the days, open the printable CRA, save it as a PDF from the browser.

### 1.2 Users and context

- One persona: a freelance developer or consultant who bills by the day.
- This version is a demo that runs on the user's own machine only. There is no hosting, no authentication and no account: the person in front of the browser is the single, implicit user.

### 1.3 Guiding principles

They take precedence over everything else in this document.

1. **As little code as possible.** Every line must be justified.
2. **Do not reinvent the wheel.** Use what Symfony, Symfony UX, Doctrine and Twig provide before writing anything.
3. **No third-party dependency** outside the Symfony, Doctrine and Twig ecosystems, unless there is no other way. Exceptions are listed and justified in section 7.3.
4. **Apply the `symfony-bp-*` skills** and the rules of `cra-design-system`.

### 1.4 In scope

| Area | Content |
|---|---|
| Profile | The freelancer's identity printed on every CRA |
| Clients | List, create, edit, delete. One client carries one mission |
| Calendar | Monthly calendar per client: day states, notes, bulk actions, totals, month navigation |
| Printable CRA | HTML page printed to PDF by the browser |
| Layout | Shared header, navigation, light and dark themes, mobile bottom bar |
| Demo | Demo data and a reset command |

### 1.5 Out of scope

| Excluded | Note |
|---|---|
| Authentication, accounts, several users | Single implicit user (D-01) |
| Hosting, deployment, production configuration | Local demo only |
| Invoicing, accounting, rates, amounts | The CRA counts days, not money |
| Server-side PDF generation | The browser prints the page |
| Exports (CSV, spreadsheet), e-mail sending | |
| Several missions per client | Two missions for the same client are two client records (D-02) |
| Locking a month | The `readonly` mode of the calendar component is not used |
| Hourly tracking | Units are the day and the half day |
| Alsace-Moselle public holidays | Metropolitan France only (D-06) |
| Other languages, translation layer | French interface only, texts written in the files (D-19) |
| Consistency between clients | The same date can be marked for several clients (BR-09) |
| Undo, history, import | Deletions are final |

## 2. User stories

Persona: Camille, freelance consultant.

### US-01: Fill in my profile

As a freelancer, I want to record my identity once, so that it is printed on every CRA.

Acceptance criteria:

1. `/profile` shows the form of `screens.md` section 4, filled with the saved values.
2. A valid submission saves the profile, shows the toast "Profil enregistré." and stays on `/profile`.
3. A submission without name, SIRET or address, or with an invalid e-mail, answers HTTP 422, shows an error under each invalid field and saves nothing.
4. `812 345 678 00013` and `81234567800013` are both accepted, and both are stored as `81234567800013`.
5. The saved SIRET is shown as `812 345 678 00013` in the field and on the CRA.
6. A SIRET that does not contain exactly 14 digits is rejected with "Le SIRET compte 14 chiffres."
7. A SIRET of 14 digits that fails the Luhn check is rejected with "Ce numéro SIRET n’est pas valide : vérifiez les chiffres saisis."
8. There is never more than one profile.

### US-02: See my clients

As a freelancer, I want to see my clients and their missions, so that I can manage them.

Acceptance criteria:

1. `/clients` lists the clients sorted by name, with the columns of `screens.md` section 3a.
2. Without any client, the page shows the empty state of `screens.md` section 3b and no table.
3. The client name links to the edit page.
4. Under 768px, each row is shown as a card.

### US-03: Add or edit a client

As a freelancer, I want to record a client and its mission, so that I can fill a calendar for it.

Acceptance criteria:

1. `/clients/new` and `/clients/{id}/edit` show the form of `screens.md` section 3c.
2. A valid submission saves the client, redirects to the list with HTTP 303 and shows "Client ajouté." or "Client mis à jour."
3. A submission without name or mission, or with an invalid contact e-mail, answers HTTP 422 with an error under each invalid field.
4. "Annuler" goes back to the list without saving.
5. `/clients/{id}/edit` with an unknown id answers HTTP 404.

### US-04: Delete a client

As a freelancer, I want to delete a client I no longer work for.

Acceptance criteria:

1. The trash button opens the confirmation dialog, which names the client and warns that its days will be deleted.
2. Confirming deletes the client and all its day entries, redirects to the list with HTTP 303 and shows "Client supprimé."
3. Cancelling, the Escape key or a click on the backdrop closes the dialog and deletes nothing.
4. A request without a valid CSRF token answers HTTP 403 and deletes nothing.
5. A `GET` request on the delete URL never deletes anything.

### US-05: Open the calendar of a mission for a month

As a freelancer, I want to open the calendar of one mission and one month.

Acceptance criteria:

1. `/` shows the current month for the client selected by BR-13.
2. The client picker lists every client as "name · mission" and reloads the calendar for the chosen client, keeping the month.
3. The previous and next links, the month list and "Aujourd'hui" change the month and keep the client.
4. Weekends, public holidays (with their name), today and the days of adjacent months are shown as specified in `calendar.md`.
5. Without any client, the page shows the empty state "Ajoutez votre premier client" instead of the calendar.
6. An invalid `month` parameter or an unknown `client` parameter answers HTTP 404.

### US-06: Mark the days I worked

As a freelancer, I want to mark a day with one click or one key.

Acceptance criteria:

1. A click or tap on a day cycles `empty → full → half → empty`, and `off → empty`.
2. The keyboard shortcuts of `calendar.md` work, with one tab stop for the whole grid.
3. Each change is saved immediately. After a reload, the calendar shows the same states.
4. When the save fails, the day goes back to its previous state and an error toast is shown.
5. Weekends and public holidays can be marked as worked.
6. Each change is announced in the live region, with the new total.

### US-07: Record a leave day or a note

As a freelancer, I want to mark a leave day and to comment a day.

Acceptance criteria:

1. "Modifier le jour" and the `N` key open the day dialog for the selected day.
2. The dialog sets the type of day (four choices) and a note of 140 characters at most.
3. Saving applies both values, closes the dialog and gives the focus back to the day.
4. A day with a note shows the note marker, and its note is part of its accessible name.

### US-08: Fill or clear a month in one action

As a freelancer, I want to fill a whole month quickly.

Acceptance criteria:

1. "Cocher les jours ouvrés" sets every empty workday to `full`. Weekends, public holidays, leave days, half days and notes are left untouched.
2. "Vider le mois" asks for confirmation, then sets every day to `empty` and removes every note.
3. Each of these actions sends one request.

### US-09: Follow my totals

As a freelancer, I want to see the total of the month at any time.

Acceptance criteria:

1. The summary bar shows the total of days worked, the number of full days, half days and leave days, and the number of workdays of the month.
2. The figures are updated after each change, without reloading the page.
3. Numbers use the French format ("12,5") and the unit agrees with the number ("1,5 jour travaillé", "2 jours travaillés").

### US-10: Produce the CRA as a PDF

As a freelancer, I want a printable CRA, so that I can attach it to my invoice.

Acceptance criteria:

1. "Voir le CRA imprimable" opens `/cra/{client}/{month}` for the client and month shown in the calendar.
2. The sheet contains what BR-18 lists, in that order.
3. A month without any worked day shows "Aucun jour travaillé ce mois-ci." and a total of 0.
4. The page title, which is the file name proposed by the browser, is `CRA - {client} - {Mois AAAA}`.
5. The print preview shows only the sheet, on A4 portrait. A full month fits on one page.
6. When the profile is incomplete, the user is redirected to `/profile` with a warning toast (BR-17).
7. "Retour au calendrier" goes back to the calendar of the same client and month.

### US-11: Use the application comfortably

As a user, I want a readable interface on any screen.

Acceptance criteria:

1. The theme switch offers light, dark and automatic. The choice is kept for a year and applied without a flash on the next page.
2. Under 768px, the navigation is a bottom tab bar.
3. Every page works with the keyboard only.
4. Every page except the in-place editing of the calendar works without JavaScript.

### US-12: Reset the demo

As the person presenting the demo, I want to come back to a known state with one command.

Acceptance criteria:

1. `composer reset` deletes the database, creates the schema and loads the demo data, without asking any question.
2. After a reset, the home page shows a calendar with data for the current month.
3. The command works on a fresh clone, after `composer install`.

## 3. Data model

Three entities, mapped with PHP attributes (`symfony-bp-business-logic`, rule 6). Validation constraints are on the entities (`symfony-bp-forms`, rule 4). Each custom message is a French text written in the constraint, the same as in `screens.md`. A constraint without a custom message shows the French message shipped with Symfony (D-20).

```text
Profile (single row)

Client 1 ──── * DayEntry      a day entry belongs to one client
```

### 3.1 `Profile`

| Field | Doctrine type | Nullable | Constraints |
|---|---|---|---|
| `id` | `integer`, generated | no | |
| `name` | `string(255)` | no | `NotBlank` "Indiquez votre nom.", `Length(max: 255)` |
| `company` | `string(255)` | yes | `Length(max: 255)` |
| `siret` | `string(14)` | no | `Sequentially`: `NotBlank`, then `Regex('/^\d{14}$/')`, both "Le SIRET compte 14 chiffres."; then `Luhn` "Ce numéro SIRET n’est pas valide : vérifiez les chiffres saisis." |
| `address` | `text` | no | `NotBlank` "Indiquez votre adresse." |
| `email` | `string(255)` | yes | `Email` "Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).", `Length(max: 255)` |

- The table holds at most one row. The repository returns that row, or a new empty `Profile` when the table is empty. The row is created by the first valid submission or by the fixtures.
- The entity only ever holds the 14 digits of the SIRET. Spaces are handled by the form, not by the entity (BR-16).
- `Sequentially` stops at the first constraint that fails, so that the field shows one message at a time.

### 3.2 `Client`

| Field | Doctrine type | Nullable | Constraints |
|---|---|---|---|
| `id` | `integer`, generated | no | |
| `name` | `string(255)` | no | `NotBlank` "Indiquez le nom du client.", `Length(max: 255)` |
| `address` | `text` | yes | |
| `contactName` | `string(255)` | yes | `Length(max: 255)` |
| `contactEmail` | `string(255)` | yes | `Email` "Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).", `Length(max: 255)` |
| `mission` | `string(255)` | no | `NotBlank` "Indiquez le nom de la mission.", `Length(max: 255)` |

- No uniqueness constraint: the same client name can appear twice, with two missions (D-02).
- No owner: there is no user entity (D-01).
- `Client` has no collection of day entries (D-04).

### 3.3 `DayEntry`

| Field | Doctrine type | Nullable | Constraints |
|---|---|---|---|
| `id` | `integer`, generated | no | |
| `client` | `ManyToOne` to `Client`, `JoinColumn(nullable: false, onDelete: 'CASCADE')` | no | |
| `date` | `date_immutable` | no | |
| `state` | `string(5)` | no | `Choice(choices: CalendarDay::STATES)` |
| `note` | `string(140)` | no, default `''` | `Length(max: 140)` |

- Unique constraint on (`client`, `date`).
- `state` is stored as a string and reuses the constants of `App\Calendar\CalendarDay`, delivered by the design system (D-03).
- A day in state `empty` without a note has no row (BR-05).
- `quantity()` returns `1.0` for `full`, `0.5` for `half` and `0.0` otherwise.

### 3.4 Database

- SQLite, one file per environment: `var/data_dev.db`, `var/data_test.db`. `DATABASE_URL` is an environment variable (`symfony-bp-configuration`, rule 1).
- SQLite does not enforce foreign keys by default. The DBAL middleware `Doctrine\DBAL\Driver\AbstractSQLiteDriver\Middleware\EnableForeignKeys` is declared as a service in `config/services.yaml`. DoctrineBundle tags it through autoconfiguration, so the declaration is one line.
- The schema is created from the mapping with `doctrine:schema:create`. There are no migrations (D-12): a change of the model requires a reset of the database.

## 4. Business rules

### 4.1 Days

**BR-01: kind of a day.** The kind is a calendar fact computed by the server: `workday` (Monday to Friday), `weekend`, `holiday`, or `outside` (day of an adjacent month shown to complete a week). A public holiday that falls on a weekend has the kind `holiday`.

**BR-02: state of a day.** The state is what the user enters, per client and per date: `empty` (default), `full`, `half` or `off` (leave or absence). Kind and state are independent: any day of the month can take any state, including weekends and public holidays. `outside` days cannot be changed.

**BR-03: changing a state.** The three ways are those of `calendar.md`: click or tap (cycle), keyboard shortcut, day dialog. Only the dialog and the `C` key set `off`.

**BR-04: note.** A note is optional, trimmed, 140 characters at most, and can be attached to a day in any state. It is printed on the CRA only when the day is `full` or `half`.

**BR-05: stored entries.** A row exists for a client and a date if, and only if, the state is not `empty` or the note is not empty. Saving an `empty` state without a note deletes the row.

**BR-06: quantity and total.** `full` counts for 1, `half` for 0.5, `empty` and `off` for 0. The total of a month is the number of full days plus half the number of half days.

**BR-07: leave days.** `off` days are informative. They are counted in the summary of the calendar. They are not part of the total and are not printed on the CRA.

**BR-08: workdays of the month.** The number shown in the summary is the number of days from Monday to Friday, minus the public holidays that fall on those days.

**BR-09: no consistency between clients.** Entries are independent from one client to another: the same date can be marked for several clients, and the sum over clients can exceed one day. The user is free (D-22).

**BR-10: bulk actions.** "Cocher les jours ouvrés" changes only the days of kind `workday` in state `empty`. "Vider le mois" sets every day of the month to `empty` and removes every note, after confirmation.

### 4.2 Public holidays

**BR-11.** Public holidays are those of metropolitan France, computed for any year by `App\Calendar\FrenchHolidays` (design system): 1 January, Easter Monday, 1 May, 8 May, Ascension Thursday, Whit Monday, 14 July, 15 August, 1 November, 11 November, 25 December. No external service and no data file are used. The holidays of Alsace-Moselle are out of scope.

A public holiday is never pre-filled and never blocks input: it is a day like any other for BR-02.

### 4.3 Dates

**BR-12: months and today.**

- A month is identified by `YYYY-MM` and must match `\d{4}-(0[1-9]|1[0-2])`. Any other value answers HTTP 404.
- There is no lower or upper bound: every valid month can be reached with the previous and next links or by its URL (D-05).
- The month list offers the 12 past months, the current month and the next 2 months, most recent first, plus the displayed month when it is outside this range.
- Days in the future can be marked.
- Without a `month` parameter, the calendar shows the current month.
- "Today" is the current date in the `Europe/Paris` time zone, read from the Symfony clock. Dates have no time part.

### 4.4 Clients

**BR-13: selected client of the calendar.** In this order:

1. the `client` query parameter. An unknown id answers HTTP 404;
2. the `client` cookie (last client shown), when it designates an existing client. Otherwise it is ignored;
3. the first client by name.

Each calendar page shown for a client writes the `client` cookie: client id, one year, path `/`, `SameSite=Lax`, `HttpOnly`.

**BR-14: deleting a client.** The deletion is final and also deletes every day entry of the client, through the `ON DELETE CASCADE` of the foreign key. It requires a `POST` request with a valid CSRF token, sent by the confirmation dialog. Nothing can be restored.

**BR-15: order.** Clients are sorted by name, in the list and in the client picker.

### 4.5 Profile

**BR-16: SIRET.**

- Input: spaces are accepted anywhere in the value.
- Storage: digits only, 14 characters.
- Validation: exactly 14 digits, then a valid Luhn checksum (Symfony `Luhn` constraint).
- Display: always in groups of 3, 3, 3 and 5 digits (`812 345 678 00013`), in the field of the profile and on the CRA (D-18).
- The form does both conversions: a data transformer on the `siret` field of `ProfileType` groups the digits for display and removes the spaces on submission. On the CRA, the `Cra:Party` component of the design system groups the digits.
- Known limit: the establishments of La Poste have SIRET numbers that do not follow the Luhn rule. They are rejected. This is accepted for a tool aimed at freelancers.

**BR-17: complete profile.** A profile is complete when it exists and passes the validation of section 3.1. The CRA cannot be shown with an incomplete profile: the request is redirected to `/profile` with the warning toast "Complétez votre profil pour ouvrir le CRA." (D-08).

### 4.6 Printed CRA

**BR-18: content.** One CRA covers one client and one month. The sheet contains, in this order:

| Block | Content | Source |
|---|---|---|
| Header | "Compte rendu d'activité", month and year as the title, mission, period (first to last day of the month), total of days worked, "Établi le" | `Client.mission`, BR-06, BR-19 |
| Parties | Freelancer: name, company, address, SIRET, e-mail. Client: name, address, contact name and e-mail | `Profile`, `Client` |
| Days | One row per day in state `full` or `half`, in chronological order: date, weekday, quantity (`1` or `0,5`), note. Total row | `DayEntry` |
| Signatures | Two boxes: the freelancer (profile name) and the client (contact name, or client name when there is no contact) | |

- Empty optional values (company, e-mails, client address, contact) print nothing: no label, no blank line.
- `off` and `empty` days are not printed, even when they have a note.
- A month without any worked day is allowed: the table shows one row "Aucun jour travaillé ce mois-ci." and the total is 0.
- The CRA always shows the current data. Nothing is frozen or archived when it is printed.

**BR-19: "Établi le".** The date is today (BR-12), the day the page is displayed (D-09).

**BR-20: file name.** The page title is `CRA - {client} - {Mois AAAA}`, with the month capitalised and the characters `/`, `\` and `:` of the client name replaced by `-`. The title has no application name suffix.

## 5. Screens and routes

Composition, fields, texts and responsive behaviour of each screen are defined in `references/screens.md`. This section adds the server behaviour.

### 5.1 Routes

Route names are those of the design system: the header and the components use them.

| Route | Method and path | Controller action | Template | Success |
|---|---|---|---|---|
| `app_calendar` | `GET /` with `?client=` and `?month=` | `CalendarController::index` | `calendar/index.html.twig` | 200 |
| `app_calendar_save` | `POST /calendar/{client}/{month}` | `CalendarController::save` | none (JSON endpoint) | 204 |
| `app_cra` | `GET /cra/{client}/{month}` | `CraController::show` | `cra/show.html.twig` | 200 |
| `app_client_index` | `GET /clients` | `ClientController::index` | `client/index.html.twig` | 200 |
| `app_client_new` | `GET, POST /clients/new` | `ClientController::new` | `client/form.html.twig` | 200, then 303 |
| `app_client_edit` | `GET, POST /clients/{id}/edit` | `ClientController::edit` | `client/form.html.twig` | 200, then 303 |
| `app_client_delete` | `POST /clients/{id}/delete` | `ClientController::delete` | none | 303 |
| `app_profile` | `GET, POST /profile` | `ProfileController::edit` | `profile/edit.html.twig` | 200, then 303 |
| `design_system_*` | `GET /_design-system/...` | `DesignSystemController` (design system, `dev` environment only) | `examples/*.html.twig` | 200 |

Common rules:

- `{client}` and `{id}` are loaded by the entity value resolver: an unknown id answers 404.
- `{month}` has the requirement of BR-12 and is converted to a date by the date value resolver.
- An invalid form answers 422. A successful `POST` redirects with 303. Both are required by Turbo Drive.
- Flash messages are French texts, shown as toasts by the layout.
- Page templates are copies of the reference screens `templates/examples/*.html.twig`, wired to real data. Template names and the variables passed by controllers are in snake_case (`symfony-bp-templates`).

### 5.2 Calendar (`app_calendar`)

- Resolves the client (BR-13) and the month (BR-12).
- Loads the entries of the client for the month and builds the view model with `CalendarMonthFactory` (design system).
- Variables: `clients`, `client` (null when there is no client), `month`, `save_url`, `print_url`.
- Without any client: answers 200 with the empty state and does not build the calendar.
- Writes the `client` cookie (BR-13).

### 5.3 Saving days (`app_calendar_save`)

The contract is the one of `calendar.md`, section "Persistence". The Stimulus controller of the design system is used as delivered (D-10).

Request:

```http
POST /calendar/1/2026-11
Content-Type: application/json
X-CSRF-Token: <token of id "calendar">

{"days": [{"date": "2026-11-18", "state": "full", "note": ""}]}
```

| Case | Answer |
|---|---|
| Every day is valid | 204, entries created, updated or deleted (BR-05) |
| Missing or invalid CSRF token | 403 |
| Unknown client, invalid month | 404 |
| Body that is not valid JSON | 400 |
| Empty list or more than 31 days, invalid date, date outside `{month}`, unknown state, note longer than 140 characters | 422 |

- The request is atomic: when one day is invalid, nothing is saved.
- When the same date appears twice in a request, the last occurrence wins.
- The controller only checks the token and delegates. The write logic is in `DayEntryRepository`.

### 5.4 Printable CRA (`app_cra`)

- Redirects to `app_profile` when the profile is incomplete (BR-17).
- Variables: `cra` (keys of `print.md`, section "Data") and `back_url`.
- The `cra` variable is an array assembled from the entities, which are passed as they are (`freelancer` is the `Profile`, `client` is the `Client`, `days` are the `DayEntry` objects). There is no dedicated view model class.
- The page extends the shared layout. The print style sheet hides everything but the sheet.

### 5.5 Clients

- `index`: all clients sorted by name. The confirmation dialog is rendered once and reused by every row.
- `new` and `edit`: one action renders and processes the form (`symfony-bp-forms`, rule 5). Both use the same template, with `client` null when creating.
- `delete`: checks the CSRF token of id `delete-client` sent in the `_token` field, deletes, adds the flash message, redirects. An invalid token answers 403.

### 5.6 Profile

- One action renders and processes the form.
- After a valid submission: flash "Profil enregistré." and redirect to `app_profile` with 303.

### 5.7 Shared layout

`templates/base.html.twig` of the design system, unchanged: skip link, header, navigation, theme switch, toasts, dialogs block. The theme is read from the `theme` cookie by the template, so that no script runs before the first paint.

## 6. Demo data and reset

### 6.1 Fixtures

One fixture class, `App\DataFixtures\AppFixtures`, loaded by DoctrineFixturesBundle. No data generator is used: the data is written in the class.

Profile:

| Field | Value |
|---|---|
| Name | Camille Durand |
| Company | Durand Conseil SASU |
| SIRET | `81234567800013` |
| Address | 25 rue des Lilas, 75020 Paris (two lines) |
| E-mail | camille@durand-conseil.fr |

The SIRET is fictitious, and the same as in the design system gallery. Its 14 digits pass the Luhn check, so the application accepts it. Its first 9 digits, the SIREN, fail the Luhn check that every real SIREN passes, so it cannot belong to a real company.

Clients:

| # | Name | Contact | E-mail | Mission | Address |
|---|---|---|---|---|---|
| 1 | Pharmacie Lumière | Claire Martin | claire.martin@pharmacie-lumiere.fr | Refonte du back-office | 12 rue de la Paix, 75002 Paris |
| 2 | Atelier Numérique | Hugo Bernard | hugo@atelier-numerique.fr | Audit de performance | 4 quai des Chartrons, 33000 Bordeaux |
| 3 | Coopérative Horizon | Inès Robert | none | Accompagnement technique | 8 place Bellecour, 69002 Lyon |

Day entries are relative to the loading date (D-14). They cover the two previous months and the current month, and only the dates strictly before today. "Nth workday" means the Nth day of kind `workday` of the month.

| Client | Rule | State | Note |
|---|---|---|---|
| 1 | Every workday, unless a rule below applies | `full` | |
| 1 | 3rd workday | `full` | Atelier de cadrage |
| 1 | 5th workday | `half` | Démo client (après-midi) |
| 1 | 10th and 11th workdays | `off` | |
| 1 | 15th workday | no entry | |
| 1 | 2nd Saturday | `full` | Mise en production |
| 2 | 5th workday | `half` | Restitution (matin) |
| 2 | 15th workday | `full` | |
| 3 | No entry | | |

Together, the three clients show every case: full days, a half day with a note, leave days, a worked Saturday, an empty calendar and an empty CRA.

Worked example, with today set to Tuesday 17 November 2026 (the date used by the tests):

| Client and month | Full | Half | Off | Total |
|---|---|---|---|---|
| 1, November 2026 | 9 (2, 3, 4, 5, 9, 10, 12, 13 and Saturday 14) | 1 (the 6th) | 1 (the 16th) | 9,5 |
| 1, October 2026 | 19 | 1 | 2 | 19,5 |
| 2, October 2026 | 1 | 1 | 0 | 1,5 |
| 3, any month | 0 | 0 | 0 | 0 |

### 6.2 Reset command

A Composer script, without any PHP code (D-13):

```json
{
    "scripts": {
        "reset": [
            "@php bin/console doctrine:database:drop --force --if-exists",
            "@php bin/console doctrine:schema:create",
            "@php bin/console doctrine:fixtures:load --no-interaction"
        ]
    }
}
```

First start on a fresh clone:

```bash
composer install
composer reset
symfony serve
```

The reset acts on the `dev` database only. It deletes everything the user entered.

## 7. Technical choices

### 7.1 One brick per need

| Need | Brick | Note |
|---|---|---|
| Create the project | Symfony CLI, `symfony new --version="8.1.*"` | Minimal skeleton, not `--webapp` (D-11). Generated in a temporary directory, then copied, because the repository already exists |
| Generate code | MakerBundle: `make:entity`, `make:form`, `make:controller`, `make:fixtures`, `make:test` | Generated code that is not needed is removed |
| Routing and controllers | `#[Route]` attributes, `AbstractController` | Thin controllers, services injected as arguments |
| Load a client from the URL | Entity value resolver | 404 when not found |
| Read the month from the URL | Route requirement and `#[MapDateTime(format: '!Y-m')]` | Date value resolver |
| Read query parameters | `#[MapQueryParameter]` with a regular expression filter | 404 when invalid |
| Read the JSON of the save endpoint | `#[MapRequestPayload]`, Serializer, Validator | Two small DTO classes. The list of days is typed by a docblock (`@param DayInput[] $days`), as in the Symfony documentation (D-21) |
| Persistence | Doctrine ORM with attribute mapping, SQLite | Queries in repositories |
| Foreign keys in SQLite | DBAL `EnableForeignKeys` middleware | One service declaration |
| Database schema | `doctrine:schema:create` | No migrations (D-12) |
| Forms | Form component: `ClientType`, `ProfileType` | Buttons in templates. Labels and help are French texts, with `'translation_domain' => false` |
| Display and clean the SIRET | `CallbackTransformer` of the Form component, Twig filters `slice`, `split` and `join` in `Cra:Party` | No custom form type, no Twig extension |
| Form rendering | Form theme of the design system | Global theme in `twig.yaml` |
| Validation | Validator constraints on entities and DTOs: `NotBlank`, `Length`, `Email`, `Regex`, `Luhn`, `Sequentially`, `Choice`, `Date`, `Count`, `Valid` | No custom constraint |
| CSRF | Form component for forms, `isCsrfTokenValid()` for the delete form and the save endpoint | `symfony/security-csrf` only, without SecurityBundle |
| Feedback after an action | Flash messages, rendered as toasts by `ToastStack` | |
| Templates and components | Twig, anonymous Twig Components of the design system | No component class |
| Client-side behaviour | Stimulus controllers of the design system | No new JavaScript is expected |
| Navigation | Turbo Drive | No Turbo Frame, no Turbo Stream |
| Icons | UX Icons, Lucide set committed in `assets/icons/` | No call to the Iconify API |
| Assets | AssetMapper and importmap | No Node, no bundler, no CSS framework |
| Texts of the application | French texts written in templates, form types, constraints and controllers | No translation keys, no translation files (D-19) |
| Messages shipped with Symfony | Translation component, locale `fr` | Only for the French catalogs of the Validator and Form components (D-20) |
| Dates and numbers | Intl: `IntlDateFormatter`, Twig filters `format_date` and `format_number` | |
| Current date | Clock component (`ClockInterface`) | Frozen in tests. The time zone is a class constant |
| Theme and last client | Cookies | No table, no session data |
| Public holidays | `App\Calendar\FrenchHolidays` of the design system | No library |
| PDF | Browser print and `print.css` | |
| Demo data | DoctrineFixturesBundle | |
| Reset | Composer script | |
| Tests | PHPUnit, `WebTestCase`, BrowserKit, CssSelector, `ClockSensitiveTrait` | |
| Debugging | WebProfilerBundle, `dev` only | |
| Checks | `lint:twig`, `lint:yaml`, `lint:container`, `doctrine:schema:validate`, `check-styles.php` | No third-party tool (D-16) |

Not used: Live Components (D-10), Turbo Frames and Streams, SecurityBundle, Doctrine Migrations, Mailer, Messenger, Tailwind, Node.

### 7.2 Packages

| Package | Environment | Why |
|---|---|---|
| `symfony/framework-bundle`, `symfony/runtime`, `symfony/flex`, `symfony/console`, `symfony/dotenv`, `symfony/yaml` | all | Skeleton |
| `symfony/twig-bundle`, `twig/extra-bundle`, `twig/intl-extra` | all | Templates, `format_date`, `format_number` |
| `symfony/asset`, `symfony/asset-mapper` | all | Assets without a build step |
| `symfony/stimulus-bundle`, `symfony/ux-turbo`, `symfony/ux-twig-component`, `symfony/ux-icons` | all | Symfony UX bricks required by the design system |
| `symfony/form`, `symfony/validator`, `symfony/security-csrf` | all | Forms, validation, CSRF |
| `symfony/translation` | all | French version of the messages shipped with Symfony |
| `symfony/intl` | all | Locale data for dates and numbers |
| `symfony/serializer-pack` | all | `#[MapRequestPayload]`: Serializer, PropertyAccess, PropertyInfo and the two docblock parsers |
| `symfony/clock` | all | Current date |
| `doctrine/doctrine-bundle`, `doctrine/orm` | all | Persistence |
| `doctrine/doctrine-fixtures-bundle` | dev, test | Demo and test data |
| `symfony/maker-bundle` | dev | Code generation |
| `symfony/web-profiler-bundle` | dev | Debug toolbar and profiler |
| `symfony/test-pack` | test | PHPUnit, BrowserKit, CssSelector |

### 7.3 Third-party exceptions

| Dependency | Justification |
|---|---|
| `phpunit/phpunit` | Installed by `symfony/test-pack`. The test tools of Symfony are built on it and the `symfony-bp-tests` skill relies on it |
| `phpstan/phpdoc-parser`, `phpdocumentor/reflection-docblock` | Installed by `symfony/serializer-pack`. The Symfony documentation requires them to map a list of DTOs with `#[MapRequestPayload]` (D-21) |
| `@hotwired/stimulus`, `@hotwired/turbo` (JavaScript) | The engines of StimulusBundle and UX Turbo, installed by their recipes through the importmap and served locally. Symfony UX cannot work without them |
| Lucide icons, Atkinson Hyperlegible fonts | Static files delivered by the design system and committed in the repository (ISC and OFL licences) |

Packages installed only as dependencies of the packages of section 7.2 are not listed.

### 7.4 Project structure

The default structure of Symfony (`symfony-bp-creating-the-project`) and the file layout of the design system (`symfony-integration.md`).

```text
src/
├─ Calendar/        view model and public holidays (design system), DTOs of the save endpoint
├─ Controller/      CalendarController, CraController, ClientController, ProfileController,
│                   DesignSystemController (design system, dev only)
├─ DataFixtures/    AppFixtures
├─ Entity/          Profile, Client, DayEntry
├─ Form/            ClientType, ProfileType, Type/ToggleType (design system)
└─ Repository/      ProfileRepository, ClientRepository, DayEntryRepository
templates/
├─ base.html.twig, components/, form/, examples/     design system, copied as is
└─ calendar/, cra/, client/, profile/                pages
```

The files of the design system are copied with the mapping of `symfony-integration.md` and are not modified.

### 7.5 Configuration

| Value | Kind | Where |
|---|---|---|
| `DATABASE_URL` | Environment variable | `.env` |
| Locale `fr` | Framework configuration | `config/packages/translation.yaml` (design system) |
| Time zone `Europe/Paris`, month pattern, maximum length of a note | Class constants | `symfony-bp-configuration`, rule 6 |

There is no secret and no application parameter.

## 8. Non-functional requirements

### 8.1 Accessibility

- WCAG 2.2, level AA, as defined in `foundations.md`, section "Accessibility baseline".
- Every page can be used with the keyboard only. The calendar follows the grid pattern of `calendar.md`.
- Color is never the only cue. Contrast is at least 4.5:1 for text and 3:1 for interface marks, in both themes.
- One `<h1>` per page, a skip link, labels on every control, errors linked to their field.
- Reduced motion and the color scheme of the system are respected.

### 8.2 Browsers and devices

- The two latest versions of Chrome, Edge, Firefox and Safari, on desktop, and Safari on iOS.
- No support for older browsers: the design system relies on cascade layers, `:has()`, the native `<dialog>` element and import maps.
- Responsive from 390px wide. Breakpoints are those of the design system.
- Printing is checked in Chrome at least, and in Firefox and Safari when `print.css` changes.

### 8.3 Language and formats

- French interface. Texts are written in French in the files that show them, with the French typography of the design system. Identifiers, comments, commits and documentation are in English.
- Dates and numbers use the French formats, through Intl.

### 8.4 Security

- No authentication: the application must only listen on the local machine and must never be exposed on a network.
- Every request that changes data is a `POST` protected by a CSRF token.
- Output is escaped by Twig. No `raw` filter on user data.
- No secret is stored.

### 8.5 Performance

No target is set for a local demo. Two rules keep pages light: the entries of a month are loaded with one query, and bulk actions send one request.

### 8.6 Code quality

- PHP 8.4 or later, `declare(strict_types=1)`, typed properties, arguments and return values, `final` classes by default.
- Symfony coding standards, applied by hand: no third-party formatter or static analyser (D-16).
- The rules of the `symfony-bp-*` skills and the non-negotiable rules of `cra-design-system`.
- These commands must pass before each delivery:

```bash
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
php .claude/skills/cra-design-system/scripts/check-styles.php
php bin/phpunit
```

### 8.7 Tests

PHPUnit only, no browser test (D-15).

| Kind | Content |
|---|---|
| Smoke test | One functional test requests every URL of section 5.1 with a data provider and checks that it loads (`symfony-bp-tests`, rule 1) |
| Functional tests | Clients: create, edit, validation errors, delete with and without a valid token. Profile: save, SIRET input with and without spaces, display in groups, validation. Calendar: client and month resolution, 404 cases, empty state. Save endpoint: every row of the table of section 5.3. CRA: content, empty month, title, redirect when the profile is incomplete |
| Unit tests | `FrenchHolidays` (2026: Easter Monday on 6 April, Ascension on 14 May, Whit Monday on 25 May), totals and workday count of `CalendarMonth`, `DayEntry::quantity()` |

- URLs are hard-coded in functional tests (`symfony-bp-tests`, rule 2).
- The clock is frozen on 17 November 2026, so that the fixtures and the results of section 6.1 are deterministic.
- Tests use their own SQLite file. The schema is recreated and the fixtures are loaded before each test, with the Doctrine schema tool and the fixture loader.
- Checked by hand, with the checklists of the design system: keyboard use of the calendar, light and dark themes, 390px width, print preview of the CRA.

### 8.8 Documentation

`README.md` gives the prerequisites (PHP 8.4 or later with the `intl` and `pdo_sqlite` extensions, Composer, Symfony CLI) and the commands to install, reset, start and test.

## Appendix A: decision log

Decisions taken by Sylvain on 2026-09-29.

| # | Subject | Decision |
|---|---|---|
| D-01 | Single user | `Profile` entity with a single row. `Client` has no owner |
| D-02 | Client and mission | `mission` is a required field of the client. No `Mission` entity, no uniqueness constraint |
| D-03 | Day entries | `DayEntry` entity. State stored as a string, validated against `CalendarDay::STATES` |
| D-04 | Cascade on client deletion | `onDelete: 'CASCADE'` on the foreign key, with the `EnableForeignKeys` middleware |
| D-05 | Date bounds | No bound. Any valid month can be reached, future days can be marked |
| D-06 | Alsace-Moselle | Out of scope |
| D-07 | Selected client | `client` cookie kept for one year |
| D-08 | CRA with an incomplete profile | Redirect to the profile with a warning toast |
| D-09 | "Établi le" | Date of the day the page is displayed |
| D-10 | Calendar persistence | Stimulus mode of the design system, `POST /calendar/{client}/{month}`, `#[MapRequestPayload]` |
| D-11 | Project creation | Minimal skeleton, then only the packages that are used |
| D-12 | Database schema | `doctrine:schema:create`, no migrations |
| D-13 | Reset command | Composer script `composer reset` |
| D-14 | Demo data | Profile and clients of the design system gallery, dates relative to the loading date |
| D-15 | Tests | PHPUnit with `symfony/test-pack`, no browser test |
| D-16 | Quality tools | Linters provided by Symfony, Doctrine and the design system only |
| D-17 | Defaults | `Europe/Paris`, clients sorted by name, 255 characters, two latest versions of the browsers, WCAG 2.2 AA, design system gallery kept in `dev` |
| D-18 | SIRET | Luhn checksum validated, digits only in the database, shown in groups wherever it is displayed. The demo SIRET is fictitious |
| D-19 | Texts of the application | French texts written directly in the files. No translation keys, no translation files |
| D-20 | Messages shipped with Symfony | `symfony/translation` is kept, with the locale `fr`, only for the French catalogs of Symfony |
| D-21 | Docblock parsers | Accepted as third-party dependencies, installed the way the Symfony documentation recommends: `symfony/serializer-pack` |
| D-22 | Consistency between clients | No check: the user is free to mark the same date for several clients |
| D-23 | Web profiler | `symfony/web-profiler-bundle` is installed in `dev` |
| D-24 | Design system | Corrected to match these decisions: French texts, demo data, no "Aucune mission" badge |

## Appendix B: assumptions to confirm

Points that the decisions of appendix A do not cover. Each one is written in the specification as if it were accepted.

| # | Assumption | Where |
|---|---|---|
| 1 | SIRET numbers of La Poste are rejected | BR-16 |
| 2 | An unknown `client` query parameter answers 404, and a stale cookie is ignored | BR-13 |
| 3 | Status codes of the save endpoint, atomic request, last occurrence wins | Section 5.3 |
| 4 | Distribution of the demo day entries, mission "Accompagnement technique" of the third client | Section 6.1 |
| 5 | Wording of four texts that the design system did not have: the Luhn error, the missing address, the invalid e-mail of the profile, the incomplete profile | Section 3.1, BR-17 |
| 6 | A `README.md` is delivered | Section 8.8 |
| 7 | The schema and the fixtures are reloaded before each test | Section 8.7 |
