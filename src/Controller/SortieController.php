<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Message;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\AnnulationType;
use App\Form\MessageType;
use App\Form\SortieType;
use App\Service\CheckerEtatSortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use App\GestionEtatSortie\CheckerEtatSortie;
use App\Repository\SortieRepository;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    public function __construct(private readonly CheckerEtatSortieService $checkerEtatSortie)
    {
    }

    #[Route('/créer', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var User $organisateur */
        $organisateur = $this->getUser();

        if (!$organisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
        }

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // L'organisateur est forcément la personne connectée
            $sortie->setOrganisateur($organisateur);

            // Le campus est forcément celui de l'organisateur
            $campus = $organisateur->getCampus();

            if (!$campus) {
                throw $this->createNotFoundException('Le campus de l\'organisateur est introuvable.');
            }

            $sortie->setCampus($campus);
            
            // Définir l'état en fonction du bouton cliqué
            $action = $request->request->get('action');
            if ($action === 'publier') {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::OUVERTE]);
            } else {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::CREEE]);
            }

            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', "La sortie a été " . ($action === 'publier' ? 'publiée' : 'créée') . " avec succès.");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'sortie_show', methods: ['GET', 'POST'])]
    public function show(int $id, SortieRepository $sortieRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkerEtatSortie->checkAndUpdateStates();
        $sortie = $sortieRepository->find($id);
        $form = $this->createForm(SortieType::class, $sortie, [
            'disabled' => true
        ]);

        $message = new Message();

        $messageForm = $this->createForm(MessageType::class, $message);

        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $message->setSortie($sortie);
            $message->setCreator($this->getUser());
            $message->setDateCreated(new \DateTimeImmutable());

            $sortie->addMessage($message);

            $entityManager->persist($message);
            $entityManager->flush();
            $this->addFlash("Success", "Votre message a bien été envoyé");

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        $messages = $entityManager->getRepository(Message::class)->findBy(['sortie' => $sortie]);

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'messageForm' => $messageForm,
            'messages' => $messages
        ]);
    }

    #[Route('/{id}/modifier', name: 'sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()  ) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour modifier une sortie");
        }
        if ($this->getUser() !== $sortie->getOrganisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous devez être le créateur de la sortie pour la modifier");
        }

        $form = $this->createForm(SortieType::class, $sortie);

        // Pré-remplir le champ ville avec la ville du lieu actuel
        if ($sortie->getLieu()) {
            $form->get('ville')->setData($sortie->getLieu()->getVille());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setDateModified(new \DateTimeImmutable());

            // Définir l'état en fonction du bouton cliqué
            $action = $request->request->get('action');
            if ($action === 'publier') {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::OUVERTE]);
            } else {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::CREEE]);
            }

            $sortie->setEtat($etat);

            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');
            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est soit l'organisateur soit un admin
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $sortie->getOrganisateur()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour supprimer cette sortie.');
        }

        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/inscrire', name: 'sortie_inscrire', methods: ['POST'])]
    public function inscrire(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($sortie->getEtat()->getLibelle() === Etat::OUVERTE) {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire à une sortie.');
            }

            $sortie->addParticipant($user);
            if ($sortie->getParticipant()->count() === $sortie->getNbInscriptionsMax()) {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::CLOTUREE]);
                $sortie->setEtat($etat);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Vous êtes inscrit à la sortie.');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas vous inscrire.');
        }
        return $this->redirectToRoute('main_home');
    }

    #[Route("/{id}/desister", name: 'sortie_desister')]
    public function desister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($sortie->getEtat()->getLibelle() === Etat::OUVERTE || $sortie->getEtat()->getLibelle() === Etat::CLOTUREE) {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour vous désister d\'une sortie.');
            }

            $sortie->removeParticipant($user);
            if ($sortie->getParticipant()->count() !== $sortie->getNbInscriptionsMax()) {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::OUVERTE]);
                $sortie->setEtat($etat);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Vous vous êtes désisté de la sortie.');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas vous désister.');
        }
        return $this->redirectToRoute('main_home');
    }

    #[Route('/{id}/annuler', name: 'sortie_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est soit l'organisateur soit un admin
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $sortie->getOrganisateur()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour annuler cette sortie.');
        }

        $form = $this->createForm(AnnulationType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour l'état de la sortie
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::ANNULEE]);
            $sortie->setEtat($etat);

            $entityManager->flush();

            $this->addFlash('success', 'La sortie a été annulée avec succès.');
            return $this->redirectToRoute('main_home');
        }


        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'form' => $form
        ]);
    }


}
