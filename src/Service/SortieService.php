<?php

namespace App\Service;

use App\Dto\SortieListDto;
use App\Dto\SortieDetailDto;
use App\Mapper\SortieMapper;
use App\Repository\SortieRepository;
use App\Entity\User;

class SortieService
{
    public function __construct(
        private readonly SortieRepository $sortieRepository,
        private readonly SortieMapper $sortieMapper,
        private readonly CheckerEtatSortieService $checkerEtatSortie
    ) {}

    /**
     * @return SortieListDto[]
     */
    public function getSortiesForHomePage(User $currentUser, ?array $filters = null): array
    {
        $this->checkerEtatSortie->checkAndUpdateStates();
        
        // Filtres par défaut si aucun filtre n'est fourni
        $defaultFilters = [
            'showPassed' => false,
            'showArchived' => false,
            // autres filtres par défaut si nécessaire
        ];
        
        // Fusionner les filtres fournis avec les filtres par défaut
        $finalFilters = $filters ? array_merge($defaultFilters, $filters) : $defaultFilters;
        
        $sorties = $this->sortieRepository->findSortiesWithFilters($finalFilters);
        
        return array_map(
            fn($sortie) => $this->sortieMapper->toListDto($sortie, $currentUser),
            $sorties
        );
    }

    /**
     * @return SortieDetailDto
     */
    public function getSortieDetail(int $id, User $currentUser): SortieDetailDto
    {
        $this->checkerEtatSortie->checkAndUpdateStates();

        $sortie = $this->sortieRepository->find($id);
        
        if (!$sortie) {
            throw new \Exception('Sortie non trouvée');
        }
        
        return $this->sortieMapper->toDetailDto($sortie, $currentUser);
    }
}
