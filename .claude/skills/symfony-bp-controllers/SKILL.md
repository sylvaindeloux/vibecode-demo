---
name: symfony-bp-controllers
description: 'Use when creating or modifying a Symfony controller, route or action: the controller base class, #[Route], #[Cache] and #[IsGranted] attributes, services needed by a controller, or loading Doctrine entities from route parameters. Official Symfony best practices, "Controllers" section.'
---

# Symfony best practices: controllers

Rules of the "Controllers" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Make your controllers extend `AbstractController`, and keep them to a few lines of glue code, without business logic.**
   Why: its shortcuts (render templates, check permissions...) are worth the coupling to Symfony, precisely because controllers hold no important logic.
2. **Configure routing, HTTP caching and security with attributes on the controller (`#[Route]`, `#[Cache]`, `#[IsGranted]`).**
   Why: the configuration sits where it applies, in a single format, instead of in several YAML or PHP files.
3. **Get services through dependency injection: type-hint them as action arguments or constructor arguments.**
   Why: `$this->container->get()` in an `AbstractController` only reaches a few common services (twig, router, doctrine...).
4. **Use the EntityValueResolver to load an entity from a route parameter when it is convenient; when the lookup is more complex, call a repository method in the controller instead of configuring the resolver.**
   Why: the resolver saves the query and returns a 404 when nothing is found; beyond simple lookups, an explicit repository call is the recommended option.

## Examples

### 1-3. Base class, attributes, injected services

Do:

```php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index', methods: ['GET'])]
    #[Cache(public: true, maxage: 3600)]
    public function index(ProductRepository $products): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $products->findAll(),
        ]);
    }

    #[Route('/products/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Product $product): Response
    {
        // glue code only: form handling, calls to services, response
        return $this->render('product/edit.html.twig', ['product' => $product]);
    }
}
```

Avoid:

```php
// services pulled from the controller's container
$products = $this->container->get('doctrine')->getRepository(Product::class)->findAll();
```

### 4. Loading entities

Do (automatic: `{id}` or a mapped `{property:argument}` route parameter, 404 if not found):

```php
#[Route('/products/{id}', name: 'product_show', methods: ['GET'])]
public function show(Product $product): Response
{
    return $this->render('product/show.html.twig', ['product' => $product]);
}

#[Route('/products/by-slug/{slug:product}', name: 'product_show_by_slug', methods: ['GET'])]
public function showBySlug(Product $product): Response
{
    return $this->render('product/show.html.twig', ['product' => $product]);
}
```

Do (complex lookup: repository method called in the controller):

```php
#[Route('/shops/{shop}/products/{reference}', name: 'shop_product_show', methods: ['GET'])]
public function showInShop(string $shop, string $reference, ProductRepository $products): Response
{
    $product = $products->findOneInShop($shop, $reference)
        ?? throw $this->createNotFoundException();

    return $this->render('product/show.html.twig', ['product' => $product]);
}
```

Avoid (complex lookup hidden in the resolver configuration):

```php
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/shops/{shop}/products/{reference}', name: 'shop_product_show', methods: ['GET'])]
public function showInShop(
    #[MapEntity(expr: 'repository.findOneInShop(shop, reference)')]
    Product $product,
): Response {
    // ...
}
```

## Project notes (EasyCRA)

- Turbo Drive is enabled: an invalid form answers 422 (passing the form object to `render()` does it) and a successful POST redirects with 303. Details: `cra-design-system`, `references/symfony-integration.md`, section "Turbo".

See also: `symfony-bp-security` (voters for complex access rules), `symfony-bp-forms` (form actions).

## Source

- <https://symfony.com/doc/current/best_practices.html#controllers>, section "Controllers" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/controller.html>, <https://symfony.com/doc/current/routing.html>, <https://symfony.com/doc/current/http_cache.html>, <https://symfony.com/doc/current/security.html>, <https://symfony.com/doc/current/doctrine.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
