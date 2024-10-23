<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ApiController extends AbstractController
{
    #[Route('/lieu-details/{id}', name: 'api_lieu_details', methods: ['GET'])]
    public function getLieuDetails(Lieu $lieu): JsonResponse
    {
        return $this->json([
            'rue' => $lieu->getRue(),
            'ville' => [
                'nom' => $lieu->getVille()->getNom(),
                'codePostal' => $lieu->getVille()->getCodePostal(),
            ],
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
        ]);
    }

    #[Route('/ville/{id}/lieux', name: 'api_ville_lieux', methods: ['GET'])]
    public function getLieuxVille(Ville $ville): JsonResponse
    {
        $lieux = $ville->getLieus();
        $lieuxArray = [];
        
        foreach ($lieux as $lieu) {
            $lieuxArray[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom()
            ];
        }
        
        // Ajout de logs pour le débogage
        dump($lieuxArray);
        
        return $this->json($lieuxArray);
    }
}
