<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
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
                ->setInfosSortie($faker->paragraph(3, true))
                ->setEtat($this->getReference('etat_' . $faker->numberBetween(1, 6)))
                ->setDuree($faker->numberBetween(30, 240))
                ->setOrganisateur($this->getReference('user_' . $faker->numberBetween(0, 9)))
                ->setCampus($this->getReference('campus_' . $faker->numberBetween(1, 5)))
                ->setLieu($this->getReference('lieu_1'));

            // Optionnellement, définissez nombreInscrits
            if ($faker->boolean) {
                $sortie->setNombreInscrits($faker->numberBetween(0, $sortie->getNbInscriptionsMax()));
            }

            $dateCreated = $faker->dateTimeBetween('-6 months', 'now');
            $sortie->setDateCreated(\DateTimeImmutable::createFromMutable($dateCreated));

            $dateModified = $faker->optional(0.5)->dateTimeBetween($dateCreated, 'now');
            if ($dateModified) {
                $sortie->setDateModified(\DateTimeImmutable::createFromMutable($dateModified));
            }

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
