<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
final class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('client/index.html.twig');
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        return $this->render('client/form.html.twig');
    }

    #[Route('/{id}/edit', name: 'app_client_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id): Response
    {
        return $this->render('client/form.html.twig');
    }
}
