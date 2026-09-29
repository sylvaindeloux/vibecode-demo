<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CraController extends AbstractController
{
    #[Route('/cra/{client}/{month}', name: 'app_cra', requirements: ['month' => '\d{4}-(0[1-9]|1[0-2])'], methods: ['GET'])]
    public function show(int $client, string $month): Response
    {
        return $this->render('cra/show.html.twig');
    }
}
