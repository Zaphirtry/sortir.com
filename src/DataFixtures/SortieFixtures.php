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

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        // Liste des types d'activités pour générer des noms plus réalistes
        $typesActivites = [
            'Soirée',
            'Concert',
            'Spectacle',
            'After-work',
            'Exposition',
            'Visite guidée',
            'Cinéma',
            'Théâtre',
            'Festival'
        ];

        // Création de 30 sorties
        for ($i = 0; $i < 30; $i++) {
            $sortie = new Sortie();
            
            // Génération de dates cohérentes
            $dateHeureDebut = $faker->dateTimeBetween('-1 month', '+3 months');
            $dateLimiteInscription = clone $dateHeureDebut;
            $dateLimiteInscription->modify('-2 days');

            // Sélection de l'organisateur
            $organisateur = $this->getReference('user_' . $faker->numberBetween(0, 9));
            
            // Génération d'un nom plus réaliste
            $typeActivite = $faker->randomElement($typesActivites);
            $nom = $typeActivite . ' : ' . $faker->words(3, true);

            $sortie->setNom($nom)
                ->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateHeureDebut))
                ->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription))
                ->setNbInscriptionsMax($faker->numberBetween(5, 50))
                ->setInfosSortie($faker->paragraphs(2, true))
                ->setDuree($faker->randomElement([60, 90, 120, 180, 240])) // Durées plus réalistes
                ->setOrganisateur($organisateur)
                ->setCampus($organisateur->getCampus())
                ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 25)))
                ->setDateCreated(new \DateTimeImmutable())
                ->setEtat($this->determinerEtat($dateHeureDebut, $dateLimiteInscription));

            // Ajout aléatoire de participants
            $nbParticipants = $faker->numberBetween(0, min(10, $sortie->getNbInscriptionsMax()));
            for ($j = 0; $j < $nbParticipants; $j++) {
                $participant = $this->getReference('user_' . $faker->numberBetween(0, 9));
                if (!$sortie->getParticipant()->contains($participant)) {
                    $sortie->addParticipant($participant);
                }
            }
            
            $sortie->setNombreInscrits($sortie->getParticipant()->count());

            $manager->persist($sortie);
        }

        $manager->flush();
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
