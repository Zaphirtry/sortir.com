<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campus')]
final class CampusController extends AbstractController
{
    #[Route(name: 'campus_list', methods: ['GET', 'POST'])]
    public function index(CampusRepository $campusRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $campuses = $campusRepository->findAll();
        $newCampus = new Campus();
        $newForm = $this->createForm(CampusType::class, $newCampus);
        $newForm->handleRequest($request);

        $editForms = [];
        foreach ($campuses as $campus) {
            $editForm = $this->createForm(CampusType::class, $campus, [
                'action' => $this->generateUrl('campus_edit', ['id' => $campus->getId()]),
                'method' => 'POST',
            ]);
            $editForms[$campus->getId()] = $editForm->createView();
        }

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $entityManager->persist($newCampus);
            $entityManager->flush();

            return $this->redirectToRoute('campus_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('campus/campus.html.twig', [
            'campuses' => $campuses,
            'new_form' => $newForm->createView(),
            'edit_forms' => $editForms,
        ]);
    }

    #[Route('/{id}/edit', name: 'campus_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campus->setDateModified(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la mise à jour du campus.');
        }

        return $this->redirectToRoute('campus_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'campus_delete', methods: ['POST'])]
public function delete(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$campus->getId(), $request->request->get('_token'))) {
        try {
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du campus.');
        }
    } else {
        $this->addFlash('error', 'Token CSRF invalide.');
    }

    return $this->redirectToRoute('campus_list', [], Response::HTTP_SEE_OTHER);
}
}
