# EasyCRA: implementation plan

Ordered list of features, cut from `docs/spec.md`. Each feature is delivered on the branch with its tests, all checks green, and ticked here (`[x]`) once pushed to the branch.

Checks run before every commit (`composer check`):

- `composer lint`: PHP-CS-Fixer, PHPStan (level max), `lint:twig`, `lint:container`, `lint:yaml`;
- `composer test`: PHPUnit;
- `php .claude/skills/cra-design-system/scripts/check-styles.php`;
- `php bin/console doctrine:schema:validate`;
- `php bin/console debug:translation fr --only-missing`: no missing key.

## Features

### [x] F1. Plan

- Goal: agree on the order of work before writing code.
- Files: `docs/plan.md`.
- Done when: this file is committed.

### [x] F2. Technical foundation

- Goal: a running Symfony 8.1 project with the design system installed and the quality tools wired.
- Files: `composer.json`, `config/`, `bin/console`, `public/index.php`, `src/Kernel.php`, `assets/` (design system copy), `templates/` (design system copy), `src/Calendar/`, `src/Form/Type/ToggleType.php`, `src/Controller/DesignSystemController.php`, `importmap.php`, `.env`, `.env.test`, `phpstan.dist.neon`, `.php-cs-fixer.dist.php`, `phpunit.dist.xml`, `.github/workflows/ci.yml`, `.gitignore`, `README.md`.
- Content:
  - packages of spec section 7.2 (framework, Twig, AssetMapper, Symfony UX, Form, Validator, CSRF, Translation, Intl, Serializer pack, Clock, Doctrine, fixtures, Maker, profiler, test pack), SQLite `DATABASE_URL`;
  - design system files copied as is (mapping of `symfony-integration.md`), config merged (`twig.yaml`, `ux_icons.yaml`, `translation.yaml`);
  - dev tools: PHPStan level max, PHP-CS-Fixer (Symfony rule set), PHPUnit;
  - Composer scripts `lint`, `test`, `check`, `reset`;
  - GitHub Actions workflow running `composer check`.
- Done when: `composer check` is green; unit tests of the design-system view model pass (`FrenchHolidays` 2026, `CalendarMonth` totals and workday count); `/_design-system` renders in `dev` (checked with the test client in `dev` environment is out of scope: it is checked by hand in the report).

### [x] F3. Data model, demo data and reset

- Goal: the three entities, their repositories, the demo fixtures and `composer reset`.
- Files: `src/Entity/{Profile,Client,DayEntry}.php`, `src/Repository/{Profile,Client,DayEntry}Repository.php`, `src/DataFixtures/AppFixtures.php`, `config/services.yaml` (`EnableForeignKeys` middleware), `config/packages/doctrine.yaml`, `tests/DatabaseTestCase.php` (schema + fixtures before each test), `tests/Entity/DayEntryTest.php`, `tests/DataFixtures/AppFixturesTest.php`.
- Done when: `doctrine:schema:validate` passes; `composer reset` works on a fresh clone; `DayEntry::quantity()` unit test passes; the fixture counts of spec section 6.1 (today frozen on 2026-11-17) are asserted by a test.

### [x] F4. Routes of the four sections

- Goal: register every route of spec section 5.1 so that the shared layout (header links) and the dev gallery can render; each following feature fills its controller.
- Files: `src/Controller/{Calendar,Cra,Client,Profile}Controller.php` generated with `make:controller`, then trimmed to the route attributes and a minimal render.
- Done when: `debug:router` lists the routes; the layout renders; the smoke test (`tests/ApplicationAvailabilityTest.php`, moved here from F10) requests every page.

### [x] F5. Profile (US-01)

- Goal: record the freelancer's identity, with the SIRET rules of BR-16.
- Files: `src/Controller/ProfileController.php`, `src/Form/ProfileType.php`, `templates/profile/edit.html.twig`, `tests/Controller/ProfileControllerTest.php`.
- Done when: every acceptance criterion of US-01 has a passing functional test (save, 422 with errors, SIRET with and without spaces, grouped display, 14 digits, Luhn, single row).

### [ ] F6. Clients (US-02, US-03, US-04)

- Goal: list, create, edit and delete clients.
- Files: `src/Controller/ClientController.php`, `src/Form/ClientType.php`, `templates/client/{index,form}.html.twig`, `tests/Controller/ClientControllerTest.php`.
- Done when: functional tests cover the list (sorted, empty state), create, edit, validation errors (422), 404 on unknown id, delete with a valid token (303, cascade on day entries), without token (403), GET on the delete URL (405).

### [ ] F7. Calendar page (US-05)

