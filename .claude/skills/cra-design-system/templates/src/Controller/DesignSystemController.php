<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calendar\CalendarMonthFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DEV ONLY - renders every reference screen (templates/examples/) with fake data,
 * to check a UI change in light/dark/mobile/print without touching real data.
 * Open /_design-system. The app routes used by the layout and the examples
 * (app_calendar, app_cra, app_client_index, app_client_new, app_client_edit,
 * app_client_delete, app_profile) must exist.
 */
#[When(env: 'dev')]
#[Route('/_design-system', name: 'design_system_')]
final class DesignSystemController extends AbstractController
{
    private const CLIENTS = [
        ['id' => 1, 'name' => 'Pharmacie Lumière', 'contactName' => 'Claire Martin', 'contactEmail' => 'claire.martin@pharmacie-lumiere.fr', 'mission' => 'Refonte du back-office', 'address' => "12 rue de la Paix\n75002 Paris"],
        ['id' => 2, 'name' => 'Atelier Numérique', 'contactName' => 'Hugo Bernard', 'contactEmail' => 'hugo@atelier-numerique.fr', 'mission' => 'Audit de performance', 'address' => "4 quai des Chartrons\n33000 Bordeaux"],
        ['id' => 3, 'name' => 'Coopérative Horizon', 'contactName' => 'Inès Robert', 'contactEmail' => null, 'mission' => 'Accompagnement technique', 'address' => "8 place Bellecour\n69002 Lyon"],
    ];

    // The SIRET is fictitious: it passes the Luhn check on its 14 digits, but its SIREN part
    // (first 9 digits) does not, so it cannot belong to a real company.
    private const FREELANCER = [
        'name' => 'Camille Durand',
        'company' => 'Durand Conseil SASU',
        'siret' => '81234567800013',
        'address' => "25 rue des Lilas\n75020 Paris",
        'email' => 'camille@durand-conseil.fr',
    ];

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $links = array_map(fn (string $name): string => sprintf('<li><a href="%s">%s</a></li>', $this->generateUrl('design_system_'.$name), $name), ['calendar', 'calendar_no_client', 'cra', 'clients', 'clients_empty', 'client_form', 'profile']);

