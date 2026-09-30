<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Profile>
 */
final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom et prénom'])
            ->add('company', TextType::class, ['label' => 'Société', 'required' => false])
            ->add('siret', TextType::class, [
                'label' => 'SIRET',
                'help' => '14 chiffres, visibles sur votre avis de situation Insee.',
                'attr' => ['inputmode' => 'numeric', 'autocomplete' => 'off', 'class' => 'input--numeric'],
            ])
            ->add('address', TextareaType::class, ['label' => 'Adresse'])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'required' => false, 'help' => 'Affiché sur le CRA si renseigné.'])
        ;

        // BR-16: the entity holds the 14 digits; the field shows them in groups of 3, 3, 3 and 5.
        $builder->get('siret')->addModelTransformer(new CallbackTransformer(
            static fn (?string $digits): ?string => null === $digits || 14 !== \strlen($digits) ? $digits : implode(' ', str_split(substr($digits, 0, 9), 3)).' '.substr($digits, 9),
            static fn (?string $input): ?string => null === $input ? null : str_replace(' ', '', $input),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Profile::class, 'translation_domain' => false]);
    }
}
