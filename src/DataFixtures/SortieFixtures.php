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
      $totalSorties = 20; // Limité à une sortie pour tester

      $typesActivites = [
        'Soirée', 'Concert', 'Spectacle', 'After-work', 'Exposition'
      ];
      for ($i = 0; $i < $totalSorties; $i++) {
        $sortie = new Sortie();
        $dateHeureDebut = $this->genererDateHeureDebut($faker);
        $dateLimiteInscription = $this->genererDateLimiteInscription($dateHeureDebut);
        $dateCreation = $this->genererDateCreation($dateLimiteInscription);

        // Simplifiez l'accès aux références pour tester
        try {
          $organisateur = $this->getReference('user_' . $faker->numberBetween(0, 9));
          $sortie->setOrganisateur($organisateur)
            ->setCampus($organisateur->getCampus());
        } catch (\Exception $e) {
          continue; // Ignore l'itération si l'utilisateur n'existe pas
        }

        // Assurez-vous que l'état est valide
        $etatLibelle = $faker->randomElement(['Créée', 'Ouverte']);
        $etat = $manager->getRepository(Etat::class)->findOneBy(['libelle' => $etatLibelle]);
        if ($etat) {
          $sortie->setEtat($etat);
        } else {
          continue; // Ignore si l'état est invalide
        }

        // Vérifiez la boucle de participants
        if ($etatLibelle === 'Ouverte') {
          $nbParticipants = $faker->numberBetween(1, $sortie->getNbInscriptionsMax());
          $participants = [];
          while (count($participants) < $nbParticipants) {
            $participant = $this->getReference('user_' . $faker->numberBetween(0, 9));
            if ($participant !== $sortie->getOrganisateur() && !in_array($participant, $participants)) {
              $participants[] = $participant;
              $sortie->addParticipant($participant);
            }
          }
        }

        $typeActivite = $faker->randomElement($typesActivites);
        $nom = $typeActivite . ' ' . $faker->word();

        $sortie->setNom($nom)
          ->setDateHeureDebut($dateHeureDebut)
          ->setDateLimiteInscription($dateLimiteInscription)
          ->setNbInscriptionsMax(10)
          ->setInfosSortie('Une sortie de test')
          ->setDuree(120)
          ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 25)))
          ->setDateCreated($dateCreation);

        $manager->persist($sortie);
      }

      $manager->flush();
    }

    private function genererDateHeureDebut(\Faker\Generator $faker): \DateTimeImmutable
    {
        $maintenant = new \DateTimeImmutable();
        $periodes = [
            'passe lointain' => [$maintenant->modify('-1 year'), $maintenant->modify('-1 month')],
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
