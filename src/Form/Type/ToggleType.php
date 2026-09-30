<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * A checkbox rendered as a switch (role="switch") by templates/form/theme.html.twig.
 * Use for settings that read as on/off. Block prefix: "toggle" (toggle_row, toggle_widget).
 */
final class ToggleType extends AbstractType
{
    public function getParent(): string
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'toggle';
    }
}
