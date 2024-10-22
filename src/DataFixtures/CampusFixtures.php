<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $Campus1 = new Campus();
        $Campus1->setNom("Nantes");
        $Campus1->setDateCreated(new \DateTimeImmutable());
        $manager->persist($Campus1);
        $this->addReference('campus1', $Campus1);

        $Campus2 = new Campus();
        $Campus2->setNom("Rennes");
        $Campus2->setDateCreated(new \DateTimeImmutable());
        $manager->persist($Campus2);
        $this->addReference('campus2', $Campus2);

        $Campus3 = new Campus();
        $Campus3->setNom("Quimper");
        $Campus3->setDateCreated(new \DateTimeImmutable());
        $manager->persist($Campus3);
        $this->addReference('campus3', $Campus3);

        $Campus4 = new Campus();
        $Campus4->setNom("Niort");
        $Campus4->setDateCreated(new \DateTimeImmutable());
        $manager->persist($Campus4);
        $this->addReference('campus4', $Campus4);

        $Campus5 = new Campus();
        $Campus5->setNom("CEL");
        $Campus5->setDateCreated(new \DateTimeImmutable());
        $manager->persist($Campus5);
        $this->addReference('campus5', $Campus5);

        $manager->flush();
    }
}
