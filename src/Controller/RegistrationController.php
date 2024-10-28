<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
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
    #[Route('/register', name: 'app_register' ,methods: ['POST','GET'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$this->getUser()){
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $date = new \DateTimeImmutable('now');
            $user->setDateCreated($date);

            //ajoute l'image si elle est présente
            $uploadedImage = $form->get('file')->getData();
            if ($uploadedImage) {
                $originalImageName = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $uploadedImage->guessExtension();
                $safeImageName = $slugger->slug($originalImageName);
                $newImageName = $safeImageName . '-' . uniqid() . '.' . $uploadedImage->guessExtension();

                try {
                    $uploadedImage->move($this->getParameter('uploads_images_directory'), $newImageName);
                    $user->setFilename($newImageName);
                } catch (\Exception $e) {
                    // Ajoutez un message flash d'erreur
                    $this->addFlash('error', "Une erreur s'est produite lors du téléchargement de l'image. Veuillez réessayer.");
                    // Vous pouvez également logger l'erreur pour le débogage
                    // $this->logger->error('Erreur de téléchargement d\'image : ' . $e->getMessage());
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
            //ajout d'un message flash
            $this->addFlash('success',"Compte créé ! Bienvenue !");
            // do anything else you need here, like send an email

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
        }else{
            return $this->redirectToRoute('main_home');
        }
    }
}