- Goal: show the monthly calendar of one client, with client and month resolution (BR-12, BR-13).
- Files: `src/Controller/CalendarController.php` (`index`), `src/Repository/DayEntryRepository.php` (entries of a month), `templates/calendar/index.html.twig`, `tests/Controller/CalendarControllerTest.php`.
- Done when: functional tests cover the current month by default, `client` and `month` query parameters, the cookie (write and read, stale cookie ignored), 404 on unknown client or invalid month, the empty state without any client, holidays and today in the grid.

### [ ] F8. Saving days (US-06, US-07, US-08, US-09)

- Goal: the JSON endpoint used by the design-system Stimulus controller (spec section 5.3).
- Files: `src/Controller/CalendarController.php` (`save`), `src/Calendar/{DaysInput,DayInput}.php` (DTOs), `src/Repository/DayEntryRepository.php` (upsert and delete, BR-05), `tests/Controller/CalendarSaveTest.php`.
- Done when: every row of the table of section 5.3 has a test (204, 403, 404, 400, 422 cases), the request is atomic, the last occurrence of a date wins, an `empty` state without note deletes the row.

### [ ] F9. Printable CRA (US-10)

- Goal: the printable sheet for one client and one month.
- Files: `src/Controller/CraController.php`, `templates/cra/show.html.twig`, `tests/Controller/CraControllerTest.php`.
- Done when: functional tests cover the content (header, parties, days in order, total), the empty month, the `<title>` (BR-20), the redirect with a warning toast when the profile is incomplete, the back link. Print preview is checked by hand (reported, not automated).

### [ ] F10. Smoke test, layout and documentation (US-11, US-12)

- Goal: the whole application is reachable, documented and verifiable.
- Files: `tests/ApplicationAvailabilityTest.php`, `README.md`, `docs/plan.md`.
- Done when: the smoke test requests every URL of section 5.1; `composer install && composer reset` then `symfony serve` gives a usable app; README lists prerequisites, installation, demo, useful commands; CI is green.

## Decisions & issues

| # | Subject | Decision |
|---|---|---|
| P-01 | Quality tools | Spec D-16 excludes third-party linters, but the request asks for PHPStan (level max) and PHP-CS-Fixer with `composer lint`. The request wins: both are `require-dev` only and never run at runtime. Listed in the PR "Decisions" table. |
| P-02 | Route coupling | The shared header links the three sections to each other, so no page can render before every route of section 5.1 exists. F4 registers the routes first (controllers generated with MakerBundle, trimmed), each later feature replaces its placeholder with the real action. |
| P-03 | Project creation | No Symfony CLI in the cloud environment: the skeleton is created with `composer create-project symfony/skeleton:"8.1.*"`, which is what `symfony new` runs. Locally, `symfony serve` is used to run the app. |
| P-04 | Composer downloads | GitHub dist downloads are refused by the cloud proxy; Composer falls back to source clones. No impact on the project files. |
| P-05 | Test database | Tests use `var/data_test.db`, the schema is created and the fixtures are loaded before each test by `tests/DatabaseTestCase.php` (spec 8.7, assumption 7). |
| P-06 | Browser checks | Keyboard use, themes, 390px width and the print preview cannot be checked in the cloud environment; they are listed as "to check by hand" in the final report. |
| P-07 | Translation check | `debug:translation fr --only-missing` always lists the French constraint messages as missing: the Validator extractor reads them from the entities, and D-19 forbids translation files. `composer check` therefore restricts the command to the `messages` domain (a `\|trans` left in a template would still be caught) and accepts exit code 64 ("no message extracted"), which is what "no missing key" means for an application without translation keys. |
| P-08 | PHP-CS-Fixer and design-system PHP files | `@Symfony` only (no risky set): the risky set would rewrite `sprintf` calls in the design-system controller. `declare_strict_types` is kept. Two design-system classes are excluded from the fixer (docblock alignment only), and PHPStan ignores the missing generics of two design-system files, so that the copies stay identical to the skill. |
| P-09 | Reset script | `doctrine:database:drop --if-exists` is not supported by the SQLite platform (DBAL 4 lists databases). `--force` alone drops the file and succeeds when it is missing, so the script of spec 6.2 is written without `--if-exists`. |
| P-10 | Schema validation | `doctrine:schema:validate` compares the mapping with the dev database, so `composer check` needs a database created from the current mapping: run `composer reset` after any change of the model (D-12). The CI workflow runs it before `composer check`. |
| P-11 | "Today" | The Europe/Paris date of the day is needed by the calendar, the CRA and the fixtures: one small `App\Calendar\Today` service wraps the Symfony clock, with the time zone as a class constant (spec 7.5). |
