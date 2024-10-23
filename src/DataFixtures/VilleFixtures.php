<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    private function createVille(string $nom, string $codePostal, ObjectManager $manager, string $reference): void
    {
        $ville = new Ville();
        $ville->setNom($nom)
              ->setCodePostal($codePostal)
              ->setDateCreated(new \DateTimeImmutable());
        
        $manager->persist($ville);
        $this->addReference($reference, $ville);
    }

    public function load(ObjectManager $manager): void
    {
        $villes = [
            ['Nantes', '44000', 'ville_1'],
            ['Saint-Herblain', '44800', 'ville_2'],
            ['Rennes', '35000', 'ville_3'],
            ['Niort', '79000', 'ville_4'],
            ['Quimper', '29000', 'ville_5'],
            ['Orvault', '44700', 'ville_6'],
            ['Cesson-Sévigné', '35510', 'ville_7'],
        ];

        foreach ($villes as [$nom, $codePostal, $reference]) {
            $this->createVille($nom, $codePostal, $manager, $reference);
        }

        $manager->flush();
    }
}