        return new Response('<!doctype html><meta charset="utf-8"><title>Design system</title><h1>Reference screens</h1><ul>'.implode('', $links).'</ul>');
    }

    #[Route('/calendar', name: 'calendar')]
    #[Route('/calendar/no-client', name: 'calendar_no_client', defaults: ['noClient' => true])]
    public function calendar(Request $request, CalendarMonthFactory $factory, bool $noClient = false): Response
    {
        // Demo "today" in a month with public holidays (1 and 11 November 2026).
        $today = new \DateTimeImmutable('2026-11-17');
        $month = new \DateTimeImmutable(($request->query->getString('month') ?: '2026-11').'-01');
        $entries = [];
        foreach (['02', '03', '04', '05', '06', '09', '10', '12', '13', '16', '17'] as $d) {
            $entries['2026-11-'.$d] = ['state' => 'full'];
        }
        $entries['2026-11-06']['note'] = 'Atelier de cadrage';
        $entries['2026-11-13'] = ['state' => 'half', 'note' => 'Démo client (après-midi)'];
        $entries['2026-11-14'] = ['state' => 'full', 'note' => 'Mise en production'];
        $entries['2026-11-19'] = ['state' => 'off'];
        $entries['2026-11-20'] = ['state' => 'off'];

        $calendarMonth = $factory->create(
            $month,
            $entries,
            fn (string $id): string => $this->generateUrl('design_system_calendar', ['month' => $id]),
            $this->generateUrl('design_system_calendar'),
            $today,
            $request->getLocale(),
        );

        return $this->render('examples/calendar.html.twig', [
            'clients' => $noClient ? [] : self::CLIENTS,
            'client' => $noClient ? null : self::CLIENTS[0],
            'month' => $calendarMonth,
            'save_url' => null, // no persistence in the demo
            'print_url' => $this->generateUrl('design_system_cra'),
        ]);
    }

    #[Route('/cra', name: 'cra')]
    public function cra(): Response
    {
        $days = [];
        foreach (['02', '03', '04', '05', '06', '09', '10', '12', '13', '14', '16', '17', '18', '23', '24', '25', '26', '27', '30'] as $d) {
            $days[] = ['date' => '2026-11-'.$d, 'quantity' => '13' === $d ? 0.5 : 1, 'note' => match ($d) {
                '06' => 'Atelier de cadrage',
                '13' => 'Démo client (après-midi)',
                '14' => 'Mise en production',
                default => '',
            }];
        }

        return $this->render('examples/cra_print.html.twig', [
            'back_url' => $this->generateUrl('design_system_calendar'),
            'cra' => [
                'monthLabel' => 'Novembre 2026',
                'periodStart' => '2026-11-01',
                'periodEnd' => '2026-11-30',
                'mission' => self::CLIENTS[0]['mission'],
                'freelancer' => self::FREELANCER,
                'client' => self::CLIENTS[0],
                'days' => $days,
                'total' => array_sum(array_column($days, 'quantity')),
                'generatedAt' => '2026-11-30',
            ],
        ]);
    }

    #[Route('/clients', name: 'clients')]
    #[Route('/clients/empty', name: 'clients_empty', defaults: ['empty' => true])]
    public function clients(bool $empty = false): Response
    {
        return $this->render('examples/client_index.html.twig', ['clients' => $empty ? [] : self::CLIENTS]);
    }

    #[Route('/clients/form', name: 'client_form')]
    public function clientForm(): Response
    {
        $form = $this->createFormBuilder(null, ['csrf_protection' => false, 'translation_domain' => false])
            ->add('name', TextType::class, ['label' => 'Nom du client', 'constraints' => [new Assert\NotBlank(message: 'Indiquez le nom du client.')]])
            ->add('address', TextareaType::class, ['label' => 'Adresse', 'required' => false, 'help' => 'Telle qu’elle doit apparaître sur le CRA.'])
            ->add('contactName', TextType::class, ['label' => 'Nom du contact', 'required' => false])
            ->add('contactEmail', EmailType::class, ['label' => 'E-mail du contact', 'required' => false, 'constraints' => [new Assert\Email(message: 'Cette adresse e-mail n’est pas valide (exemple : prenom@societe.fr).')]])
            ->add('mission', TextType::class, ['label' => 'Nom de la mission', 'help' => 'Par exemple : « Refonte du back-office ».', 'constraints' => [new Assert\NotBlank(message: 'Indiquez le nom de la mission.')]])
            ->getForm();

        // Submitted with invalid data, to show validation errors.
        $form->submit(['name' => '', 'address' => "12 rue de la Paix\n75002 Paris", 'contactName' => 'Claire Martin', 'contactEmail' => 'claire.martin@', 'mission' => 'Refonte du back-office']);

        return $this->renderForm('examples/client_form.html.twig', $form, ['client' => null]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        // The field shows the SIRET in groups, as the data transformer of the real form does.
        $form = $this->createFormBuilder(['siret' => '812 345 678 00013'] + self::FREELANCER, ['csrf_protection' => false, 'translation_domain' => false])
            ->add('name', TextType::class, ['label' => 'Nom et prénom'])
            ->add('company', TextType::class, ['label' => 'Société', 'required' => false])
            ->add('siret', TextType::class, ['label' => 'SIRET', 'help' => '14 chiffres, visibles sur votre avis de situation Insee.', 'attr' => ['inputmode' => 'numeric', 'autocomplete' => 'off', 'class' => 'input--numeric']])
            ->add('address', TextareaType::class, ['label' => 'Adresse'])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'required' => false, 'help' => 'Affiché sur le CRA si renseigné.'])
            ->getForm();

        return $this->renderForm('examples/profile.html.twig', $form);
    }

    /** @param array<string, mixed> $parameters */
    private function renderForm(string $template, FormInterface $form, array $parameters = []): Response
    {
        return $this->render($template, ['form' => $form->createView()] + $parameters, new Response(status: $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
