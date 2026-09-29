---
name: symfony-bp-business-logic
description: 'Use when creating or wiring a service class in a Symfony app (autowiring, autoconfiguration, #[Autowire], service configuration files), when considering a bundle to organize application code, or when defining the mapping of a Doctrine entity. Official Symfony best practices, "Business Logic" section.'
---

# Symfony best practices: business logic

Rules of the "Business Logic" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Do not create bundles to organize your application logic; structure the code with PHP namespaces under `App\`.**
   Why: a bundle is meant to be reusable as stand-alone software; create one only to share a feature across several projects (a private repository is fine).
2. **Use autowiring together with autoconfiguration for your services.**
   Why: type-hints are enough to inject dependencies and the needed tags (Twig extensions, event subscribers...) are added automatically, so services need no explicit configuration.
3. **Keep services private and get them through dependency injection, never with `$container->get()`.**
   Why: private services cannot be fetched from the container, which forces proper dependency injection.
4. **Keep service configuration minimal: rely on the default `config/services.yaml`, and put the configuration that belongs next to the code in attributes such as `#[Autowire]`.**
   Why: autowiring and autoconfiguration already configure most services, so manual configuration is rarely needed.
5. **For the remaining manual service configuration, pick one format (YAML or PHP) and use it consistently across the project.**
   Why: both work well (YAML is concise and friendly to newcomers, PHP adds type-safety and IDE autocompletion); what matters is consistency.
6. **Define the Doctrine entity mapping with PHP attributes.**
   Why: attributes are the most convenient and agile way to set up and look up mapping information.

## Examples

### 1. Namespaces, not bundles

Do:

```text
src/
├─ Controller/
├─ Entity/
└─ Invoice/                        # namespace App\Invoice
   └─ InvoiceNumberGenerator.php
```

Avoid:

```text
src/InvoiceBundle/InvoiceBundle.php   # a bundle used only by this application
```

### 2. Autowiring and autoconfiguration

Do (no service configuration needed: the logger is injected, the subscriber is tagged):

```php
namespace App\Invoice;

use Psr\Log\LoggerInterface;

final class InvoiceNumberGenerator
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }
}
```

```php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestLogSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // ...
    }
}
```

Avoid:

```yaml
# config/services.yaml: definitions that autowiring and autoconfiguration make useless
services:
    App\Invoice\InvoiceNumberGenerator:
        arguments: ['@logger']
    App\EventSubscriber\RequestLogSubscriber:
        tags: ['kernel.event_subscriber']
```

### 3. Private services

Do: inject the service (constructor or controller action argument), as in example 2.

Avoid:

```php
$generator = $container->get(InvoiceNumberGenerator::class);
```

```yaml
services:
    App\Invoice\InvoiceNumberGenerator:
        public: true   # only needed to allow $container->get()
```

### 4. Minimal configuration, next to the code

```yaml
# config/services.yaml: the default configuration is enough for most services
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
```

```php
namespace App\Report;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ReportExporter
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/exports')]
        private readonly string $exportDir,
    ) {
    }
}
```

### 6. Doctrine mapping with attributes

Do:

```php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;
}
```

Avoid:

```text
config/doctrine/Product.orm.xml   # mapping kept away from the class
```

## Project notes (EasyCRA)

- Service configuration format (rule 5): YAML, the format of the Flex recipes and of the `config/packages/*.yaml` files of `cra-design-system`.

## Source

- <https://symfony.com/doc/current/best_practices.html#business-logic>, section "Business Logic" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/bundles.html>, <https://symfony.com/doc/current/service_container.html>, <https://symfony.com/doc/current/service_container/autowiring.html>, <https://symfony.com/doc/current/doctrine.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
