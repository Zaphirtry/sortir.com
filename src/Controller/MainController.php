<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(SortieRepository $sortieRepository): Response
    {
        return $this->render('main/home.html.twig',[
           'sorties' => $sortieRepository->findAll()
        ]);
    }
}
