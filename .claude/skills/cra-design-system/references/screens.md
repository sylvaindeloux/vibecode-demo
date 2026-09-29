# Screens

Four pages, one shared layout. Each has a reference template in `templates/examples/` composed only of components; copy it to the real template path and wire the controller variables. The dev controller `src/Controller/DesignSystemController.php` renders them all with fake data at `/_design-system` (dev only).

| Page | Route (convention) | Reference template | Dev URL |
|---|---|---|---|
| Calendrier (home) | `app_calendar` `GET /` (`?client=&month=`) | `examples/calendar.html.twig` | `/_design-system/calendar`, `/calendar/no-client` |
| CRA imprimable | `app_cra` `GET /cra/{client}/{month}` | `examples/cra_print.html.twig` | `/_design-system/cra` |
| Clients – liste | `app_client_index` `GET /clients` | `examples/client_index.html.twig` | `/_design-system/clients`, `/clients/empty` |
| Clients – formulaire | `app_client_new` `GET|POST /clients/new`, `app_client_edit` `GET|POST /clients/{id}/edit` | `examples/client_form.html.twig` | `/_design-system/clients/form` (with validation errors) |
| Clients – suppression | `app_client_delete` `POST /clients/{id}/delete` | ConfirmDialog in `client_index` | click a trash icon |
| Profil | `app_profile` `GET|POST /profile` | `examples/profile.html.twig` | `/_design-system/profile` |

The route names above are used by `AppHeader` and the examples: keep them, or update both.

## Shared layout

`base.html.twig`: skip link, `AppHeader` (brand "EasyCRA" → calendar; nav Calendrier / Clients / Profil; ThemeSwitch), `<main class="page">`, `ToastStack`, `dialogs` block.

- Desktop: sticky top header 64px, content max 1120px centred, gutters 32px (16px under 1024px).
- Mobile: header = brand + theme switch; nav = fixed bottom tab bar (3 icons + labels, thumb reach); toasts above it.

## 1. Calendrier — home

Purpose: mark this month's days for one mission, then open the CRA.

Composition (top → bottom):

1. `PageHeader` — title "Calendrier", subtitle "Cochez vos jours travaillés, puis ouvrez votre CRA pour l’enregistrer en PDF.", actions: `ClientPicker` (only when clients exist).
2. `Calendar` with `Calendar:Toolbar` (‹ month ›, month select, Aujourd’hui, Cocher les jours ouvrés, Vider le mois), grid, selection bar, legend, keyboard help, sticky `Calendar:Summary` with primary CTA "Voir le CRA imprimable".
3. No client yet: `EmptyState` (users icon, "Ajoutez votre premier client", CTA "Ajouter un client" → `app_client_new`) instead of the calendar.

Controller: resolve client (query `client`, else the last used, else the first), month (query `month` `Y-m`, else current), load entries, build the view model with `CalendarMonthFactory`, pass `save_url` and `print_url`. The reference month shows every state: full days, a half day with note, two leave days, a worked Saturday, weekends, two holidays (1 Nov on a Sunday, 11 Nov), outside days and today.

## 2. CRA imprimable

Purpose: produce the PDF attached to the invoice.

Screen: `Cra:Toolbar` (← Retour au calendrier · "CRA - Client - Mois" · Imprimer / Enregistrer en PDF), `Cra:Help` (4 steps), `Cra:Sheet` (A4 paper preview). Print: only the sheet. `<title>` = PDF file name. Everything in **print.md**.

Controller: 404 if the client does not belong to the user; build `cra` from the profile, the client and the month's `full`/`half` entries; allow empty months (the table says so).

## 3. Clients

### 3a. Liste

1. `PageHeader` "Clients" + subtitle + primary "Ajouter un client" (`plus`).
2. `DataTable` caption "Liste des clients", columns Nom du client (primary cell, link to edit) · Contact (name + muted e-mail) · Nom de la mission (or neutral Badge "Aucune mission") · actions (visually hidden header): icon-only ghost `sm` Edit (`pencil`, link) and Delete (`trash-2`, opens the ConfirmDialog), each in a decorative Tooltip, labels "Modifier {name}" / "Supprimer {name}".
3. Mobile: rows become cards.
4. `dialogs` block: one `ConfirmDialog` `#confirm-delete-client` ("Supprimer ce client ?", subject = client name, "Ses jours saisis seront aussi supprimés. Cette action est définitive.", danger "Supprimer le client", CSRF `delete-client`).

After delete: flash `client.flash.deleted` + redirect (303) to the list.

### 3b. Liste vide

`PageHeader` without action + `EmptyState` (briefcase, "Aucun client pour l’instant", "Ajoutez un client et sa mission : ses coordonnées apparaîtront sur vos CRA.", primary "Ajouter un client").

### 3c. Formulaire (création / édition)

`page--narrow`. `PageHeader` "Nouveau client" / "Modifier {name}" + subtitle "Ces informations figurent sur le CRA remis au client." Then a `Card` containing the Symfony form:

| Field | Type | Required | Help / validation |
|---|---|---|---|
| Nom du client (`name`) | TextType | yes | NotBlank "Indiquez le nom du client." |
| Adresse (`address`) | TextareaType | no | help "Telle qu’elle doit apparaître sur le CRA." |
| Nom du contact (`contactName`) + E-mail du contact (`contactEmail`) | TextType + EmailType, side by side in `.form__row` | no | Email "Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr)." |
| Nom de la mission (`mission`) | TextType | yes | help "Par exemple : « Refonte du back-office »." NotBlank |

Actions: primary "Enregistrer" (`check`) + ghost "Annuler" (back to list). Invalid submit: HTTP 422, errors under each field (reference shows two). Success: flash `client.flash.created|updated`, redirect to the list.

### 3d. Suppression

ConfirmDialog (see 3a); never delete on GET; no "undo" promise.

## 4. Profil

`page--narrow`. `PageHeader` "Profil", subtitle "Vos informations, telles qu’elles apparaissent sur chaque CRA." `Card` with the form:

| Field | Type | Required | Notes |
|---|---|---|---|
| Nom et prénom (`name`) + Société (`company`) | TextType ×2 in `.form__row` | yes / no | |
| SIRET (`siret`) | TextType, `inputmode="numeric"`, class `input--numeric` | yes | help "14 chiffres, visibles sur votre avis de situation Insee."; validate 14 digits (spaces allowed), message `profile.siret.invalid` |
| Adresse (`address`) | TextareaType | yes | multi-line, printed as is |
| E-mail (`email`) | EmailType | no | help "Affiché sur le CRA si renseigné." |

Action: primary "Enregistrer". Success: flash `profile.flash.updated`, stay on the page.

## Screen-level rules

- One `<h1>` per page (PageHeader; on the CRA page, the sheet's month title).
- One primary button per view (the page's main action).
- Every page works without JavaScript except the calendar's in-place editing (links, forms and the print page do).
- Every page is checked in light, dark and at 390px width; the CRA page also in print preview.
