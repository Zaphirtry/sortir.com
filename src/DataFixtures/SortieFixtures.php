<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $typesActivites = [
            'Soirée', 'Concert', 'Spectacle', 'After-work', 'Exposition'
        ];

        $totalSorties = 5; // Nombre fixe de sorties

        for ($i = 0; $i < $totalSorties; $i++) {
            $sortie = new Sortie();

            $dateHeureDebut = $this->genererDateHeureDebut($faker);
            $dateLimiteInscription = $this->genererDateLimiteInscription($dateHeureDebut);
            $dateCreation = $this->genererDateCreation($dateLimiteInscription);

            $organisateur = $this->getReference('user_' . $faker->numberBetween(0, 9), User::class);

            $typeActivite = $faker->randomElement($typesActivites);
            $nom = $typeActivite . ' ' . $faker->word();

            $sortie->setNom($nom)
                ->setDateHeureDebut($dateHeureDebut)
                ->setDateLimiteInscription($dateLimiteInscription)
                ->setNbInscriptionsMax($faker->numberBetween(5, 20))
                ->setInfosSortie($faker->text(255))
                ->setDuree($faker->randomElement([60, 90, 120, 180, 240]))
                ->setOrganisateur($organisateur)
                ->setCampus($organisateur->getCampus())
                ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 25), Lieu::class))
                ->setDateCreated($dateCreation);

            $etatLibelle = $faker->randomElement([Etat::CREEE, Etat::OUVERTE]);
            $etat = $manager->getRepository(Etat::class)->findOneBy(['libelle' => $etatLibelle]);
            $sortie->setEtat($etat);

            if ($etatLibelle === 'Ouverte') {
                $nbParticipants = $faker->numberBetween(1, $sortie->getNbInscriptionsMax());
                $participants = [];
                while (count($participants) < $nbParticipants) {
                    $participant = $this->getReference('user_' . $faker->numberBetween(0, 9), User::class);
                    if ($participant !== $sortie->getOrganisateur() && !in_array($participant, $participants)) {
                        $participants[] = $participant;
                        $sortie->addParticipant($participant);
                    }
                }
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    private function genererDateHeureDebut(\Faker\Generator $faker): \DateTimeImmutable
    {
        $maintenant = new \DateTimeImmutable();
        $periodes = [
            'passé' => [$maintenant->modify('-1 month'), $maintenant],
            'futur proche' => [$maintenant, $maintenant->modify('+1 month')],
            'futur lointain' => [$maintenant->modify('+1 month'), $maintenant->modify('+3 months')],
        ];

        $periode = $faker->randomElement(array_keys($periodes));

        $debut = $periodes[$periode][0];
        $fin = $periodes[$periode][1];

        return $this->dateTimeBetween($faker, $debut, $fin);
    }

    private function genererDateLimiteInscription(\DateTimeImmutable $dateHeureDebut): \DateTimeImmutable
    {
        $delaiMinimum = new \DateInterval('P1D'); // 1 jour minimum
        $delaiMaximum = new \DateInterval('P7D'); // 7 jours maximum

        $dateMinimum = $dateHeureDebut->sub($delaiMaximum);
        $dateMaximum = $dateHeureDebut->sub($delaiMinimum);

        return $this->dateTimeBetween(\Faker\Factory::create(), $dateMinimum, $dateMaximum);
    }

    private function genererDateCreation(\DateTimeImmutable $dateLimiteInscription): \DateTimeImmutable
    {
        $delaiMaximum = new \DateInterval('P30D'); // 30 jours maximum avant la date limite d'inscription

        $dateMinimum = $dateLimiteInscription->sub($delaiMaximum);
        $dateMaximum = $dateLimiteInscription;

        return $this->dateTimeBetween(\Faker\Factory::create(), $dateMinimum, $dateMaximum);
    }

    private function dateTimeBetween(\Faker\Generator $faker, \DateTimeImmutable $start, \DateTimeImmutable $end): \DateTimeImmutable
    {
        $startTimestamp = $start->getTimestamp();
        $endTimestamp = $end->getTimestamp();
        $randomTimestamp = $faker->numberBetween($startTimestamp, $endTimestamp);
        return (new \DateTimeImmutable())->setTimestamp($randomTimestamp);
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
