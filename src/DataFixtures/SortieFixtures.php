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
    private const TYPES_ACTIVITES = [
        'Soirée', 'Concert', 'Spectacle', 'After-work', 'Exposition'
    ];
    
    private const ETATS_VALIDES = ['Créée', 'Ouverte'];
    private const NB_SORTIES = 20;
    private const MIN_INSCRIPTIONS = 2;
    private const MAX_INSCRIPTIONS = 20;

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < self::NB_SORTIES; $i++) {
            $this->creerSortie($manager, $faker);
        }

        $manager->flush();
    }

    private function creerSortie(ObjectManager $manager, \Faker\Generator $faker): void
    {
        $sortie = new Sortie();
        $dateHeureDebut = $this->genererDateHeureDebut($faker);
        $dateLimiteInscription = $this->genererDateLimiteInscription($dateHeureDebut);
        
        $organisateur = $this->getReference('user_' . $faker->numberBetween(0, 9));
        if (!$organisateur) {
            return;
        }

        $etatLibelle = $faker->randomElement(self::ETATS_VALIDES);
        $etat = $manager->getRepository(Etat::class)->findOneBy(['libelle' => $etatLibelle]);
        if (!$etat) {
            return;
        }

        $sortie
            ->setOrganisateur($organisateur)
            ->setCampus($organisateur->getCampus())
            ->setEtat($etat)
            ->setNom($this->genererNomSortie($faker))
            ->setDateHeureDebut($dateHeureDebut)
            ->setDateLimiteInscription($dateLimiteInscription)
            ->setNbInscriptionsMax($faker->numberBetween(self::MIN_INSCRIPTIONS, self::MAX_INSCRIPTIONS))
            ->setInfosSortie('Une sortie de test')
            ->setDuree(120)
            ->setLieu($this->getReference('lieu_' . $faker->numberBetween(1, 25)))
            ->setDateCreated($this->genererDateCreation($dateLimiteInscription));

        if ($etatLibelle === 'Ouverte') {
            $this->ajouterParticipants($sortie, $faker);
        }

        $manager->persist($sortie);
    }

    private function genererNomSortie(\Faker\Generator $faker): string
    {
        $typeActivite = $faker->randomElement(self::TYPES_ACTIVITES);
        return $typeActivite . ' ' . $faker->word();
    }

    private function ajouterParticipants(Sortie $sortie, \Faker\Generator $faker): void
    {
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
