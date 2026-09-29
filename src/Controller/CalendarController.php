<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calendar\CalendarMonthFactory;
use App\Calendar\DaysInput;
use App\Calendar\Today;
use App\Entity\Client;
use App\Entity\DayEntry;
use App\Repository\ClientRepository;
use App\Repository\DayEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapDateTime;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    /** A month is identified by 'YYYY-MM' (BR-12); any other value answers 404. */
    public const string MONTH_PATTERN = '\d{4}-(0[1-9]|1[0-2])';

    /** Last client shown, kept for one year (BR-13, D-07). */
    private const string CLIENT_COOKIE = 'client';

    #[Route('/', name: 'app_calendar', methods: ['GET'])]
    public function index(
        Request $request,
        ClientRepository $clients,
        DayEntryRepository $entries,
        CalendarMonthFactory $factory,
        Today $today,
        #[MapQueryParameter] ?int $client = null,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^'.self::MONTH_PATTERN.'$/'])] ?string $month = null,
    ): Response {
        $allClients = $clients->findAllSortedByName();
        $selected = match (true) {
            null !== $client => $clients->find($client) ?? throw $this->createNotFoundException(),
            default => $clients->find($request->cookies->getInt(self::CLIENT_COOKIE)) ?? $allClients[0] ?? null,
        };

        if (null === $selected) {
            return $this->render('calendar/index.html.twig', ['clients' => [], 'client' => null, 'month' => null]);
        }

        $today = $today->date();
        $monthDate = new \DateTimeImmutable(($month ?? $today->format('Y-m')).'-01');
        $calendarMonth = $factory->create(
            $monthDate,
            array_map(static fn (DayEntry $entry): array => ['state' => $entry->getState(), 'note' => $entry->getNote()], $entries->findByClientAndMonth($selected, $monthDate)),
            fn (string $id): string => $this->generateUrl('app_calendar', ['client' => $selected->getId(), 'month' => $id]),
            $this->generateUrl('app_calendar'),
            $today,
            $request->getLocale(),
        );

        $response = $this->render('calendar/index.html.twig', [
            'clients' => $allClients,
            'client' => $selected,
            'month' => $calendarMonth,
            'save_url' => $this->generateUrl('app_calendar_save', ['client' => $selected->getId(), 'month' => $calendarMonth->id]),
            'print_url' => $this->generateUrl('app_cra', ['client' => $selected->getId(), 'month' => $calendarMonth->id]),
        ]);
        $response->headers->setCookie(Cookie::create(self::CLIENT_COOKIE, (string) $selected->getId(), '+1 year', '/', httpOnly: true, sameSite: Cookie::SAMESITE_LAX));

        return $response;
    }

    /** JSON endpoint of the design-system calendar controller (spec 5.3). */
    #[Route('/calendar/{client}/{month}', name: 'app_calendar_save', requirements: ['client' => '\d+', 'month' => self::MONTH_PATTERN], methods: ['POST'])]
    public function save(
        Request $request,
        Client $client,
        #[MapDateTime(format: '!Y-m')] \DateTimeImmutable $month,
        #[MapRequestPayload] DaysInput $days,
        DayEntryRepository $entries,
    ): Response {
        if (!$this->isCsrfTokenValid('calendar', $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entries->save($client, $month, $days->days);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
