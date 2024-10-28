<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CsvImportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CampusRepository $campusRepository
    ) {}

    public function importUsers(string $csvFile): array
    {
        $results = ['success' => 0, 'errors' => []];
        
        if (($handle = fopen($csvFile, "r")) !== false) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                try {
                    [$pseudo, $email, $prenom, $nom, $telephone, $campusNom] = $data;
                    
                    $user = new User();
                    $user->setPseudo($pseudo)
                         ->setEmail($email)
                         ->setPrenom($prenom)
                         ->setNom($nom)
                         ->setTelephone((int)$telephone)
                         ->setRoles(['ROLE_USER'])
                         ->setDateCreated(new \DateTimeImmutable())
                         ->setIsActive(true);

                    // Définir un mot de passe par défaut
                    $defaultPassword = 'Cours2024';
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $defaultPassword);
                    $user->setPassword($hashedPassword);

                    // Associer le campus
                    $campus = $this->campusRepository->findOneBy(['nom' => $campusNom]);
                    if (!$campus) {
                        throw new \Exception("Campus '$campusNom' non trouvé");
                    }
                    $user->setCampus($campus);

                    $this->entityManager->persist($user);
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    $results['errors'][] = "Ligne $pseudo : " . $e->getMessage();
                }
            }
            fclose($handle);
            
            if ($results['success'] > 0) {
                $this->entityManager->flush();
            }
        }
        
        return $results;
    }
}

