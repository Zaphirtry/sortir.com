<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    private function determinerEtat(\DateTime $dateHeureDebut, \DateTime $dateLimiteInscription): Etat
    {
        $maintenant = new \DateTime();
        
        if ($dateHeureDebut < $maintenant) {
            return $this->getReference('etat_5'); // Passée
        } elseif ($dateHeureDebut == $maintenant) {
            return $this->getReference('etat_4'); // Activité en cours
        } elseif ($dateLimiteInscription < $maintenant) {
            return $this->getReference('etat_3'); // Clôturée
        } else {
            return $this->getReference('etat_2'); // Ouverte
        }
    }

    private function distribuerEtats(ObjectManager $manager): array
    {
        $etats = $manager->getRepository(Etat::class)->findAll();
        $distribution = [];
        foreach ($etats as $etat) {
            $distribution[$etat->getId()] = 3; // Au moins 3 sorties par état
        }
        return $distribution;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        $typesActivites = [
            'Soirée', 'Concert', 'Spectacle', 'After-work', 'Exposition',
            'Visite guidée', 'Cinéma', 'Théâtre', 'Festival'
        ];

        $distribution = $this->distribuerEtats($manager);
        $totalSorties = max(array_sum($distribution), 30); // Au moins 30 sorties

        for ($i = 0; $i < $totalSorties; $i++) {
            $sortie = new Sortie();
            
            $dateHeureDebut = $faker->dateTimeBetween('-1 month', '+3 months');
            $dateLimiteInscription = clone $dateHeureDebut;
            $dateLimiteInscription->modify('-2 days');

            $organisateur = $this->getReference('user_' . $faker->numberBetween(0, 9));
            
            $typeActivite = $faker->randomElement($typesActivites);
            $nom = $typeActivite . ' : ' . $faker->words(3, true);

            $sortie->setNom($nom)
                ->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateHeureDebut))
                ->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription))
                ->setNbInscriptionsMax($faker->numberBetween(5, 50))
                ->setInfosSortie($faker->text(100))
                ->setDuree($faker->randomElement([60, 90, 120, 180, 240]))
                ->setOrganisateur($organisateur)
                ->setCampus($organisateur->getCampus())
                ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 25)))
                ->setDateCreated(new \DateTimeImmutable());

            // Attribution d'un état en respectant la distribution
            $etatId = $this->attribuerEtat($distribution);
            $etat = $manager->getRepository(Etat::class)->find($etatId);
            $sortie->setEtat($etat);

            // Ajout aléatoire de participants
            $nbParticipants = $faker->numberBetween(0, min(10, $sortie->getNbInscriptionsMax()));
            for ($j = 0; $j < $nbParticipants; $j++) {
                $participant = $this->getReference('user_' . $faker->numberBetween(0, 9));
                if (!$sortie->getParticipant()->contains($participant)) {
                    $sortie->addParticipant($participant);
                }
            }


            $manager->persist($sortie);
        }

        $manager->flush();
    }

    private function attribuerEtat(array &$distribution): int
    {
        $etatsDisponibles = array_filter($distribution, fn($count) => $count > 0);
        if (empty($etatsDisponibles)) {
            return array_rand($distribution); // Si tous les états ont été utilisés, on choisit aléatoirement
        }
        $etatId = array_rand($etatsDisponibles);
        $distribution[$etatId]--;
        return $etatId;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EtatFixtures::class,
            CampusFixtures::class,
            LieuFixtures::class,
        ];
    }
}
