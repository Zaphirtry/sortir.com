<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer un utilisateur admin
        $dateCreated = \DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());

        $admin = new User();
        $admin->setPseudo('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin2024'));
        $admin->setPrenom('Admin');
        $admin->setNom('Istrateur');
        $admin->setTelephone(0642424242);
        $admin->setEmail('admin@sortir.com');
        $admin->setDateCreated($dateCreated);
        $admin->setCampus($this->getReference('campus_1', Campus::class));
        $manager->persist($admin);
        $this->addReference('admin_user', $admin);

        // Créer des utilisateurs normaux
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setPseudo("user$i");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Cours2024'));
            $user->setPrenom($faker->firstName());
            $user->setNom($faker->lastName());
            $user->setTelephone($faker->numerify('0#########'));
            $user->setEmail("user$i@sortir.com");
            $user->setDateCreated($dateCreated);
            $user->setCampus($this->getReference('campus_' . $faker->numberBetween(1, 5), Campus::class));
            $manager->persist($user);
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
