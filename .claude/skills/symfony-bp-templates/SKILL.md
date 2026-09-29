---
name: symfony-bp-templates
description: 'Use when creating, naming or renaming a Twig template, a template directory or a template fragment (partial), or when naming the variables passed to or set in a template. Official Symfony best practices, "Templates" section.'
---

# Symfony best practices: templates

Rules of the "Templates" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Use lowercase snake_case for template names, template directories and template variables.**
   Why: it is the convention recommended by Symfony for template files and by Twig for variable names.
2. **Prefix the names of template fragments (partial templates) with an underscore.**
   Why: fragments are then told apart from complete templates at a glance.

## Examples

### 1. snake_case

Do:

```text
templates/product/edit_form.html.twig
templates/user_profile/show.html.twig
```

```php
return $this->render('product/edit_form.html.twig', [
    'related_products' => $relatedProducts,
]);
```

```twig
{% set total_price = order.total %}
```

Avoid:

```text
templates/Product/EditForm.html.twig
```

```twig
{% set totalPrice = order.total %}
```

### 2. Fragments

Do:

```twig
{# templates/product/show.html.twig #}
{{ include('product/_price_tag.html.twig', {price: product.price}) }}
```

Avoid:

```text
templates/product/price_tag.html.twig   # fragment without the "_" prefix
```

## Project notes (EasyCRA)

- Twig Components (`templates/components/`) follow `cra-design-system`, not rule 1: the file name is the component name in PascalCase (`components/Calendar/Day.html.twig` is `<twig:Calendar:Day>`), and props and local variables are camelCase (`saveUrl`, `hideLabel`). Rule 1 applies to page templates, fragments and the variables that controllers pass to them.
- Reusable UI goes into a Twig Component (`cra-design-system`, "Components first"). Use an `_` fragment only for a piece specific to a page or a feature, e.g. a form shared by the new and edit pages, or the content of a Turbo Frame.

## Source

- <https://symfony.com/doc/current/best_practices.html#templates>, section "Templates" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked page checked for current syntax: <https://symfony.com/doc/current/templates.html> ("Template Naming", "Including Templates").
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
