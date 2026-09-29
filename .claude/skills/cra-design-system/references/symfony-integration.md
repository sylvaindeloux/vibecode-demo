# Symfony integration

Target: Symfony 8.x, Symfony UX 3.x (TwigComponent, StimulusBundle, Turbo, UX Icons), AssetMapper, PHP 8.4. Verified with Symfony 8.1 / UX 3.5.

## Packages

```bash
composer require symfony/twig-bundle symfony/asset symfony/asset-mapper \
    symfony/stimulus-bundle symfony/ux-turbo symfony/ux-twig-component symfony/ux-icons \
    symfony/form symfony/validator symfony/security-csrf symfony/translation symfony/intl \
    twig/extra-bundle twig/intl-extra
```

`twig/intl-extra` provides `format_date` / `format_number`; `symfony/asset` provides `asset()` (font preload); `symfony/intl` provides locale data; `symfony/translation` only provides the French version of the messages shipped with Symfony (validation, form errors): the app has no translation file (see "UI texts").

## Installing the design system

Copy `templates/` of the skill into the project root, respecting paths (the skill folder mirrors the project):

| Skill path | Project path |
|---|---|
| `templates/assets/styles/**` | `assets/styles/**` (`app.css` entry, `tokens.css`, `base.css`, `utilities.css`, `print.css`, `components/*.css`) |
| `templates/assets/controllers/*_controller.js` | `assets/controllers/` |
| `templates/assets/fonts/*` | `assets/fonts/` |
| `templates/assets/icons/lucide/*.svg` | `assets/icons/lucide/` |
| `templates/assets/app.js` | `assets/app.js` |
| `templates/templates/**` | `templates/**` (`base.html.twig`, `components/`, `form/theme.html.twig`, `examples/`) |
| `templates/config/packages/*.yaml` | `config/packages/` (merge with existing files: `twig.yaml` form theme, `ux_icons.yaml`, `translation.yaml`) |
| `templates/src/**` | `src/**` (`Calendar/*`, `Form/Type/ToggleType.php`, `Controller/DesignSystemController.php` dev only) |

Then: `php bin/console cache:clear`, open `/_design-system` in dev, and run `php <skill>/scripts/check-styles.php`.

## Twig Components (anonymous)

- `config/packages/twig_component.yaml`: `anonymous_template_directory: 'components/'` (recipe default).
- A file `templates/components/Button.html.twig` is `<twig:Button>`; `templates/components/Calendar/Day.html.twig` is `<twig:Calendar:Day>`. A component and a folder with the same name can coexist (`Calendar.html.twig` + `Calendar/`).
- Template skeleton:

```twig
{#
 # ComponentName - one-line purpose. Spec: references/components.md#componentname
 #}
{% props variant = 'default', title, icon = null %}
<div {{ attributes.defaults({class: 'component component--' ~ variant, 'data-controller': 'component'}) }}>
    {% if icon %}<twig:ux:icon :name="icon" class="icon" />{% endif %}
    {% block content %}{% endblock %}
    {% if block('footer') is defined %}<footer class="component__footer">{{ block('footer') }}</footer>{% endif %}
</div>
```

- Rules:
  - Props without default are required; give every optional prop a default.
  - Root element gets `{{ attributes.defaults({...}) }}` (merges `class`, `data-controller`, `data-action`). To forward attributes to a nested component: `<twig:Button {{ ...attributes }}>`.
  - Dynamic prop values use `:prop="expression"`; static strings `prop="text"`; boolean `iconOnly` alone = true.
  - Content = `{% block content %}` (what is between the tags); named slots = `<twig:block name="footer">…</twig:block>`. **A `<twig:block>` cannot be inside `{% if %}`**: branch around the component instead. Optional slots are tested with `block('x') is defined`.
  - User-facing text is written in French, directly in the template (no `|trans`); a text that depends on the caller is a prop. Component-internal ids are derived from a prop `id`.
  - One component = one CSS file named after its block (`Button` → `button.css`, class `.button`), imported in `assets/styles/app.css`.

## Stimulus

