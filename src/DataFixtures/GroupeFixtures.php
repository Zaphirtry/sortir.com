<?php

namespace App\DataFixtures;

use App\Entity\Groupe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GroupeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer tous les utilisateurs
        for ($i = 0; $i < 10; $i++) { // Supposons que vous avez 10 utilisateurs
            $user = $this->getReference('user_' . $i);

            for ($j = 0; $j < 2; $j++) { // Chaque utilisateur a 2 groupes
                $groupe = new Groupe();
                $groupe->setNom($faker->words(2, true) . ' ' . $faker->emoji());
                $groupe->addMembre($user);
                $groupe->setDateCreated(new \DateTimeImmutable());
                $groupe->setCreateur($user);

                // Ajouter au moins 2 autres membres au groupe
                while ($groupe->getMembre()->count() < 3) {
                    $randomUser = $this->getReference('user_' . $faker->numberBetween(0, 9));
                    if (!$groupe->getMembre()->contains($randomUser)) {
                        $groupe->addMembre($randomUser);
                    }
                }

                $manager->persist($groupe);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
