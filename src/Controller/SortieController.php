<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'sortie_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('sorties/show.html.twig');
    }

    #[Route('/{id}', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('sorties/create.html.twig');
    }

    #[Route('/{id}/modifier', name: 'sortie_update', methods: ['GET', 'POST'])]
    public function update(): Response
    {
        return $this->render('sorties/update.html.twig');
    }

    #[Route('/{id}/annuler', name: 'sortie_cancel', methods: ['GET', 'POST'])]
    public function cancel(): Response
    {
        return $this->render('sorties/cancel.html.twig');
    }

    #[Route('/{id}/supprimer', name: 'sortie_delete', methods: ['GET', 'POST'])]
    public function delete(): Response
    {
        return $this->redirectToRoute('main_home');
    }
}
