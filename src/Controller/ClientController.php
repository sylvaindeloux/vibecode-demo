<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
final class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clients): Response
    {
        return $this->render('client/index.html.twig', ['clients' => $clients->findAllSortedByName()]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->save($request, $entityManager, new Client(), 'Client ajouté.');
    }

    #[Route('/{id}/edit', name: 'app_client_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Client $client): Response
    {
        return $this->save($request, $entityManager, $client, 'Client mis à jour.');
    }

    #[Route('/{id}/delete', name: 'app_client_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, Client $client): Response
    {
        if (!$this->isCsrfTokenValid('delete-client', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // The day entries of the client go with it (ON DELETE CASCADE, BR-14).
        $entityManager->remove($client);
        $entityManager->flush();
        $this->addFlash('success', 'Client supprimé.');

        return $this->redirectToRoute('app_client_index', status: Response::HTTP_SEE_OTHER);
    }

    /** Renders and processes the client form, for creation (new client) and edition (spec 5.5). */
    private function save(Request $request, EntityManagerInterface $entityManager, Client $client, string $successMessage): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();
            $this->addFlash('success', $successMessage);

            return $this->redirectToRoute('app_client_index', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/form.html.twig', [
            'form' => $form,
            'client' => null === $client->getId() ? null : $client,
        ]);
    }
}
