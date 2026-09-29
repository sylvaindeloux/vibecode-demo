---
name: symfony-bp-tests
description: 'Use when writing or changing automated tests of a Symfony app, in particular functional (application) tests that request URLs with WebTestCase. Official Symfony best practices, "Tests" section.'
---

# Symfony best practices: tests

Rules of the "Tests" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **As soon as you create the application, add a smoke test: a functional test that requests every application URL (via a PHPUnit data provider) and checks that it loads successfully.**
   Why: it takes little effort and catches any page that returns an error; more specific tests per page come later.
2. **Hard-code URLs in functional tests instead of generating them from routes.**
   Why: when a public URL changes, the failing test reminds you to set up a redirection for the users of the old URL.

## Examples

### 1. Smoke test

```php
// tests/ApplicationAvailabilityTest.php
namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationAvailabilityTest extends WebTestCase
{
    #[DataProvider('provideUrls')]
    public function testPageIsSuccessful(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function provideUrls(): iterable
    {
        yield 'home' => ['/'];
        yield 'product list' => ['/products'];
        yield 'product page' => ['/products/1'];   // backed by test fixtures
        // ... one line per URL
    }
}
```

### 2. Hard-coded URLs

Do:

```php
$client->request('GET', '/products/1/edit');
```

Avoid:

```php
$url = static::getContainer()->get('router')->generate('product_edit', ['id' => 1]);
$client->request('GET', $url);
```

## Source

- <https://symfony.com/doc/current/best_practices.html#tests>, section "Tests" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/testing.html>, <https://symfony.com/doc/current/routing.html#routing-generating-urls>, <https://docs.phpunit.de/en/13.1/writing-tests-for-phpunit.html#data-providers>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
