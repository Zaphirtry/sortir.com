<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
      if ($this->getUser()) {
        return $this->redirectToRoute('main_home');
      }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
  #[Route('/profile/{pseudo}', name: 'security_profile', methods: ['GET'], requirements: ['username' => '[a-zA-Z0-9_-]+'])]
    public function affiProfile(User $user): Response{
    if ($this->getUser()->getPseudo() === $user->getPseudo()||$this->isGranted('ROLE_ADMIN')){
      return $this->render('security/profile.html.twig', [
        'user' => $user,
      ]);
    }else {
      return $this->redirectToRoute('main_home');
    }
  }

  #[Route('/profile/sorties/{pseudo}', name: 'security_profile_sorties', methods: ['GET'], requirements: ['username' => '[a-zA-Z0-9_-]+'])]
  public function affiSorties(User $user,UserRepository $userRepository, SortieRepository $sortieRepository): Response{
    if ($this->getUser()->getPseudo() === $user->getPseudo()||$this->isGranted('ROLE_ADMIN')) {
      $sorties = $sortieRepository->findByUser($user);
      return $this->render('security/sortiesProfile.html.twig', [
        'sorties' => $sorties,
        'user' => $user,
      ]);
    }else{
      return $this->redirectToRoute('main_home');
    }
  }


    #[Route('/profile/modifier/{pseudo}', name: 'app_profile', methods: ['GET', 'POST'], requirements: ['username' => '[a-zA-Z0-9_-]+'])]
    public function profile(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()->getPseudo() === $user->getPseudo()||$this->isGranted('ROLE_ADMIN')) {
        $userForm = $this->createForm(RegistrationFormType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted()) {
            // Vérification du mot de passe actuel
            $currentPassword = $userForm->get('plainPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                // Si le mot de passe n'est pas correct, ajouter un message d'erreur
                $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');
            } else if ($userForm->isValid()) {
                // Le mot de passe est correct, on peut enregistrer les modifications
                $user->setDateModified(new \DateTimeImmutable());
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Le profil a bien été modifié.');

                return $this->redirectToRoute('main_home'); // Redirection après succès
            } else {
                // Si le formulaire n'est pas valide, ajoute un message d'erreur
                $this->addFlash('danger', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        return $this->render('security/ModifProfile.html.twig', [
            'user' => $user,
            'userForm' => $userForm->createView(),
        ]);
        }else{
            return $this->render('security/profileUtilisateur.html.twig', [
                'user' => $user,
            ]);
        }
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
