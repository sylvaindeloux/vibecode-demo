---
name: symfony-bp-creating-the-project
description: 'Use when creating a new Symfony application, or when deciding where a new directory or kind of file belongs in the project tree (a new top-level folder, or a new src/ subfolder such as src/Command/ or src/EventSubscriber/). Official Symfony best practices, "Creating the Project" section.'
---

# Symfony best practices: creating the project

Rules of the "Creating the Project" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Create new Symfony applications with the Symfony binary (`symfony new`).**
   Why: it is the simplest way, and it runs the right Composer command to create an application on the current stable version.
2. **Follow the default Symfony directory structure, unless your development practice imposes another one.**
   Why: the default structure is flat, self-explanatory and not coupled to Symfony.
   Doc note: the tree on the official page still lists `public/build/` (Webpack Encore output); with AssetMapper, compiled assets go to `public/assets/`.

## Examples

### 1. Create the application

```bash
# traditional web application (--webapp installs the extra packages a web app needs)
symfony new my_project_directory --version="8.1.*" --webapp

# microservice, console application or API
symfony new my_project_directory --version="8.1.*"
```

### 2. Default directory structure

```text
your_project/
├─ assets/
├─ bin/
│  └─ console
├─ config/
│  ├─ packages/
│  ├─ routes/
│  └─ services.yaml
├─ migrations/
├─ public/
│  ├─ assets/        # written by asset-map:compile (see Doc note)
│  └─ index.php
├─ src/
│  ├─ Kernel.php
│  ├─ Command/
│  ├─ Controller/
│  ├─ DataFixtures/
│  ├─ Entity/
│  ├─ EventSubscriber/
│  ├─ Form/
│  ├─ Repository/
│  ├─ Security/
│  └─ Twig/
├─ templates/
├─ tests/
├─ translations/
├─ var/
│  ├─ cache/
│  └─ log/
└─ vendor/
```

## Source

- <https://symfony.com/doc/current/best_practices.html#creating-the-project>, section "Creating the Project" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/setup.html>, <https://symfony.com/doc/current/frontend/asset_mapper.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
