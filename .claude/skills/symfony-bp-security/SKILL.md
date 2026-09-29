---
name: symfony-bp-security
description: 'Use when configuring authentication or authorization in a Symfony app: firewalls in security.yaml, password hashing, or the rules deciding who may access what (voters, access expressions). Official Symfony best practices, "Security" section.'
---

# Symfony best practices: security

Rules of the "Security" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Define a single firewall, unless you have two legitimately different authentication systems and users (e.g. form login for the site and tokens for an API).**
   Why: one firewall keeps things simple.
2. **Use the `auto` password hasher.**
   Why: it selects the best available algorithm for your PHP installation (currently bcrypt).
3. **Implement complex, fine-grained authorization rules in custom voters, not in long expressions inside the security attribute.**
   Why: the voter system is the solution Symfony documents for complex authorization rules.
   Doc note: the official page still names the `#[Security]` attribute of the abandoned SensioFrameworkExtraBundle; with current Symfony, access expressions go in `#[IsGranted(new Expression(...))]`.

## Examples

### 1. A single firewall

Do:

```yaml
# config/packages/security.yaml
security:
    firewalls:
        # from the recipe: only disables security for the profiler and assets
        dev:
            pattern: ^/(_profiler|_wdt|assets|build)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
```

Avoid:

```yaml
security:
    firewalls:
        main:
            # ...
        admin:
            pattern: ^/admin
            form_login: { login_path: admin_login, check_path: admin_login }
            # same users, same login system: keep it in "main"
```

### 2. The `auto` password hasher

Do:

```yaml
# config/packages/security.yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
```

Avoid:

```yaml
security:
    password_hashers:
        App\Entity\User:
            algorithm: 'bcrypt'   # pinned algorithm instead of 'auto'
```

### 3. Voters for complex rules

Do:

```php
// src/Security/ProductVoter.php
namespace App\Security;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProductVoter extends Voter
{
    public const string EDIT = 'PRODUCT_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EDIT === $attribute && $subject instanceof Product;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');

            return false;
        }

        return $subject->getOwner() === $user && !$subject->isArchived();
    }
}
```

```php
// in the controller: 'product' is the name of the action argument passed to the voter
#[Route('/products/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
#[IsGranted(ProductVoter::EDIT, 'product')]
public function edit(Product $product): Response
{
    // ...
}
```

Avoid:

```php
use Symfony\Component\ExpressionLanguage\Expression;

// business rule hidden in an attribute expression
#[IsGranted(new Expression(
    'user and subject.getOwner() === user and not subject.isArchived()'
), 'product')]
public function edit(Product $product): Response
{
    // ...
}
```

See also: `symfony-bp-controllers` (security attributes on controllers).

## Source

- <https://symfony.com/doc/current/best_practices.html#security>, section "Security" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/security.html>, <https://symfony.com/doc/current/security/passwords.html>, <https://symfony.com/doc/current/security/voters.html>, <https://symfony.com/doc/current/security/expressions.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
