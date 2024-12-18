<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class CheckerEtatSortieService
{
    public function __construct(
        private readonly SortieRepository $sortieRepository,
        private readonly EtatRepository   $etatRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function checkAndUpdateStates(): void
    {
        $now = new \DateTime();
        $sorties = $this->sortieRepository->findAll();

        foreach ($sorties as $sortie) {
            $this->updateSortieState($sortie, $now);
        }

        $this->entityManager->flush();
    }

    public function updateSortieState(Sortie $sortie, \DateTime $now): void
    {
        $newState = $this->determineState($sortie, $now);
        if ($newState && $sortie->getEtat()->getLibelle() !== $newState->getLibelle()) {
            $sortie->setEtat($newState);
            $this->entityManager->persist($sortie);
        }
    }

    private function determineState(Sortie $sortie, \DateTime $now): ?Etat
    {
        $dateHeureDebut = $sortie->getDateHeureDebut();
        $dateLimiteInscription = $sortie->getDateLimiteInscription();
        $dateFin = (clone $dateHeureDebut)->modify('+' . $sortie->getDuree() . ' minutes');
        $dateArchivage = (clone $dateFin)->modify('+ 1 month');

        if ($now > $dateArchivage) {
            return $this->etatRepository->findOneBy(['libelle' => Etat::ARCHIVEE]);
        } elseif ($dateFin < $now) {
            return $this->etatRepository->findOneBy(['libelle' => Etat::PASSEE]);
        } elseif ($dateHeureDebut <= $now && $now < $dateFin) {
            return $this->etatRepository->findOneBy(['libelle' => Etat::ACTIVITE_EN_COURS]);
        } elseif ($dateLimiteInscription < $now && $now < $dateHeureDebut) {
            return $this->etatRepository->findOneBy(['libelle' => Etat::CLOTUREE]);
        } elseif ($sortie->getEtat()->getLibelle() === Etat::CREEE && $now >= $dateLimiteInscription) {
            return $this->etatRepository->findOneBy(['libelle' => Etat::OUVERTE]);
        }

        return null;
    }
}
