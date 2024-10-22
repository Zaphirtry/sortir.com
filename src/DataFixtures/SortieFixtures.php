<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        for ($i = 0; $i < 10; $i++) {
            $sortie = new Sortie();
            $dateHeureDebut = $faker->dateTimeBetween('now', '+1 month');
            $sortie->setNom($faker->sentence(3, true))
                ->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateHeureDebut))
                ->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween(
                    $dateHeureDebut,
                    $dateHeureDebut->modify('+1 month')
                )))
                ->setNbInscriptionsMax($faker->numberBetween(5, 20))
//                ->setNbInscriptions($faker->numberBetween(0, $sortie->getNbInscriptionsMax()))
                ->setInfosSortie($faker->paragraph(3, true))
                ->setEtat('En cours')
                ->setDuree($faker->numberBetween(30, 240)); // Ajoutez cette ligne pour définir la durée en minutes

            $dateCreated = $faker->dateTimeBetween('-6 months', 'now');
            $sortie->setDateCreated(\DateTimeImmutable::createFromMutable($dateCreated));

            $dateModified = $faker->dateTimeBetween($dateCreated, 'now');
            $sortie->setDateModified(\DateTimeImmutable::createFromMutable($dateModified));

            // $dateModified = $faker->optional(0.5)->dateTimeBetween($dateCreated, 'now');
            // if ($dateModified) {
            //     $sortie->setDateModified(\DateTimeImmutable::createFromMutable($dateModified));
            // }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}
