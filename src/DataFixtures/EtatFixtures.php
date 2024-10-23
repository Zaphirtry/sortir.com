<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etats = [
            'Créée',
            'Ouverte',
            'Clôturée',
            'Activité en cours',
            'Passée',
            'Annulée'
        ];

        foreach ($etats as $index => $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $etat->setDateCreated(new \DateTimeImmutable('2024-10-22'));
            $manager->persist($etat);
            $this->addReference('etat_' . ($index + 1), $etat);
        }

        $manager->flush();
    }
}
