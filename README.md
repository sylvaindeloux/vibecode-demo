# EasyCRA

EasyCRA helps a French freelancer produce the monthly CRA ("compte rendu d’activité") attached to an invoice: mark the days worked for one client, open the printable CRA, save it as a PDF from the browser.

This version is a local demo: no hosting, no authentication, one implicit user. The specification is in [`docs/spec.md`](docs/spec.md), the implementation plan and its decisions in [`docs/plan.md`](docs/plan.md).

## Prerequisites

- PHP 8.4 or later, with the `intl` and `pdo_sqlite` extensions;
- [Composer](https://getcomposer.org/);
- the [Symfony CLI](https://symfony.com/download).

No Node, no bundler, no database server: assets are served by AssetMapper and the data lives in a SQLite file (`var/data_dev.db`).

## Installation and demo

```bash
composer install
composer reset      # drops var/data_dev.db, creates the schema, loads the demo data
symfony serve
```

Then open <https://127.0.0.1:8000/>: the calendar of the current month, with demo days for the two previous months and the current one. `composer reset` can be run again at any time to come back to the demo state; it deletes everything entered in the meantime. There are no migrations: after a change of the entities, run `composer reset`.

## Useful commands

| Command | What it does |
|---|---|
| `composer check` | Every check required before a commit, in order: `lint`, `test`, design-system styles, schema validation, missing translation keys |
| `composer lint` | PHP-CS-Fixer (dry run), PHPStan (level max), `lint:twig`, `lint:container`, `lint:yaml` |
| `composer fix` | Applies PHP-CS-Fixer |
| `composer test` | PHPUnit: unit tests and functional tests (`WebTestCase`) on `var/data_test.db`, with the clock frozen on 17 November 2026 |
| `composer reset` | Demo data |
| `php .claude/skills/cra-design-system/scripts/check-styles.php` | Design tokens in sync, semantic tokens only |
| `php bin/console doctrine:schema:validate` | Mapping and schema in sync |

The GitHub Actions workflow (`.github/workflows/ci.yml`) runs `composer reset` then `composer check` on every push and pull request.

## Project layout

| Path | Content |
|---|---|
| `src/Entity/`, `src/Repository/` | `Profile`, `Client`, `DayEntry` and their repositories |
| `src/Controller/` | `CalendarController` (calendar page and JSON save endpoint), `CraController`, `ClientController`, `ProfileController`, `DesignSystemController` (dev only) |
| `src/Form/` | `ClientType`, `ProfileType` (SIRET data transformer), `Type/ToggleType` (design system) |
| `src/Calendar/` | Calendar view model and French public holidays (design system), `Today`, DTOs of the save endpoint |
| `src/DataFixtures/AppFixtures.php` | Demo data, relative to the loading date |
| `templates/` | `base.html.twig`, `components/`, `form/theme.html.twig`, `examples/` (design system, copied as delivered) and the pages `calendar/`, `cra/`, `client/`, `profile/` |
| `assets/` | Stylesheets, Stimulus controllers, fonts and Lucide icons of the design system |
| `.claude/skills/cra-design-system/` | The design system: references, tokens, templates, style checker |

In the `dev` environment, `/_design-system` renders every reference screen of the design system with fake data, to check a UI change in light, dark, mobile and print preview without touching data.
