<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calendar\CalendarDay;
use App\Calendar\Today;
use App\Entity\Client;
use App\Entity\DayEntry;
use App\Repository\DayEntryRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapDateTime;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CraController extends AbstractController
{
    #[Route('/cra/{client}/{month}', name: 'app_cra', requirements: ['client' => '\d+', 'month' => CalendarController::MONTH_PATTERN], methods: ['GET'])]
    public function show(
        Request $request,
        Client $client,
        #[MapDateTime(format: '!Y-m')] \DateTimeImmutable $month,
        ProfileRepository $profiles,
        DayEntryRepository $entries,
        ValidatorInterface $validator,
        Today $today,
    ): Response {
        $profile = $profiles->get();
        if (\count($validator->validate($profile)) > 0) {
            $this->addFlash('warning', 'Complétez votre profil pour ouvrir le CRA.');

            return $this->redirectToRoute('app_profile', status: Response::HTTP_SEE_OTHER);
        }

        // Full and half days only, in chronological order (BR-18).
        $days = array_values(array_map(
            static fn (DayEntry $entry): array => ['date' => $entry->getDate()->format('Y-m-d'), 'quantity' => $entry->quantity(), 'note' => $entry->getNote()],
            array_filter($entries->findByClientAndMonth($client, $month), static fn (DayEntry $entry): bool => \in_array($entry->getState(), [CalendarDay::STATE_FULL, CalendarDay::STATE_HALF], true)),
        ));
        // The month keeps the time zone of the clock (date value resolver): format it in that zone.
        $monthFormatter = new \IntlDateFormatter($request->getLocale(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $month->getTimezone(), null, 'LLLL y');

        return $this->render('cra/show.html.twig', [
            'cra' => [
                'monthLabel' => ucfirst((string) $monthFormatter->format($month)),
                'periodStart' => $month->format('Y-m-d'),
                'periodEnd' => $month->modify('last day of this month')->format('Y-m-d'),
                'mission' => $client->getMission(),
                'freelancer' => $profile,
                'client' => $client,
                'days' => $days,
                'total' => array_sum(array_column($days, 'quantity')),
                'generatedAt' => $today->date()->format('Y-m-d'),
            ],
            'back_url' => $this->generateUrl('app_calendar', ['client' => $client->getId(), 'month' => $month->format('Y-m')]),
        ]);
    }
}
