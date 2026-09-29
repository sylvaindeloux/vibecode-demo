---
name: symfony-bp-web-assets
description: 'Use when adding or managing CSS, JavaScript, images or fonts in a Symfony app, installing a frontend package, or when a bundler (Webpack, Webpack Encore) or a Node build step is being considered. Official Symfony best practices, "Web Assets" section.'
---

# Symfony best practices: web assets

Rule of the "Web Assets" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Manage web assets (CSS, JavaScript, images) with AssetMapper, not with a bundler such as Webpack or Webpack Encore.**
   Why: AssetMapper lets you write modern JavaScript and CSS without the complexity of a bundler.

## Examples

### 1. AssetMapper

Do:

```twig
{# templates/base.html.twig: loads assets/app.js and the CSS it imports #}
{% block importmap %}{{ importmap('app') }}{% endblock %}

{# any file of assets/, e.g. assets/images/logo.svg #}
<img src="{{ asset('images/logo.svg') }}" alt="Company logo">
```

```bash
php bin/console importmap:require some-package   # add a third-party JavaScript package
php bin/console asset-map:compile                # at deploy time: writes the files to public/assets/
```

Avoid:

```bash
composer require symfony/webpack-encore-bundle   # bundler-based pipeline
npx webpack --mode production
```

## Source

- <https://symfony.com/doc/current/best_practices.html#web-assets>, section "Web Assets" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/frontend/asset_mapper.html>, <https://symfony.com/doc/current/frontend.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
