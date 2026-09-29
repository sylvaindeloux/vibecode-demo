# EasyCRA

EasyCRA helps a French freelancer produce the monthly CRA ("compte rendu d’activité") attached to an invoice: mark the days worked for one client, open the printable CRA, save it as a PDF from the browser.

This version is a local demo: no hosting, no authentication, one implicit user. The specification is in [`docs/spec.md`](docs/spec.md), the implementation plan in [`docs/plan.md`](docs/plan.md).

## Prerequisites

- PHP 8.4 or later, with the `intl` and `pdo_sqlite` extensions;
- [Composer](https://getcomposer.org/);
- the [Symfony CLI](https://symfony.com/download).

No Node, no bundler, no database server: assets are served by AssetMapper and the data lives in a SQLite file.

## Installation and demo

```bash
composer install
composer reset      # drops var/data_dev.db, creates the schema, loads the demo data
symfony serve
```

Then open <https://127.0.0.1:8000/>. `composer reset` can be run again at any time to come back to the demo state; it deletes everything entered in the meantime.

## Useful commands

| Command | What it does |
|---|---|
| `composer check` | Everything below, in order: the checks required before a commit |
| `composer lint` | PHP-CS-Fixer (dry run), PHPStan (level max), `lint:twig`, `lint:container`, `lint:yaml` |
| `composer fix` | Applies PHP-CS-Fixer |
| `composer test` | PHPUnit (unit and functional tests, on `var/data_test.db`) |
| `php .claude/skills/cra-design-system/scripts/check-styles.php` | Design tokens in sync, semantic tokens only |
| `php bin/console doctrine:schema:validate` | Mapping and schema in sync |
| `composer reset` | Demo data |

In the `dev` environment, `/_design-system` renders every reference screen of the design system with fake data.