- Controllers live in `assets/controllers/<name>_controller.js` and are auto-registered by StimulusBundle (`assets/stimulus_bootstrap.js` from the recipe — older recipes call it `bootstrap.js`; keep the project's name in `app.js`). File `modal_trigger_controller.js` → identifier `modal-trigger`.
- Heavy or page-specific controllers start with `/* stimulusFetch: 'lazy' */` (calendar).
- **No inline JavaScript**: no `<script>` in templates, no `onclick=`. Behaviour = `data-controller`, `data-action`, `data-<ctrl>-target`, `data-<ctrl>-<name>-value`, `data-<ctrl>-<name>-param`.
- Texts needed by JS are written in the Twig template and passed in a value, e.g. `data-calendar-labels-value="{{ labels|json_encode }}"`. No French text in a controller.
- Controllers of the design system:

| Identifier | Element | Role |
|---|---|---|
| `theme` | ThemeSwitch fieldset | `<html data-theme>` + `theme` cookie |
| `modal` | `<dialog>` | open/close, fill slots from `modal:open` detail, backdrop click, Turbo cache |
| `modal-trigger` | any button | opens a dialog by id, forwards params |
| `toast-stack` | ToastStack | `toast:show` window event → new toast from `<template>` |
| `toast` | Toast | auto-dismiss, pause on hover/focus, close |
| `tooltip` | Tooltip | Escape to dismiss |
| `print` | PrintButton | `window.print()` |
| `autosubmit` | GET form | submit on change, hides `.autosubmit-fallback` |
| `calendar` | Calendar | grid keyboard, states, dialog, quick actions, summary, persistence |

- Controllers only toggle **attributes** (`data-state`, `aria-*`, `hidden`) and text; visuals stay in CSS. Never set `style=` from JS.
- Cross-controller communication: DOM events (`modal:open`, `toast:show`), Stimulus outlets if needed. No globals.

## Turbo

- Turbo Drive is on (ux-turbo): links and forms are fetch-based page visits. Consequences:
  - invalid form submissions must answer **422** (`$this->render(..., new Response(status: 422))` or `render()` with a submitted invalid form in Symfony ≥ 6.2 via `$this->render('…', ['form' => $form])` which sets 422 automatically);
  - successful POST → redirect **303** (`$this->redirectToRoute(..., status: 303)`);
  - elements that must not be cached (toasts) carry `data-turbo-temporary`; open dialogs are closed on `turbo:before-cache`;
  - Turbo sets `aria-busy="true"` on a submitting form → submit buttons show their spinner (CSS only).
- Month navigation and client switching are plain GET visits: no frames needed. Use a Turbo Frame only if a part of a page must reload alone (e.g. a future side panel).
- The CRA page is a normal visit; printing needs nothing from Turbo.

## Forms

- Global theme in `config/packages/twig.yaml`:

```yaml
twig:
    form_themes: ['form/theme.html.twig']
```

- Templates render rows one by one (`form_row(form.name)`), in the order of the design, with layout helpers (`.form__row` for side-by-side fields) and buttons as components:

```twig
{{ form_start(form) }}            {# adds class "form" and novalidate #}
    {{ form_errors(form) }}       {# form-level errors: alert #}
    {{ form_row(form.name) }}
    <div class="form__row">{{ form_row(form.contactName) }}{{ form_row(form.contactEmail) }}</div>
    <div class="form-actions">
        <twig:Button type="submit" variant="primary" icon="lucide:check">Enregistrer</twig:Button>
        <twig:Button variant="ghost" :href="path('app_client_index')">Annuler</twig:Button>
    </div>
{{ form_end(form) }}
```

- FormType options: `label` and `help` are **French texts** (`'label' => 'Nom du client'`) and the form type sets `'translation_domain' => false`; `required` drives "(facultatif)"; constraint messages are French texts too.
- Switch-like booleans: `App\Form\Type\ToggleType` (renders `toggle_row`). Expanded single choices render as a SegmentedControl in a fieldset.
- Dates: `DateType` with `'widget' => 'single_text'` (native date input).
- Example `ClientType`:

```php
final class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du client'])
            ->add('address', TextareaType::class, ['label' => 'Adresse', 'required' => false, 'help' => 'Telle qu’elle doit apparaître sur le CRA.'])
            ->add('contactName', TextType::class, ['label' => 'Nom du contact', 'required' => false])
            ->add('contactEmail', EmailType::class, ['label' => 'E-mail du contact', 'required' => false])
            ->add('mission', TextType::class, ['label' => 'Nom de la mission', 'help' => 'Par exemple : « Refonte du back-office ».']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Client::class, 'translation_domain' => false]);
    }
}
```

(constraints on the entity: `#[Assert\NotBlank(message: 'Indiquez le nom du client.')]`, `#[Assert\Email(message: 'Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).')]`.)

## UI texts

The app has one language, French, and is not meant to be translated.

- **No translation key, no `|trans`, no translation file.** Write each text in French in the file that shows it: templates and components, form types (`label`, `help`), constraints (`message`), controllers (flash messages), `FrenchHolidays` (holiday names). The texts of every screen are listed in `screens.md`.
- A text used at several places of one component tree is written once and passed down as a prop (the names of the day states: `labels.states` in `Calendar.html.twig`, prop `stateLabel` of `Calendar:Day`).
- Texts with a variable part use Twig output or interpolation: `label="Modifier {{ client.name }}"`.
- Plurals: `{{ total < 2 ? 'jour travaillé' : 'jours travaillés' }}` (in French, 0 and 1,5 are singular). In JavaScript: `Intl.PluralRules`, with the two forms passed by Twig.
- Typography: `’` (typographic apostrophe), `« … »`, a space before `:`.
- Flash messages: `$this->addFlash('success', 'Client ajouté.')`.
- Locale `fr` (`config/packages/translation.yaml`): it sets `<html lang>`, the Intl formats of dates and numbers, and the language of the messages shipped with Symfony. A constraint without a custom `message` shows Symfony's French message.

## UX Icons

- `<twig:ux:icon name="lucide:<name>" class="icon" />` in templates; `ux_icon('lucide:<name>', {class: 'icon'})` where the HTML syntax is unavailable.
- `config/packages/ux_icons.yaml` of the skill: default attributes `aria-hidden="true"` and `focusable="false"`, **no `fill`/size defaults** (Lucide is stroked; size via CSS).
- Icons are committed in `assets/icons/lucide/`. New icon: use it, then `php bin/console ux:icons:lock` (downloads to `assets/icons/`), and commit.

## CSS with AssetMapper (no build step)

- `assets/app.js` imports `./styles/app.css`; `{{ importmap('app') }}` in `base.html.twig` emits the `<link rel="stylesheet">`.
- `app.css` declares the cascade layers and imports every file (AssetMapper rewrites `@import url()` to versioned URLs):

```css
@layer tokens, base, components, utilities, print;
@import url("./tokens.css") layer(tokens);
@import url("./base.css") layer(base);
@import url("./components/button.css") layer(components);
/* … one line per component … */
@import url("./utilities.css") layer(utilities);
@import url("./print.css") layer(print);
```

- Order = priority: a later layer wins regardless of specificity, so no `!important` is needed (print overrides components; utilities override components).
- New component stylesheet → add one `@import` line in the `components` layer.
- Native CSS only: custom properties, nesting is allowed but keep BEM flat, `:has()`, logical properties (`inline`/`block`), `@media` with literal breakpoints. No preprocessor, no framework, no CSS-in-JS.
- Fonts: `assets/fonts/*.woff2` referenced from base.css (`url("../fonts/…")`, rewritten by AssetMapper), preloaded in base.html.twig.
- Production: `php bin/console asset-map:compile`.

## File organisation (project)

```
assets/
  app.js                         # imports stimulus_bootstrap.js + styles/app.css
  controllers/*_controller.js    # one Stimulus controller per behaviour
  fonts/                         # Atkinson Hyperlegible Next + Mono (woff2) + OFL.txt
  icons/lucide/*.svg             # locked Lucide icons
  styles/
    app.css                      # layers + imports (entry)
    tokens.css                   # design tokens (mirror of tokens.json)
    base.css                     # fonts, reset, typography, focus, skip link
    utilities.css                # tiny utility set
    print.css                    # @page + @media print
    components/<component>.css   # one file per component
templates/
  base.html.twig
  components/<Name>.html.twig    # anonymous Twig Components (+ <Name>/<Sub>.html.twig)
  form/theme.html.twig           # Symfony form theme
  examples/*.html.twig           # reference screens (design system, keep up to date)
  <feature>/*.html.twig          # real pages (calendar/, client/, profile/, cra/)
src/Calendar/                    # calendar view model + French holidays
src/Form/Type/ToggleType.php
src/Controller/DesignSystemController.php   # dev-only screen gallery
```

## Dev screen gallery

`DesignSystemController` (`#[When(env: 'dev')]`) renders every reference screen with fake data at `/_design-system/*`. Use it to check any UI change in light, dark (theme switch), mobile (390px) and print preview without touching data. It needs the app routes listed in screens.md to exist.
