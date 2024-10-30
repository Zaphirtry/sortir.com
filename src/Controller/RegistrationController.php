<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CsvImportType;
use App\Form\RegistrationFormType;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST', 'GET'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, CsvImportService $importService, Security $security, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_home');
        }

        // Formulaire d'importation CSV
        $csvForm = $this->createForm(CsvImportType::class);
        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            $csvFile = $csvForm->get('csvFile')->getData();

            if ($csvFile) {
                $filePath = $csvFile->getPathname();
                $results = $importService->importUsers($filePath);

                if ($results['success'] > 0) {
                    $this->addFlash('success', "{$results['success']} utilisateurs ont été importés avec succès.");
                }

                foreach ($results['errors'] as $error) {
                    $this->addFlash('error', $error);
                }
            }
            return $this->redirectToRoute('admin_index');
        }

        // Formulaire d'enregistrement d'utilisateur
        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $plainPassword = $registrationForm->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $date = new \DateTimeImmutable('now');
            $user->setDateCreated($date);
            $user->setIsActive(true);

            $uploadedImage = $registrationForm->get('file')->getData();
            if ($uploadedImage) {
                $originalImageName = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $uploadedImage->guessExtension();
                $safeImageName = $slugger->slug($originalImageName);
                $newImageName = $safeImageName . '-' . uniqid() . '.' . $uploadedImage->guessExtension();

                try {
                    $uploadedImage->move($this->getParameter('uploads_images_directory'), $newImageName);
                    $user->setFilename($newImageName);
                } catch (\Exception $e) {
                    $this->addFlash('error', "Une erreur s'est produite lors du téléchargement de l'image. Veuillez réessayer.");
                }
            } else {
                // Définir l'image par défaut si aucune image n'est téléchargée
                $user->setFilename('avatardefault.png');
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Utilisateur ajouté !");

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'csvForm' => $csvForm->createView(),
        ]);
    }
}
