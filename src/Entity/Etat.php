<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    public const CREEE = 'Créée';
    public const OUVERTE = 'Ouverte';
    public const CLOTUREE = 'Clôturée';
    public const ACTIVITE_EN_COURS = 'Activité en cours';
    public const PASSEE = 'Passée';
    public const ANNULEE = 'Annulée';

    public static function getEtats(): array
    {
        return [
            self::CREEE,
            self::OUVERTE,
            self::CLOTUREE,
            self::ACTIVITE_EN_COURS,
            self::PASSEE,
            self::ANNULEE,
        ];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateModified = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'etat')]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->dateModified = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

     public function setLibelle(string $libelle): static
     {
         if (!in_array($libelle, self::getEtats())) {
             throw new \InvalidArgumentException("État invalide");
         }
         $this->libelle = $libelle;
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

    public function setDateModified(\DateTimeImmutable $dateModified): static
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setEtat($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getEtat() === $this) {
                $sorty->setEtat(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle;
    }
}
