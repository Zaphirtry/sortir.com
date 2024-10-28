<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    private function createLieu(string $nom, string $rue, string $latitude, string $longitude, 
                              Ville $ville, ObjectManager $manager, string $reference): void
    {
        $lieu = new Lieu();
        $lieu->setNom($nom)
             ->setRue($rue)
             ->setLatitude($latitude)
             ->setLongitude($longitude)
             ->setDateCreated(new \DateTimeImmutable())
             ->setVille($ville);
        
        $manager->persist($lieu);
        $this->addReference($reference, $lieu);
    }

    public function load(ObjectManager $manager): void
    {
        $lieux = [
            // Nantes (ville_1)
            ['Pathé Atlantis', '8 Allée la Pérouse', '47.2232', '-1.6314', 'ville_1', 'lieu_1'],
            ['Stereolux', '4 Boulevard Léon Bureau', '47.2054', '-1.5629', 'ville_1', 'lieu_2'],
            ['Le Warehouse', '21 Quai des Antilles', '47.2001', '-1.5732', 'ville_1', 'lieu_3'],
            ['Le Lieu Unique', '2 Rue de la Biscuiterie', '47.2157', '-1.5483', 'ville_1', 'lieu_4'],
            ['Le Ferrailleur', 'Quai des Antilles', '47.2048', '-1.5732', 'ville_1', 'lieu_5'],
            
            // Saint-Herblain (ville_2)
            ['Zénith Nantes Métropole', 'ZAC Ar Mor', '47.2284', '-1.6349', 'ville_2', 'lieu_6'],
            ['L\'Odyssée', 'Le Bourg', '47.2146', '-1.6491', 'ville_2', 'lieu_7'],
            ['Onyx', '1 Place Océane', '47.2235', '-1.6405', 'ville_2', 'lieu_8'],
            ['Atlantis Le Centre', 'Place Océane', '47.2235', '-1.6405', 'ville_2', 'lieu_9'],
            
            // Rennes (ville_3)
            ['Le Liberté', '1 Esplanade Charles de Gaulle', '48.1051', '-1.6737', 'ville_3', 'lieu_10'],
            ['L\'Étage', '1 Esplanade Charles de Gaulle', '48.1051', '-1.6737', 'ville_3', 'lieu_11'],
            ['L\'Antipode MJC', '2 Rue André Trasbot', '48.1000', '-1.7062', 'ville_3', 'lieu_12'],
            ['Le Triangle', 'Boulevard de Yougoslavie', '48.0880', '-1.6697', 'ville_3', 'lieu_13'],
            
            // Niort (ville_4)
            ['L\'Acclameur', '50 Rue Charles Darwin', '46.3235', '-0.4547', 'ville_4', 'lieu_14'],
            ['Le Camji', '3 Rue de l\'Ancien Musée', '46.3259', '-0.4646', 'ville_4', 'lieu_15'],
            ['Le Moulin du Roc', '9 Boulevard Main', '46.3259', '-0.4646', 'ville_4', 'lieu_16'],
            
            // Quimper (ville_5)
            ['Le Pavillon', '2 Rue Pen Ar Stang', '47.9947', '-4.1001', 'ville_5', 'lieu_17'],
            ['Théâtre de Cornouaille', '1 Esplanade François Mitterrand', '47.9947', '-4.1001', 'ville_5', 'lieu_18'],
            ['Le Novomax', '2 Boulevard Dupleix', '47.9947', '-4.1001', 'ville_5', 'lieu_19'],
            
            // Orvault (ville_6)
            ['L\'Odyssée', '2 Avenue de la Jeunesse', '47.2657', '-1.6227', 'ville_6', 'lieu_20'],
            ['Théâtre de la Gobinière', '37 Avenue de la Ferrière', '47.2657', '-1.6227', 'ville_6', 'lieu_21'],
            ['La Gobinière', '37 Avenue de la Ferrière', '47.2657', '-1.6227', 'ville_6', 'lieu_22'],
            
            // Cesson-Sévigné (ville_7)
            ['Carré Sévigné', '1 Rue du Bac', '48.1195', '-1.6037', 'ville_7', 'lieu_23'],
            ['Le Pont des Arts', 'Parc de Bourgchevreuil', '48.1195', '-1.6037', 'ville_7', 'lieu_24'],
            ['La Chalotais', '2 Rue de la Chalotais', '48.1195', '-1.6037', 'ville_7', 'lieu_25']
        ];

        foreach ($lieux as [$nom, $rue, $latitude, $longitude, $villeRef, $reference]) {
            $ville = $this->getReference($villeRef, Ville::class);
            $this->createLieu($nom, $rue, $latitude, $longitude, $ville, $manager, $reference);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
