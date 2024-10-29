<?php

namespace App\Mapper;

use App\Dto\SortieListDto;
use App\Dto\SortieDetailDto;
use App\Entity\Sortie;
use App\Entity\User;

class SortieMapper
{
    public function toListDto(Sortie $sortie, User $currentUser): SortieListDto
    {
        return new SortieListDto(
            id: $sortie->getId(),
            nom: $sortie->getNom(),
            dateHeureDebut: $sortie->getDateHeureDebut(),
            dateLimiteInscription: $sortie->getDateLimiteInscription(),
            nbInscriptionsMax: $sortie->getNbInscriptionsMax(),
            nbParticipants: $sortie->getParticipant()->count(),
            etatLibelle: $sortie->getEtat()->getLibelle(),
            organisateurPseudo: $sortie->getOrganisateur()->getPseudo(),
            campusNom: $sortie->getCampus()->getNom(),
            lieuNom: $sortie->getLieu()->getNom(),
            villeNom: $sortie->getLieu()->getVille()->getNom(),
            isInscrit: $sortie->getParticipant()->contains($currentUser),
            isOrganisateur: $sortie->getOrganisateur() === $currentUser,
            motifAnnulation: $sortie->getMotifAnnulation()
        );
    }

    public function toDetailDto(Sortie $sortie, User $currentUser): SortieDetailDto
    {
        $participants = $sortie->getParticipant()->map(function(User $participant) {
            return [
                'pseudo' => $participant->getPseudo(),
                'nom' => $participant->getNom(),
                'prenom' => $participant->getPrenom(),
                'campus' => $participant->getCampus()->getNom()
            ];
        })->toArray();

        return new SortieDetailDto(
            id: $sortie->getId(),
            nom: $sortie->getNom(),
            dateHeureDebut: $sortie->getDateHeureDebut(),
            dateLimiteInscription: $sortie->getDateLimiteInscription(),
            nbInscriptionsMax: $sortie->getNbInscriptionsMax(),
            nbParticipants: $sortie->getParticipant()->count(),
            etatLibelle: $sortie->getEtat()->getLibelle(),
            organisateurPseudo: $sortie->getOrganisateur()->getPseudo(),
            campusNom: $sortie->getCampus()->getNom(),
            lieuNom: $sortie->getLieu()->getNom(),
            villeNom: $sortie->getLieu()->getVille()->getNom(),
            rue: $sortie->getLieu()->getRue(),
            codePostal: $sortie->getLieu()->getVille()->getCodePostal(),
            latitude: $sortie->getLieu()->getLatitude(),
            longitude: $sortie->getLieu()->getLongitude(),
            duree: $sortie->getDuree(),
            infosSortie: $sortie->getInfosSortie(),
            isInscrit: $sortie->getParticipant()->contains($currentUser),
            isOrganisateur: $sortie->getOrganisateur() === $currentUser,
            motifAnnulation: $sortie->getMotifAnnulation(),
            participants: $participants
        );
    }
}
