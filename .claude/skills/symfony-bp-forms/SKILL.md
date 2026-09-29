---
name: symfony-bp-forms
description: 'Use when creating or modifying a Symfony form: the form type class, its submit buttons, the validation constraints of the data it edits, or the controller action that renders and processes it. Official Symfony best practices, "Forms" section.'
---

# Symfony best practices: forms

Rules of the "Forms" section of the official Symfony best practices (Symfony 8.1).

## Rules

1. **Define forms as PHP classes (form types), not in controllers.**
   Why: form classes can be reused in several places, and controllers stay simpler to write and maintain.
2. **Add submit buttons in the templates, not in form classes or controllers.**
   Why: the form class stays agnostic of where it is used (the same form can say "Add new" or "Save changes"), and the button styling stays in the template.
3. **Exception: when a form has several submit buttons, add them in the controller.**
   Why: otherwise the controller cannot check which button was clicked.
4. **Put validation constraints on the underlying object (e.g. the entity), not on form fields.**
   Why: constraints on form fields cannot be reused by other forms or other places that use the object.
5. **Render and process a form in a single controller action.**
   Why: rendering and processing are almost identical, so one action is much simpler.

## Examples

### 1. Form type class

Do:

```php
namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('stock', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
```

Avoid:

```php
// form built inline in the controller
$form = $this->createFormBuilder($product)
    ->add('name', TextType::class)
    ->add('stock', IntegerType::class)
    ->getForm();
```

### 2. Buttons in templates

Do:

```twig
{# templates/product/new.html.twig (edit.html.twig says "Save changes") #}
{{ form_start(form) }}
    {{ form_widget(form) }}
    <button type="submit">Add new</button>
{{ form_end(form) }}
```

Avoid:

```php
// in ProductType::buildForm(): the label only fits one of the pages using the form
$builder->add('save', SubmitType::class, ['label' => 'Add new']);
```

### 3. Several submit buttons: in the controller

```php
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

$form = $this->createForm(ProductType::class, $product)
    ->add('save', SubmitType::class)
    ->add('saveAndAdd', SubmitType::class);

$form->handleRequest($request);
if ($form->isSubmitted() && $form->isValid()) {
    // ... save the product

    $next = $form->get('saveAndAdd')->isClicked() ? 'product_new' : 'product_index';

    return $this->redirectToRoute($next, status: Response::HTTP_SEE_OTHER);
}
```

### 4. Constraints on the object

Do:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Product
{
    // ...

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $stock = null;
}
```

Avoid:

```php
// constraints tied to this form only
$builder->add('name', TextType::class, [
    'constraints' => [new Assert\NotBlank()],
]);
```

### 5. One action renders and processes the form

Do:

```php
#[Route('/products/new', name: 'product_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->redirectToRoute('product_index', status: Response::HTTP_SEE_OTHER);
    }

    // passing the form object (not createView()) answers 422 when the submitted form is invalid
    return $this->render('product/new.html.twig', ['form' => $form]);
}
```

Avoid:

```php
#[Route('/products/new', name: 'product_new', methods: ['GET'])]
public function new(): Response
{
    // only renders the form
}

#[Route('/products', name: 'product_create', methods: ['POST'])]
public function create(Request $request): Response
{
    // only processes it
}
```

## Project notes (EasyCRA)

- Form rendering follows `cra-design-system` (`references/symfony-integration.md`, section "Forms"): global form theme, submit buttons as `<twig:Button type="submit">` components, `label` and `help` options given as French texts, with `'translation_domain' => false` (no translation keys).

See also: `symfony-bp-controllers` (controller rules that also apply to form actions).

## Source

- <https://symfony.com/doc/current/best_practices.html#forms>, section "Forms" (Symfony 8.1 documentation), read on 2026-09-29.
- Linked pages checked for current syntax: <https://symfony.com/doc/current/forms.html> (form classes, rendering, processing, multiple buttons), <https://symfony.com/doc/current/validation.html>, <https://symfony.com/doc/current/reference/constraints.html>.
- Rules rephrased from the Symfony documentation, licensed under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
