<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $etatRepository = $manager->getRepository(Etat::class);

        // Récupération des états de base
        $etatCreee = $etatRepository->findOneBy(['libelle' => Etat::CREEE]);
        $etatOuverte = $etatRepository->findOneBy(['libelle' => Etat::OUVERTE]);
        $etatAnnulee = $etatRepository->findOneBy(['libelle' => Etat::ANNULEE]);

        // Pour chaque utilisateur (sauf admin), créer des sorties
        for ($i = 0; $i < 10; $i++) {
            $organisateur = $this->getReference('user_' . $i);
            
            // Sorties futures
            $this->creerSortie($manager, $faker, $organisateur, $etatCreee, 'future');
            $this->creerSortie($manager, $faker, $organisateur, $etatOuverte, 'future');
            
            // Sorties passées (pour générer des états "Passée")
            $this->creerSortie($manager, $faker, $organisateur, $etatOuverte, 'passee');
            
            // Sorties très anciennes (pour générer des états "Archivée")
            $this->creerSortie($manager, $faker, $organisateur, $etatOuverte, 'archivee');
            
            // Sorties annulées (futures et passées)
            $this->creerSortie($manager, $faker, $organisateur, $etatAnnulee, 'future');
            $this->creerSortie($manager, $faker, $organisateur, $etatAnnulee, 'passee');
        }

        $manager->flush();
    }

    private function creerSortie(
        ObjectManager $manager, 
        \Faker\Generator $faker, 
        $organisateur, 
        $etat,
        string $periode
    ): void {
        $sortie = new Sortie();
        
        // Définir les dates selon la période
        switch ($periode) {
            case 'future':
                $dateHeureDebut = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('+1 week', '+6 months')
                );
                break;
            case 'passee':
                $dateHeureDebut = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-6 months', '-1 week')
                );
                break;
            case 'archivee':
                $dateHeureDebut = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', '-7 months')
                );
                break;
            default:
                throw new \InvalidArgumentException('Période invalide');
        }
        
        // Date limite d'inscription entre 1 et 5 jours avant la date de début
        $dateLimiteInscription = $dateHeureDebut->modify('-' . $faker->numberBetween(1, 5) . ' days');
        
        $sortie->setNom($faker->words(3, true))
            ->setDateHeureDebut($dateHeureDebut)
            ->setDuree($faker->numberBetween(60, 240))
            ->setDateLimiteInscription($dateLimiteInscription)
            ->setNbInscriptionsMax($faker->numberBetween(5, 20))
            ->setInfosSortie($faker->text(150))
            ->setOrganisateur($organisateur)
            ->setCampus($organisateur->getCampus())
            ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 22)))
            ->setEtat($etat);

        // Ajouter un motif d'annulation si la sortie est annulée
        if ($etat->getLibelle() === Etat::ANNULEE) {
            $sortie->setMotifAnnulation($faker->sentence());
        }

        // Ajouter des participants aléatoires pour les sorties ouvertes
        if ($etat->getLibelle() === Etat::OUVERTE) {
            $nbParticipants = $faker->numberBetween(0, 5);
            for ($k = 0; $k < $nbParticipants; $k++) {
                $participant = $this->getReference('user_' . $faker->numberBetween(0, 9));
                if ($participant !== $organisateur && !$sortie->getParticipant()->contains($participant)) {
                    $sortie->addParticipant($participant);
                }
            }
        }

        $manager->persist($sortie);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            LieuFixtures::class,
            EtatFixtures::class,
        ];
    }
}
