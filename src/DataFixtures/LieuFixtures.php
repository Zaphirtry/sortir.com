<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $ville = $this->getReference('ville_1');

        $lieu1 = new Lieu();
        $lieu1->setNom("Nantes");
        $lieu1->setRue("1 rue de la paix");
        $lieu1->setLatitude("44.848614");
        $lieu1->setLongitude("-0.580605");
        $lieu1->setDateCreated(new \DateTimeImmutable());
        $lieu1->setVille($ville);
        $manager->persist($lieu1);
        $this->addReference('lieu_1', $lieu1);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
