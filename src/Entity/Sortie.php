<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\Column]
    private ?\DateInterval $duree = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column]
    private ?int $nombreInscrits = null;

    #[ORM\Column(type: Types::TEXT, nullable:true)]
    #[Assert\Length(
        min: 3,
        max: 180,
        minMessage: 'La description doit avoit au moins 3 caractères',
        maxMessage: 'La description doit avoit au maximum 255 caractères'
    )]
    private ?string $infosSortie = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModified = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $organisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?\DateInterval
    {
        return $this->duree;
    }

    public function setDuree(\DateInterval $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getNombreInscrits(): ?int
    {
        return $this->nombreInscrits;
    }

    public function setNombreInscrits(int $nombreInscrits): static
    {
        $this->nombreInscrits = $nombreInscrits;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeImmutable $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeImmutable $dateModified): static
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getOrganisateur(): ?user
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?user $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }
}
