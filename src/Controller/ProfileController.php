<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProfileRepository $profiles, EntityManagerInterface $entityManager): Response
    {
        $profile = $profiles->get();
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($profile);
            $entityManager->flush();
            $this->addFlash('success', 'Profil enregistré.');

            return $this->redirectToRoute('app_profile', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', ['form' => $form]);
    }
}
