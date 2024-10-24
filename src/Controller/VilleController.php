<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/villes')]
final class VilleController extends AbstractController
{
    #[Route(name: 'villes_list', methods: ['GET', 'POST'])]
    public function villes(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $villes = $villeRepository->findAll();
        $newVille = new Ville();
        $newForm = $this->createForm(VilleType::class, $newVille);
        $newForm->handleRequest($request);

        $editForms = [];
        foreach ($villes as $ville) {
            $editForm = $this->createForm(VilleType::class, $ville, [
                'action' => $this->generateUrl('ville_edit', ['id' => $ville->getId()]),
                'method' => 'POST',
            ]);
            $editForms[$ville->getId()] = $editForm->createView();
        }

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $entityManager->persist($newVille);
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été créée avec succès.');
            return $this->redirectToRoute('villes_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ville/villes.html.twig', [
            'villes' => $villes,
            'new_form' => $newForm->createView(),
            'edit_forms' => $editForms,
        ]);
    }

    #[Route('/{id}/edit', name: 'ville_edit', methods: ['POST'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ville->setDateModified(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été mise à jour avec succès.');
        } else {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la mise à jour de la ville.');
        }

        return $this->redirectToRoute('villes_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'ville_delete', methods: ['POST'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ville->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($ville);
                $entityManager->flush();
                $this->addFlash('success', 'La ville a été supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression de la ville.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        return $this->redirectToRoute('villes_list', [], Response::HTTP_SEE_OTHER);
    }
}
