<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(): Response
    {
        $user = $this->getUser();
//        $sorties = $sortieRepository->findAll();

        return $this->render('main/home.html.twig',[
            'user' => $user,
//            'sorties' => $sorties
        ]);
    }

    #[Route('/sortie', name: 'main_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('sorties/show.html.twig');
    }
}
