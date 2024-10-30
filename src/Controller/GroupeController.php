<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use App\Form\GroupeType;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/groupe')]
final class GroupeController extends AbstractController
{
    #[Route(name: 'groupe_list', methods: ['GET'])]
    public function list(UserInterface $user, GroupeRepository $groupeRepository): Response
    {
        $groupes = $groupeRepository->findByMembre($user);

        return $this->render('groupe/list_groupe.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/new', name: 'groupe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $groupe = new Groupe();
        $groupe->addMembre($user);
        $form = $this->createForm(GroupeType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupe->setDateCreated(new \DateTimeImmutable());
            $groupe->setCreateur($user);

            // Ajoutez chaque membre sélectionné au groupe
            foreach ($form->get('membre')->getData() as $membre) {
                $groupe->addMembre($membre);
            }

            $entityManager->persist($groupe);
            $entityManager->flush();

            return $this->redirectToRoute('groupe_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groupe/new.html.twig', [
            'groupe' => $groupe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'groupe_show', methods: ['GET'])]
    public function show(Groupe $groupe): Response
    {
        return $this->render('groupe/show.html.twig', [
            'groupe' => $groupe,
        ]);
    }

    #[Route('/{id}/edit', name: 'groupe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroupeType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupe->setDateModified(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->redirectToRoute('groupe_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groupe/edit.html.twig', [
            'groupe' => $groupe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'groupe_delete', methods: ['POST'])]
    public function delete(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($groupe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('groupe_list', [], Response::HTTP_SEE_OTHER);
    }
}
