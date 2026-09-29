---
name: symfony-bp-configuration
description: 'Use when adding or changing a configuration value in a Symfony app: environment variables and .env files, secrets (API keys, passwords, tokens), container parameters and their per-environment overrides, or class constants for options that rarely change. Official Symfony best practices, "Configuration" section.'
---

# Symfony best practices: configuration

Rules of the "Configuration" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Use environment variables for infrastructure configuration, and `.env` files to set them per environment.**
   Why: these values change from one machine to another (development machine, production server) but do not change how the application behaves.
2. **Store sensitive configuration (API keys, passwords...) with Symfony's secrets management.**
   Why: sensitive values need secure storage; the secrets vault keeps them encrypted.
3. **Define the options that change the application behaviour as parameters in `config/services.yaml`, not as environment variables; override them per environment in `config/services_<env>.yaml`.**
   Why: their value does not change per machine (e.g. the sender of notification emails, the enabled feature toggles).
4. **Do not use the Config component to define your own options, unless the configuration is reused many times and needs rigid validation.**
   Why: parameters are enough for application configuration; the Config component only pays off for reused, strictly validated configuration.
5. **Prefix your parameters with `app.` and name them with one or two meaningful words, always in the same format.**
   Why: the prefix avoids collisions with Symfony and third-party parameters, and short names stay readable.
6. **Define options that rarely change (e.g. the number of items per page) as class constants, not as parameters.**
   Why: constants can be used everywhere, including Twig templates and Doctrine entities, while parameters need the service container; their only drawback is that they are hard to redefine in tests.

## Examples

### 1. Environment variables

Do:

```bash
# .env: committed, default values for local development only
DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

# .env.local (not committed) overrides values on one machine;
# .env.test (committed) overrides values for the test environment.
```

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
```

Avoid:

```yaml
# a machine-specific value hard-coded in versioned configuration
doctrine:
    dbal:
        url: 'sqlite:////var/www/shop/var/app.db'
```

### 2. Secrets

Do:

```bash
php bin/console secrets:set MAILER_API_KEY                        # dev vault
APP_RUNTIME_ENV=prod php bin/console secrets:set MAILER_API_KEY   # prod vault
# never commit config/secrets/prod/prod.decrypt.private.php
```

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// a secret is read like an environment variable
public function __construct(
    #[Autowire(env: 'MAILER_API_KEY')]
    private readonly string $mailerApiKey,
) {
}
```

Avoid:

```bash
# .env is committed: never put a real secret in it
MAILER_API_KEY=the-real-production-key
```

### 3. Parameters for application behaviour

Do:

```yaml
# config/services.yaml
parameters:
    app.notifications_sender: 'notifications@example.com'
```

```yaml
# config/services_dev.yaml: override for the dev environment only
parameters:
    app.notifications_sender: 'dev-notifications@example.com'
```

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

public function __construct(
    #[Autowire(param: 'app.notifications_sender')]
    private readonly string $sender,
) {
}
```

Avoid:

```bash
# .env: the value is the same on every machine, so it is not an env var
APP_NOTIFICATIONS_SENDER=notifications@example.com
```

### 5. Parameter names

```yaml
parameters:
    app.export_dir: '%kernel.project_dir%/var/exports'   # prefixed, short, meaningful
    app.admin_email: 'admin@example.com'                 # same format everywhere
    # Avoid:
    # app.dir: '...'           too generic
    # export_dir: '...'        no "app." prefix
    # app.admin-email: '...'   mixes "-" with "_"
```

### 6. Constants for options that rarely change

Do:

```php
namespace App\Entity;

class Product
{
    public const int ITEMS_PER_PAGE = 20;

    // ...
}
```

```twig
{{ constant('App\\Entity\\Product::ITEMS_PER_PAGE') }}
```

Avoid:

```yaml
parameters:
    app.items_per_page: 20   # rarely changes: use a class constant
```

## Source

- <https://symfony.com/doc/current/best_practices.html#configuration>, section "Configuration" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/configuration.html>, <https://symfony.com/doc/current/configuration/secrets.html>, <https://symfony.com/doc/current/service_container/autowiring.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
