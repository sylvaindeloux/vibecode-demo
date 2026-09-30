<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Client>
 */
final class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du client'])
            ->add('address', TextareaType::class, ['label' => 'Adresse', 'required' => false, 'help' => 'Telle qu’elle doit apparaître sur le CRA.'])
            ->add('contactName', TextType::class, ['label' => 'Nom du contact', 'required' => false])
            ->add('contactEmail', EmailType::class, ['label' => 'E-mail du contact', 'required' => false])
            ->add('mission', TextType::class, ['label' => 'Nom de la mission', 'help' => 'Par exemple : « Refonte du back-office ».'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Client::class, 'translation_domain' => false]);
    }
}
