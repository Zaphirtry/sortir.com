<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route('/créer', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $organisateur */
        $organisateur = $this->getUser();
        
        if (!$organisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
        }

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // L'organisateur est forcément la personne connectée
            $sortie->setOrganisateur($organisateur);
            $sortie->setNombreInscrits(0);

            // Le campus est forcément celui de l'organisateur
            $campus = $organisateur->getCampus();
            
            if (!$campus) {
                throw $this->createNotFoundException('Le campus de l\'organisateur est introuvable.');
            }

            $sortie->setCampus($campus);

            // Définir l'état en fonction du bouton cliqué
            $action = $request->request->get('action');
            if ($action === 'publier') {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
            } else {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            }
            
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', "La sortie a été " . ($action === 'publier' ? 'publiée' : 'créée') . " avec succès.");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        $form = $this->createForm(SortieType::class, $sortie, [
            'disabled' => true
        ]);

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);

        // Pré-remplir le champ ville avec la ville du lieu actuel
        if ($sortie->getLieu()) {
            $form->get('ville')->setData($sortie->getLieu()->getVille());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setDateModified(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');
            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }
}
