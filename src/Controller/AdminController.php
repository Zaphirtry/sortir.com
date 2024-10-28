<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CsvImportType;
use App\Service\CsvImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé');
        }
        
        $users = $userRepository->findAll();
        return $this->render('admin/adminUsers.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/import-users', name: 'admin_import_users')]
    public function importUsers(Request $request, CsvImportService $importService): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();
            
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

        return $this->render('admin/import_users.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/user/{id}/toggle', name: 'admin_toggle_user', methods: ['POST'])]
    public function toggleUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(!$user->isActive());
            $entityManager->flush();
            
            $message = $user->isActive() ? 'L\'utilisateur a été activé.' : 'L\'utilisateur a été désactivé.';
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('admin_index');
    }
}
