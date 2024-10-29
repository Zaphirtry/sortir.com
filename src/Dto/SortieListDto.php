<?php

namespace App\Dto;

class SortieListDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly \DateTimeImmutable $dateHeureDebut,
        public readonly \DateTimeImmutable $dateLimiteInscription,
        public readonly int $nbInscriptionsMax,
        public readonly int $nbParticipants,
        public readonly string $etatLibelle,
        public readonly string $organisateurPseudo,
        public readonly string $campusNom,
        public readonly string $lieuNom,
        public readonly string $villeNom,
        public readonly bool $isInscrit,
        public readonly bool $isOrganisateur,
        public readonly ?string $motifAnnulation = null
    ) {}
}
