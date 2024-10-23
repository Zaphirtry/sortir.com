<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campusNames = ["Nantes", "Rennes", "Quimper", "Niort", "CEL"];

        foreach ($campusNames as $index => $name) {
            $campus = new Campus();
            $campus->setNom($name);
            $campus->setDateCreated(new \DateTimeImmutable('1984-01-01'));
            $manager->persist($campus);
            $this->addReference('campus_' . ($index + 1), $campus);
        }

        $manager->flush();
    }
}
