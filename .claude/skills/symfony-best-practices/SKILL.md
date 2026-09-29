---
name: symfony-best-practices
description: 'Entry point to the official Symfony best practices, split into symfony-bp-* skills. Use when starting a Symfony feature that spans several areas (controller, form, template, tests...), when reviewing Symfony code against the official best practices, or when unsure which symfony-bp-* skill applies.'
---

# Symfony best practices (index)

The official Symfony best practices (Symfony 8.1), one skill per section. Read the skill that matches the task; several often apply together (e.g. a form page: controllers, forms, templates, tests).

| Skill | In one line | Read it when |
|---|---|---|
| `symfony-bp-creating-the-project` | Create apps with `symfony new` and keep the default directory structure. | Bootstrapping the app, or deciding where a new directory or kind of file goes. |
| `symfony-bp-configuration` | Env vars for infrastructure, secrets for sensitive values, `app.` parameters for behaviour, constants for options that rarely change. | Adding or changing a configuration value. |
| `symfony-bp-business-logic` | Namespaces instead of bundles, autowiring with autoconfiguration, private services, minimal service config, Doctrine mapping with attributes. | Creating or wiring a service, or mapping an entity. |
| `symfony-bp-controllers` | Thin controllers extending `AbstractController`, configured with attributes, with injected services and entities resolved when convenient. | Writing a controller, route or action. |
| `symfony-bp-templates` | snake_case template names, directories and variables; `_` prefix for fragments. | Creating or naming a template, fragment or template variable. |
| `symfony-bp-forms` | Form type classes, buttons in templates, constraints on the object, one action renders and processes the form. | Writing a form or the action that handles it. |
| `symfony-bp-security` | A single firewall, the `auto` password hasher, voters for complex authorization rules. | Configuring authentication or authorization. |
| `symfony-bp-web-assets` | AssetMapper, no bundler. | Adding CSS, JavaScript, images, fonts or a frontend package. |
| `symfony-bp-tests` | Smoke test every URL; hard-code URLs in functional tests. | Writing functional tests. |

The Symfony Demo application follows all these practices: <https://github.com/symfony/demo>.

## Project notes (EasyCRA)

- Precedence: `CLAUDE.md` and `cra-design-system` win over these skills (rule in `CLAUDE.md`); these skills win over the examples of the vendored skills (`turbo`, `twig-component`, `live-component`...), which are illustrative. Known conflicts are listed in `.claude/skills/SOURCES.md`.
- The "Internationalization" section of the official page is intentionally not covered: the project is not internationalized.

## Source

- <https://symfony.com/doc/current/best_practices.html> (Symfony 8.1 documentation), read on 2026-09-29.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
