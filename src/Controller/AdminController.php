<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/villes', name: 'admin_villes', methods: ['GET', 'POST'])]
    public function villes(): Response
    {
        return $this->render('admin/villes.html.twig');
    }

    #[Route('/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
    public function campus(): Response
    {
        return $this->render('admin/campus.html.twig');
    }
}
